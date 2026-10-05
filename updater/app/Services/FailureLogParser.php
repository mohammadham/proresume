<?php

namespace App\Services;

// Illuminate\Support\Carbon extends this class, so hinting on the base type
// accepts both without forcing callers to import a specific subclass
use Carbon\Carbon;

/**
 * Reads Laravel daily log files and returns complete log entries.
 *
 * A Laravel log record is NOT always one physical line: any context value
 * containing a newline (an exception trace, most of all) is written as a
 * single unescaped newline inside the JSON context, so the record spills over
 * into continuation lines ("#0 /path/to/file.php(12): ...").
 *
 * That breaks a naive line-by-line reader: the header line's JSON is left
 * unterminated, json_decode() fails, and the record degrades to "message with
 * no context" - exactly the part a digest needs (the error text, the gateway,
 * the transaction id). This parser therefore joins continuation lines onto the
 * record they belong to before decoding, so both compact single-line records
 * and multi-line trace records come back with a usable context array.
 */
class FailureLogParser
{
    /** "[2026-10-04 17:06:46] local.ERROR: message {json}" */
    private const HEADER = '/^\[(?<ts>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+\S+\.(?<level>[A-Z]+):\s(?<rest>.*)$/';

    /**
     * Parse one log file and return its entries oldest-first.
     *
     * @param  string  $path  absolute path to the log file
     * @return array<int, array{time: string, level: string, message: string, context: array, timestamp: int}>
     */
    public function parseFile(string $path): array
    {
        if (!is_file($path) || !($handle = fopen($path, 'r'))) {
            return [];
        }

        $entries = [];
        $current = null;

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            if (preg_match(self::HEADER, $line, $m)) {
                // a new record starts: flush the one we were assembling
                if ($current !== null) {
                    $entries[] = $this->finalize($current);
                }
                $current = ['ts' => $m['ts'], 'level' => strtolower($m['level']), 'rest' => $m['rest']];
                continue;
            }

            if ($current !== null) {
                // continuation of the previous record (a stack frame); the
                // leading "#N " marker keeps these from being mistaken for a
                // new record, and we rejoin with a space so the JSON stays
                // syntactically valid
                $current['rest'] .= ' ' . $line;
            }
        }

        if ($current !== null) {
            $entries[] = $this->finalize($current);
        }
        fclose($handle);

        return $entries;
    }

    /**
     * Parse every daily file a look-back window can touch, newest day first.
     *
     * The daily driver names files <channel>-Y-m-d.log, so the day list is
     * derived from the cutoff rather than hard-coded to "yesterday + today":
     * a 72h window must reach three files, a 6h window only one.
     *
     * @param  string  $basePath  channel path WITHOUT the date suffix
     * @return array<int, array{time: string, level: string, message: string, context: array, timestamp: int}>
     */
    public function parseChannel(string $basePath, Carbon $cutoff, string $suffix = '.log'): array
    {
        $prefix = preg_replace('/\.log$/', '', $basePath);

        // start at the day the cutoff falls in and walk forward to today
        $days = [];
        $day = $cutoff->copy()->startOfDay();
        $today = Carbon::now()->startOfDay();
        while ($day->lte($today)) {
            $days[] = $day->format('Y-m-d');
            $day->addDay();
        }

        $entries = [];
        foreach ($days as $d) {
            foreach ($this->parseFile($prefix . '-' . $d . $suffix) as $entry) {
                if ($entry['timestamp'] >= $cutoff->timestamp) {
                    $entries[] = $entry;
                }
            }
        }

        usort($entries, fn ($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        return $entries;
    }

    /**
     * Split "message {json context}" into its two halves.
     *
     * Handles the three layouts this codebase produces:
     *   - "message {json}"     standard Laravel
     *   - "{json}"             JSON-as-the-message (Laravel 9 context-as-message)
     *   - "message"            no context at all
     */
    private function finalize(array $current): array
    {
        $rest = $current['rest'];
        $message = $rest;
        $context = [];

        if (isset($rest[0]) && $rest[0] === '{') {
            $decoded = json_decode($rest, true);
            if (is_array($decoded)) {
                $message = $decoded['event'] ?? $rest;
                $context = $decoded;
            }
        } else {
            // Find the " {" that actually opens the context object. Scanning
            // for the LAST one is wrong: a re-joined stack trace can contain
            // " {" sequences of its own (frame arguments, array literals), and
            // picking one of those yields an undecodable tail and leaves the
            // whole record as message text. Walking every candidate from the
            // front and taking the first that parses as a JSON object is both
            // correct and cheap - the real context starts at the first brace.
            $offset = 0;
            while (($brace = strpos($rest, ' {', $offset)) !== false) {
                $decoded = json_decode(substr($rest, $brace + 1), true);
                if (is_array($decoded)) {
                    $message = substr($rest, 0, $brace);
                    $context = $decoded;
                    break;
                }
                $offset = $brace + 2;
            }
        }

        return [
            'time' => $current['ts'],
            'level' => $current['level'],
            'message' => trim($message),
            'context' => $context,
            'timestamp' => Carbon::createFromFormat('Y-m-d H:i:s', $current['ts'])->timestamp,
        ];
    }
}