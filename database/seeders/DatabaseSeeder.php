<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([LanguageSeeder::class, BadgeTypeSeeder::class, AdminSeeder::class]);

        // On = the site comes up with the sample content; off = a clean site to fill from the panel.
        if (config('app.seed_demo')) {
            $this->call(DemoContentSeeder::class);
        }
    }
}
