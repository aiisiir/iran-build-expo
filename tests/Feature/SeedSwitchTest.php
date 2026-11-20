<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_without_demo_gives_an_empty_site_and_a_closed_badge_form(): void
    {
        config(['app.seed_demo' => false]);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Page::count());
        $this->get('/fa/badge')->assertOk()->assertSee('ثبت‌نام کارت در حال حاضر باز نیست');
    }

    public function test_seed_with_demo_brings_the_sample_site_and_opens_the_badge_form(): void
    {
        config(['app.seed_demo' => true]);
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Page::count());
        $this->assertNotNull(Edition::current());
        $this->get('/fa/badge')->assertOk()->assertDontSee('ثبت‌نام کارت در حال حاضر باز نیست')->assertSee('badge-form', false);
    }
}
