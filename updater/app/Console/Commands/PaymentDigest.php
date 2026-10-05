<?php

namespace App\Console\Commands;

use App\Mail\PaymentDigestMail;
use App\Models\Admin;
use App\Services\FailureLogParser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Surfaces payment-gateway failures that currently die silently in
 * storage/logs/payment-*.log.
 *
 * The gateway controllers log every anomaly they recover from - a callback for
 * an unknown transaction, a verification the bank rejected, a SOAP fault, a
 * refund that did not go through - and then keep serving the next request. That
 * is the right behaviour for the customer in front of them, but from the
 * operator's side a gateway that has been failing all day looks exactly like a
 * gateway nobody used: no mail, no queue, no alert, no entry in any report.
 *
 * This command reads that log for the requested window and emails the admin a
 * digest. Two deliberate choices:
 *
 *  - Only WARNING+ records are counted. The channel also logs INFO for the
 *    happy path (initiation, verification, duplicate callbacks); those are
 *    normal traffic and would bury the failures.
 *  - Records are bucketed by gateway and failure kind rather than by literal
 *    message, so the 100+ distinct strings across the gateway controllers
 *    collapse into a handful of readable lines ("ZarinPal: 23 verification
 *    failures"), and a new gateway added later is still counted.
 *
 * Sent-state is remembered so a daily schedule tick does not re-send unchanged
 * news, while genuinely new failures keep producing new digests.
 */
class PaymentDigest extends Command
{
    protected $signature = 'payment:digest
        {--window=24 : Look-back window in hours}
        {--to= : Override the recipient (defaults to config, then first admin email)}
        {--dry : Build and display the digest without sending or remembering it}
        {--force : Send even when no new failures were reported since the last digest}
        {--min-level=warning : Lowest severity to report (debug|info|warning|error|critical)}';

    protected $description = 'Email a digest of recent payment gateway failures to the admin';

    private const SENT_CACHE_KEY = 'payment:digest:sent';

    private const LEVELS = ['debug' => 0, 'info' => 1, 'notice' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4, 'alert' => 5, 'emergency' => 6];

    /**
     * Failure kinds, matched in order against the log message. Order matters:
     * the first pattern that matches names the record, so the more specific
     * patterns must come first.
     */
    private const KINDS = [
        // a callback arrived for a transaction this install has no record of -
        // either a stray/replayed callback or, worse, a real payment whose row
        // was lost; both need a human
        ['transaction not found', 'unknown_transaction'],
        ['missing token', 'unknown_transaction'],
        // the gateway answered but rejected the verification
        ['verification failed', 'verification_failed'],
        ['verify failed', 'verification_failed'],
        // the amount the gateway reported is not what was expected
        ['amount mismatch', 'amount_mismatch'],
        // gateway-side transport/protocol faults (Mellat is SOAP)
        ['soap', 'gateway_error'],
        ['verification error', 'verification_error'],
        ['verify error', 'verification_error'],
        ['initiation error', 'initiation_error'],
        ['init error', 'initiation_error'],
        ['send failed', 'initiation_failed'],
        ['initiation failed', 'initiation_failed'],
        ['init failed', 'initiation_failed'],
        ['refund failed', 'refund_failed'],
        ['refund error', 'refund_error'],
        ['cancelled', 'cancelled'],
        // money was verified but the membership/extend flow could not run
        ['without a checkout payload', 'grant_failed'],
        ['package that no longer exists', 'grant_failed'],
        ['outside a membership/extend checkout', 'grant_failed'],
    ];

    /** Fallback for a failure whose wording we have not seen before. */
    private const KIND_UNKNOWN = 'other_failure';

    public function handle(FailureLogParser $parser)
    {
        $window = max(1, (int) $this->option('window'));
        $cutoff = Carbon::now()->subHours($window);

        $minLevel = strtolower((string) $this->option('min-level'));
        if (!isset(self::LEVELS[$minLevel])) {
            $this->error("Unknown --min-level '{$minLevel}'. Use one of: " . implode(', ', array_keys(self::LEVELS)) . '.');
            return 1;
        }

        $entries = $this->collectEntries($parser, $cutoff, self::LEVELS[$minLevel]);
        $digest = $this->buildDigest($entries, $window);

        if ($this->option('dry')) {
            $this->renderDigest($digest);
            $this->line('Dry run: nothing was sent or remembered.');
            return 0;
        }

        if ($digest['total'] === 0) {
            $this->info("No payment failures at {$minLevel} or above in the last {$window}h; no digest sent.");
            return 0;
        }

        $sentCount = (int) Cache::get(self::SENT_CACHE_KEY, 0);
        if (!$this->option('force') && $digest['total'] <= $sentCount) {
            $this->info("No new payment failures since the last digest ({$digest['total']} known); nothing sent.");
            return 0;
        }

        $recipient = $this->option('to') ?: config('payment.digest_recipient') ?: Admin::query()->value('email');
        if (!$recipient) {
            $this->error('No recipient available: pass --to=... or create an admin with an email address.');
            return 1;
        }

        try {
            Mail::to($recipient)->send(new PaymentDigestMail($digest));
        } catch (\Throwable $e) {
            // the sent-counter is intentionally left untouched so the next
            // scheduled run retries the delivery of these failures
            Log::channel('payment')->error('Payment digest email failed', ['error' => $e->getMessage()]);
            $this->error('Digest email failed: ' . $e->getMessage());
            return 1;
        }

        Cache::put(self::SENT_CACHE_KEY, $digest['total']);

        $this->info("Payment digest sent to {$recipient}: {$digest['total']} failure(s) in the last {$window}h.");
        return 0;
    }

    /**
     * Turn raw log entries into classified digest records, dropping anything
     * below the requested severity.
     */
    private function collectEntries(FailureLogParser $parser, Carbon $cutoff, int $minLevel): array
    {
        $path = config('logging.channels.payment.path') ?: storage_path('logs/payment.log');
        $entries = [];

        foreach ($parser->parseChannel($path, $cutoff) as $entry) {
            if ((self::LEVELS[$entry['level']] ?? 0) < $minLevel) {
                continue;
            }
            $entries[] = $this->classify($entry);
        }

        return $entries;
    }

    /**
     * Map one log entry onto gateway / kind buckets.
     *
     * The gateway is taken from the leading word(s) of the message
     * ("ZarinPal callback: ..."), which is the convention every gateway
     * controller already follows; the context 'gateway' key wins when present
     * because the shared grant helper writes it explicitly.
     */
    private function classify(array $entry): array
    {
        $message = $entry['message'];
        $lower = mb_strtolower($message);

        $kind = self::KIND_UNKNOWN;
        foreach (self::KINDS as [$needle, $name]) {
            if (strpos($lower, $needle) !== false) {
                $kind = $name;
                break;
            }
        }

        return [
            'time' => $entry['time'],
            'level' => $entry['level'],
            'gateway' => $this->detectGateway($entry),
            'kind' => $kind,
            'message' => $message,
            'detail' => $this->detail($entry),
        ];
    }

    private function detectGateway(array $entry): string
    {
        if (!empty($entry['context']['gateway'])) {
            return (string) $entry['context']['gateway'];
        }

        // "ZarinPal callback: ..." / "Pay.ir send failed ..." -> leading token
        // up to the first space, but "Pay.ir" contains a dot and no space, so
        // the token is taken verbatim and simply trimmed of punctuation
        if (preg_match('/^([A-Za-z][A-Za-z0-9.]*)\s/', $entry['message'], $m)) {
            $token = rtrim($m[1], '.');
            if ($token !== '') {
                return $token;
            }
        }

        return 'unknown';
    }

    /**
     * Build the short human-readable detail shown next to a record: the error
     * text when there is one, otherwise whatever identifier the gateway
     * context carried (authority / refId / trackId / payment_id / token).
     */
    private function detail(array $entry): string
    {
        $context = $entry['context'];

        if (!empty($context['error'])) {
            return mb_substr((string) $context['error'], 0, 200);
        }

        foreach (['refId', 'authority', 'trackId', 'trans_id', 'payment_id', 'token', 'order_id', 'transaction_id'] as $key) {
            if (array_key_exists($key, $context) && $context[$key] !== null && $context[$key] !== '') {
                return $key . '=' . mb_substr((string) $context[$key], 0, 80);
            }
        }

        if (!empty($context['message'])) {
            return mb_substr((string) $context['message'], 0, 200);
        }

        return '';
    }

    private function buildDigest(array $entries, int $window): array
    {
        $byGateway = [];
        $byKind = [];
        foreach ($entries as $entry) {
            $byGateway[$entry['gateway']] = ($byGateway[$entry['gateway']] ?? 0) + 1;
            $byKind[$entry['kind']] = ($byKind[$entry['kind']] ?? 0) + 1;
        }
        arsort($byGateway);
        arsort($byKind);

        // errors first so the worst news is never pushed off the end of a
        // long list, then newest first within the same severity
        $rank = ['critical' => 0, 'error' => 1, 'warning' => 2, 'info' => 3, 'debug' => 4];
        usort($entries, function ($a, $b) use ($rank) {
            $sa = $rank[$a['level']] ?? 9;
            $sb = $rank[$b['level']] ?? 9;
            return $sa === $sb ? strcmp($b['time'], $a['time']) : $sa <=> $sb;
        });

        return [
            'total' => count($entries),
            'window_hours' => $window,
            'by_gateway' => $byGateway,
            'by_kind' => $byKind,
            'entries' => array_slice($entries, 0, 25),
            'generated_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    private function renderDigest(array $digest): void
    {
        $this->line("Payment digest (window: {$digest['window_hours']}h, generated: {$digest['generated_at']})");
        $this->line("Total failures: {$digest['total']}");
        foreach ($digest['by_gateway'] as $gateway => $count) {
            $this->line("  gateway {$gateway}: {$count}");
        }
        foreach ($digest['by_kind'] as $kind => $count) {
            $this->line("  kind {$kind}: {$count}");
        }
        foreach ($digest['entries'] as $entry) {
            $detail = $entry['detail'] !== '' ? ": {$entry['detail']}" : '';
            $this->line("  [{$entry['time']}] {$entry['level']} {$entry['gateway']}/{$entry['kind']} {$entry['message']}{$detail}");
        }
    }
}