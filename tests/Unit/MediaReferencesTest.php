<?php

namespace Tests\Unit;

use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaReferencesTest extends TestCase
{
    use RefreshDatabase;

    /** A new column that points at media_assets must be listed in MediaAsset::REFERENCES, or an unused-looking file could be deleted. */
    public function test_every_foreign_key_to_media_assets_is_registered(): void
    {
        $found = [];

        foreach (Schema::getTables() as $table) {
            foreach (Schema::getForeignKeys($table['name']) as $fk) {
                if ($fk['foreign_table'] === 'media_assets') {
                    array_push($found, ...array_map(fn ($c) => $table['name'].'.'.$c, $fk['columns']));
                }
            }
        }

        $registered = collect(MediaAsset::REFERENCES)->flatMap(fn ($cols, $table) => array_map(fn ($c) => "$table.$c", $cols))->all();
        sort($found);
        sort($registered);

        $this->assertSame($registered, $found);
    }
}
