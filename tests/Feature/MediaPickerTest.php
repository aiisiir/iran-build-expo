<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Post;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function tearDown(): void
    {
        MediaAsset::withTrashed()->get()->each(fn ($m) => File::delete(public_path('uploads/'.$m->path)));
        parent::tearDown();
    }

    private function png(): UploadedFile
    {
        $image = imagecreatetruecolor(20, 20);
        $path = tempnam(sys_get_temp_dir(), 'png');
        imagepng($image, $path);

        return new UploadedFile($path, 'cover photo.png', 'image/png', null, true);
    }

    public function test_picker_lists_and_searches_media_and_quick_upload_is_flagged_inline(): void
    {
        $this->seedLanguages();
        $this->actingAs($this->admin());

        $this->postJson('/admin/media/quick', ['file' => $this->png(), 'alt' => 'نمای ساختمان'])->assertCreated()->assertJsonPath('image', true);
        $asset = MediaAsset::firstOrFail();
        $this->assertTrue($asset->is_inline);

        $this->getJson('/admin/media/picker?kind=image&q=cover')->assertOk()->assertJsonPath('data.0.id', $asset->id);
        $this->getJson('/admin/media/picker?q=%25')->assertOk()->assertJsonCount(0, 'data'); // "%" is searched literally
        $this->getJson('/admin/media/picker?q=ساختمان')->assertJsonPath('data.0.id', $asset->id);
        $this->postJson('/admin/media/quick', [])->assertUnprocessable();
    }

    public function test_media_field_only_accepts_images_for_image_fields_and_form_renders_the_popup_button(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        $post = Post::firstOrFail();
        $pdf = MediaAsset::create(['kind' => 'document', 'path' => 'x/doc.pdf', 'original_name' => 'doc.pdf', 'mime' => 'application/pdf', 'size' => 1]);

        $this->get("/admin/r/posts/{$post->id}/edit")->assertOk()->assertSee('data-media-open', false)->assertSee('id="media-picker"', false);
        $this->put("/admin/r/posts/{$post->id}", [
            'language_id' => $post->language_id, 'page_id' => $post->page_id, 'category_id' => $post->category_id, 'title' => $post->title,
            'slug' => $post->slug, 'status' => 'published', 'image_id' => $pdf->id,
        ])->assertSessionHasErrors('image_id');
    }

    public function test_permanent_delete_removes_inline_file_only_when_nothing_else_uses_it(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        [$a, $b] = [Post::skip(0)->firstOrFail(), Post::skip(1)->firstOrFail()];

        $this->postJson('/admin/media/quick', ['file' => $this->png()])->assertCreated();
        $shared = MediaAsset::firstWhere('is_inline', true);
        $a->forceFill(['image_id' => $shared->id])->save();
        $b->forceFill(['image_id' => $shared->id])->save();

        $a->delete();
        $this->post("/admin/r/posts/{$a->id}/purge")->assertRedirect('/admin/trash');
        $this->assertNotNull(MediaAsset::find($shared->id)); // still used by the other post
        $this->assertFileExists(public_path('uploads/'.$shared->path));

        $b->delete();
        $this->post("/admin/r/posts/{$b->id}/purge")->assertRedirect('/admin/trash');
        $this->assertNull(MediaAsset::withTrashed()->find($shared->id));
        $this->assertFileDoesNotExist(public_path('uploads/'.$shared->path));
    }

    public function test_library_files_are_never_removed_by_record_purge(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        $post = Post::firstOrFail();
        $this->post('/admin/media', ['file' => $this->png()]);
        $library = MediaAsset::firstOrFail();
        $post->forceFill(['image_id' => $library->id])->save();

        $post->delete();
        $this->post("/admin/r/posts/{$post->id}/purge");

        $this->assertNotNull(MediaAsset::find($library->id));
    }
}
