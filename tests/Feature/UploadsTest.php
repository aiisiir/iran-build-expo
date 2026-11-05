<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class UploadsTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
        $this->actingAs($this->editor());
    }

    protected function tearDown(): void
    {
        MediaAsset::all()->each(fn ($m) => File::delete(public_path('uploads/'.$m->path)));
        parent::tearDown();
    }

    private function jpegWithPayload(): UploadedFile
    {
        $image = imagecreatetruecolor(40, 30);
        ob_start();
        imagejpeg($image);
        $jpeg = ob_get_clean().'<?php system($_GET["c"]); ?>'; // polyglot: valid JPEG followed by PHP

        $path = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($path, $jpeg);

        return new UploadedFile($path, 'photo.php.jpg', 'image/jpeg', null, true);
    }

    public function test_images_are_reencoded_so_appended_payloads_are_dropped(): void
    {
        $this->post('/admin/media', ['file' => $this->jpegWithPayload()])->assertRedirect()->assertSessionHasNoErrors();

        $asset = MediaAsset::firstOrFail();
        $stored = file_get_contents(public_path('uploads/'.$asset->path));

        $this->assertStringEndsWith('.jpg', $asset->path);
        $this->assertStringNotContainsString('<?php', $stored);
        $this->assertSame(40, $asset->width);
        $this->assertDoesNotMatchRegularExpression('/php/i', $asset->path); // random name, client name never used
    }

    public function test_non_images_pretending_to_be_images_are_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('shell.png', '<?php echo 1;');

        $this->post('/admin/media', ['file' => $file])->assertSessionHasErrors('file');
        $this->assertSame(0, MediaAsset::count());
    }

    public function test_images_over_two_megabytes_are_rejected(): void
    {
        $file = UploadedFile::fake()->image('big.jpg', 3000, 3000)->size(2500);

        $this->post('/admin/media', ['file' => $file])->assertSessionHasErrors('file');
    }

    public function test_svg_is_stored_untouched_as_an_image_file(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><circle r="4"/></svg>';
        $file = UploadedFile::fake()->createWithContent('logo.svg', $svg);

        $this->post('/admin/media', ['file' => $file])->assertSessionHasNoErrors();

        $asset = MediaAsset::firstOrFail();
        $this->assertStringEndsWith('.svg', $asset->path);
        $this->assertSame('image/svg+xml', $asset->mime);
        $this->assertSame($svg, file_get_contents(public_path('uploads/'.$asset->path)));
        $this->assertNull($asset->width); // XML is never parsed
    }

    public function test_uploads_directory_blocks_script_execution(): void
    {
        $htaccess = file_get_contents(public_path('uploads/.htaccess'));

        $this->assertStringContainsString('Require all denied', $htaccess);
        $this->assertStringContainsString('php', $htaccess);
        $this->assertStringContainsString('sandbox', $htaccess); // SVG opened directly cannot run scripts
    }
}
