<?php

namespace Database\Seeders;

use App\Models\BadgeType;
use Illuminate\Database\Seeder;

class BadgeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['مدیر' => '#1d3557', 'مهمان' => '#2a9d8f', 'خبرنگار' => '#e76f51', 'غرفه‌دار' => '#e9a100', 'خدماتی' => '#6c757d'];
        $order = 1;

        foreach ($types as $name => $color) {
            BadgeType::updateOrCreate(['name' => $name], ['color' => $color, 'sort_order' => $order++]);
        }
    }
}
