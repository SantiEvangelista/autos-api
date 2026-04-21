<?php

use App\Models\InfoautoCatalog;

it('exposes external_id accessor with ia_ prefix', function () {
    $catalog = InfoautoCatalog::factory()->create();

    expect($catalog->external_id)->toBe("ia_{$catalog->id}");
});

it('returns same external_id after reload', function () {
    $catalog = InfoautoCatalog::factory()->create();

    $expected = $catalog->external_id;
    $reloaded = InfoautoCatalog::find($catalog->id);

    expect($reloaded->external_id)->toBe($expected);
});

it('does not persist external_id as column', function () {
    $catalog = InfoautoCatalog::factory()->create();

    $attrs = $catalog->getAttributes();
    expect(array_key_exists('external_id', $attrs))->toBeFalse();
});
