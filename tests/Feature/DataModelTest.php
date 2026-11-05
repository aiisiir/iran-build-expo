<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Edition;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class DataModelTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
    }

    public function test_slug_is_generated_from_the_persian_title_and_is_unique_per_language(): void
    {
        $a = $this->makePage('fa', ['title' => 'درباره ما']);
        $b = $this->makePage('fa', ['title' => 'درباره ما']);
        $en = $this->makePage('en', ['title' => 'درباره ما']);

        $this->assertSame('درباره-ما', $a->slug);
        $this->assertSame('درباره-ما-2', $b->slug);
        $this->assertSame('درباره-ما', $en->slug); // another language may reuse the slug
    }

    public function test_a_soft_deleted_slug_stays_reserved(): void
    {
        $page = $this->makePage('fa', ['title' => 'تماس']);
        $page->delete();

        $this->assertSame('تماس-2', $this->makePage('fa', ['title' => 'تماس'])->slug);
        $this->assertDatabaseHas('pages', ['id' => $page->id]); // row is still in the database
        $this->assertNotNull(Page::withTrashed()->find($page->id)->deleted_at);
    }

    public function test_reserved_words_are_never_used_as_page_slugs(): void
    {
        $this->assertSame('badge-2', $this->makePage('fa', ['title' => 'badge'])->slug);
    }

    public function test_post_slug_is_unique_inside_language_and_page_only(): void
    {
        $news = $this->makePage('fa', ['title' => 'اخبار']);
        $other = $this->makePage('fa', ['title' => 'اطلاعیه']);

        $this->assertSame('خبر-آزمایشی', $this->makePost($news)->slug);
        $this->assertSame('خبر-آزمایشی-2', $this->makePost($news)->slug);
        $this->assertSame('خبر-آزمایشی', $this->makePost($other)->slug);
    }

    public function test_only_one_default_language_and_one_home_page_per_language(): void
    {
        $en = $this->lang('en');
        $en->claimFlag('is_default');
        $this->assertSame('en', Language::default()->code);
        $this->assertSame(1, Language::where('is_default', 1)->count());

        $first = $this->makePage('fa', ['title' => 'خانه']);
        $second = $this->makePage('fa', ['title' => 'خانه دوم']);
        $first->claimFlag('is_home', ['language_id' => $first->language_id]);
        $second->claimFlag('is_home', ['language_id' => $second->language_id]);

        $this->assertSame([$second->id], Page::where('is_home', 1)->pluck('id')->all());
    }

    public function test_the_database_rejects_two_home_pages_in_one_language(): void
    {
        $a = $this->makePage('fa', ['title' => 'الف']);
        $b = $this->makePage('fa', ['title' => 'ب']);
        $a->forceFill(['is_home' => true])->save();

        $this->expectException(QueryException::class);
        $b->forceFill(['is_home' => true])->save();
    }

    public function test_a_translation_key_can_exist_once_per_language(): void
    {
        $this->makePage('fa', ['title' => 'درباره', 'translation_key' => 'about']);
        $this->makePage('en', ['title' => 'About', 'translation_key' => 'about']);

        $this->expectException(QueryException::class);
        $this->makePage('fa', ['title' => 'درباره دوم', 'translation_key' => 'about']);
    }

    public function test_only_one_current_edition(): void
    {
        $a = Edition::create(['code' => '1405', 'name' => 'A']);
        $b = Edition::create(['code' => '1406', 'name' => 'B']);
        $a->claimFlag('is_current');
        $b->claimFlag('is_current');

        $this->assertSame($b->id, Edition::current()->id);
    }

    public function test_rich_text_is_sanitized_when_saved(): void
    {
        $page = $this->makePage('fa', ['body' => '<p>متن</p><script>alert(1)</script>']);

        $this->assertStringNotContainsString('script', $page->fresh()->body);
        $this->assertStringContainsString('متن', $page->fresh()->body);
    }

    public function test_changes_are_audited_with_field_names_only(): void
    {
        $page = $this->makePage('fa', ['title' => 'قدیمی']);
        $page->update(['title' => 'جدید']);

        $event = AuditEvent::where('subject_type', 'Page')->where('event', 'updated')->latest('id')->first();
        $this->assertContains('title', json_decode($event->changes, true));
        $this->assertStringNotContainsString('جدید', (string) $event->changes);
    }

    public function test_publish_window_is_evaluated_at_query_time(): void
    {
        $page = $this->makePage('fa', ['title' => 'اخبار']);
        $live = $this->makePost($page, ['title' => 'منتشر شده']);
        $this->makePost($page, ['title' => 'آینده', 'published_at' => now()->addDay()]);
        $this->makePost($page, ['title' => 'منقضی', 'published_at' => now()->subDays(2), 'expires_at' => now()->subDay()]);
        $this->makePost($page, ['title' => 'پیش‌نویس', 'status' => 'draft']);

        $this->assertSame([$live->id], Post::live()->pluck('id')->all());
    }
}
