<?php

// Test-only override: make sleep()/usleep() no-ops inside scrape command namespaces.
// When PHP resolves an unqualified function call inside a namespaced file it first
// looks up the symbol in the current namespace, then falls back to the global one.
// Declaring these here short-circuits the global sleep/usleep for scrape commands
// so tests that exercise retry/rate-limit paths don't actually block.
//
// Notably unblocks: ScrapeInfoautoPricesSaveTest 'handles batch upsert for more than
// 500 entries' — which otherwise triggers the hourly rate-limit sleep(3600) repeatedly
// once the 510 mocked list-price requests cross SAFE_MAX_REQUESTS_PER_HOUR=45.

namespace App\Console\Commands {
    if (! function_exists(__NAMESPACE__ . '\\sleep')) {
        function sleep(int $seconds): int
        {
            return 0;
        }
    }

    if (! function_exists(__NAMESPACE__ . '\\usleep')) {
        function usleep(int $microseconds): void
        {
            // no-op
        }
    }
}
