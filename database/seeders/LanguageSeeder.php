<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $fa = Language::updateOrCreate(['code' => 'fa'], ['name' => 'Persian', 'native_name' => 'فارسی', 'direction' => 'rtl', 'sort_order' => 1]);
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'sort_order' => 2]);

        $fa->claimFlag('is_default');
    }
}
