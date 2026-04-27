<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Source priority for catalog dedup
    |--------------------------------------------------------------------------
    |
    | Ordered list of identifiers used in the `source_system` column when
    | resolving duplicate rows (same canonical brand + version_name_raw).
    | Earlier entries win over later entries. Identifiers not listed here
    | fall to the lowest priority.
    |
    | Configure the actual identifiers per environment via the
    | INFOAUTO_SOURCE_PRIORITY env variable (comma-separated).
    |
    */

    'priority' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('INFOAUTO_SOURCE_PRIORITY', ''))
    ))),
];
