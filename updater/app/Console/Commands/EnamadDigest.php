<?php

namespace App\Console\Commands;

use App\Mail\EnamadDigestMail;
use App\Models\Admin;
use App\Services\FailureLogParser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The Enamad verifier (enamad:verify) can fail in many ways - HTTP errors,
 * timeouts, malformed payloads - and every failure only lands in
 * storage/logs/enamad-*.log. Nothing surfaces those failures to a human:
 * this command reads that log for the requested window, classifies the
 * entries, emails a digest to the site admin and remembers how many
 * failures it already reported, so a repeated schedule run does not resend
 * unchanged news while genuinely new failures keep producing new digests.
 */
class EnamadDigest extends Command
{
    protected $signature = 'enamad:digest
        {--window=24 : Look-back window in hours}
        {--to= : Override the recipient (defaults to the first admin email)}
        {--dry : Build and display the digest without sending or remembering it}
        {--force : Send even when no new failures were reported since the last digest}';

    protected $description = 'Email a digest of recent Enamad verification failures to the admin';

    private const EVENTS = [
        'Scheduled Enamad verification failed' => 'verification_failed',
        'Scheduled Enamad verification returned a malformed payload' => 'malformed_payload',
        'Scheduled Enamad verification exception' => 'verification_exception',
    ];

    private const SENT_CACHE_KEY = 'enamad:digest:sent';

    public function handle(FailureLogParser $parser)
    {
        $window = max(1, (int) $this->option('window'));
        $cutoff = Carbon::now()->subHours($window);

        $entries = $this->collectEntries($parser, $cutoff);

        $counts = [];
        foreach ($entries as $entry) {
            $counts[$entry['event']] = ($counts[$entry['event']] ?? 0) + 1;
        }

        $digest = [
            'total' => count($entries),
            'window_hours' => $window,
            'counts' => $counts,
            'entries' => array_slice(array_reverse($entries), 0, 20),
            'generated_at' => Carbon::now()->toDateTimeString(),
        ];

        if ($this->option('dry')) {
            $this->renderDigest($digest);
            $this->line('Dry run: nothing was sent or remembered.');
            return 0;
        }

        if ($digest['total'] === 0) {
            $this->info("No Enamad verification failures in the last {$window}h; no digest sent.");
            return 0;
        }

        $sentCount = (int) Cache::get(self::SENT_CACHE_KEY, 0);
        if (!$this->option('force') && $digest['total'] <= $sentCount) {
            $this->info("No new Enamad verification failures since the last digest ({$digest['total']} known); nothing sent.");
            return 0;
        }

        $recipient = $this->option('to') ?: config('enamad.digest_recipient') ?: Admin::query()->value('email');
        if (!$recipient) {
            $this->error('No recipient available: pass --to=... or create an admin with an email address.');
            return 1;
        }

        try {
            Mail::to($recipient)->send(new EnamadDigestMail($digest));
        } catch (\Throwable $e) {
            // the sent-counter is intentionally left untouched so the next
            // scheduled run retries the delivery of these failures
            Log::channel('enamad')->error('Enamad digest email failed', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Digest email failed: ' . $e->getMessage());
            return 1;
        }

        Cache::put(self::SENT_CACHE_KEY, $digest['total']);

        $this->info("Enamad digest sent to {$recipient}: {$digest['total']} failure(s) in the last {$window}h.");
        return 0;
    }

    /**
     * Return the verifier failure entries that happened after $cutoff,
     * oldest first.
     *
     * Parsing lives in FailureLogParser because the enamad log shares the
     * multi-line stack-trace layout with every other channel, and a record
     * whose context spills over several lines used to lose its payload here
     * (the digest showed "verification_exception" with an empty detail).
     */
    private function collectEntries(FailureLogParser $parser, Carbon $cutoff): array
    {
        $path = config('logging.channels.enamad.path') ?: storage_path('logs/enamad.log');
        $entries = [];

        foreach ($parser->parseChannel($path, $cutoff) as $entry) {
            if (!isset(self::EVENTS[$entry['message']])) {
                continue;
            }
            $entries[] = [
                'time' => $entry['time'],
                'level' => $entry['level'],
                'event' => self::EVENTS[$entry['message']],
                'error' => isset($entry['context']['error']) ? mb_substr((string) $entry['context']['error'], 0, 200) : '',
                'body' => isset($entry['context']['body']) ? mb_substr((string) $entry['context']['body'], 0, 200) : '',
            ];
        }

        return $entries;
    }

    private function renderDigest(array $digest): void
    {
        $this->line("Enamad digest (window: {$digest['window_hours']}h, generated: {$digest['generated_at']})");
        $this->line("Total failures: {$digest['total']}");
        foreach ($digest['counts'] as $event => $count) {
            $this->line("  {$event}: {$count}");
        }
        foreach ($digest['entries'] as $entry) {
            $detail = $entry['error'] !== '' ? ": {$entry['error']}" : ($entry['body'] !== '' ? ": {$entry['body']}" : '');
            $this->line("  [{$entry['time']}] {$entry['event']}{$detail}");
        }
    }
}
