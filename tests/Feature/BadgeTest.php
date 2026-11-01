<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\BadgeType;
use App\Models\Edition;
use Database\Seeders\BadgeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class BadgeTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
        $this->seed(BadgeTypeSeeder::class);
        Storage::fake('local');
        Edition::create(['code' => '1405', 'name' => 'نمایشگاه ۱۴۰۵'])->claimFlag('is_current');
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'full_name' => 'علی رضایی', 'badge_type_id' => BadgeType::first()->id, 'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'email' => 'ali@example.com', 'gender' => 'male', 'consent' => 1,
            'photo' => UploadedFile::fake()->image('me.jpg', 300, 400),
        ];
    }

    public function test_a_visitor_can_request_a_badge_and_personal_data_is_encrypted_at_rest(): void
    {
        $this->post('/fa/badge', $this->payload())->assertRedirect('/fa/badge')->assertSessionHas('sent');

        $badge = Badge::firstOrFail();
        $this->assertSame('pending', $badge->status->value);
        $this->assertSame('09123456789', $badge->phone); // Persian digits normalised
        $this->assertNotSame('09123456789', DB::table('badges')->value('phone'));
        $this->assertNotSame('ali@example.com', DB::table('badges')->value('email'));
        Storage::disk('local')->assertExists($badge->photo_path);
        $this->assertStringStartsWith('badges/', $badge->photo_path);
    }

    public function test_required_fields_consent_and_honeypot_are_enforced(): void
    {
        $this->post('/fa/badge', $this->payload(['phone' => '', 'email' => '', 'gender' => '']))->assertSessionHasErrors(['phone', 'email', 'gender']);
        $this->post('/fa/badge', $this->payload(['consent' => 0]))->assertSessionHasErrors('consent');
        $this->post('/fa/badge', $this->payload(['website' => 'http://spam.test']))->assertSessionHasErrors('website');
        $this->assertSame(0, Badge::count());
    }

    public function test_the_form_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/fa/badge', $this->payload());
        }

        $this->post('/fa/badge', $this->payload())->assertStatus(429);
    }

    public function test_the_form_is_closed_without_a_current_edition(): void
    {
        Edition::query()->update(['is_current' => null]);

        $this->get('/fa/badge')->assertOk()->assertSee('ثبت‌نام کارت در حال حاضر باز نیست');
        $this->post('/fa/badge', $this->payload())->assertNotFound();
    }

    public function test_only_staff_can_see_photos_and_only_approved_badges_print(): void
    {
        $this->post('/fa/badge', $this->payload());
        $badge = Badge::firstOrFail();

        $this->get("/admin/badges/{$badge->id}/photo")->assertRedirect('/admin/login');

        $this->actingAs($this->editor());
        $this->get("/admin/badges/{$badge->id}/photo")->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/admin/badges/print', ['ids' => [$badge->id]])->assertOk()->assertDontSee('علی رضایی');

        $this->put("/admin/badges/{$badge->id}", [
            'full_name' => 'علی رضایی', 'badge_type_id' => $badge->badge_type_id, 'phone' => '09123456789', 'email' => 'ali@example.com', 'gender' => 'male', 'status' => 'approved',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post('/admin/badges/print', ['ids' => [$badge->id]])->assertOk()->assertSee('علی رضایی')->assertSee('class="badge"', false);
        $this->assertSame(1, $badge->fresh()->print_count);
        $this->assertNotNull($badge->fresh()->reviewed_by);
    }
}
