<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelTest extends TestCase
{
    use RefreshDatabase;

    private function makeXlsx(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'artikel.xlsx', null, null, true);
    }

    public function test_member_exports_only_own_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->create(['title' => 'Milik Saya']);
        Post::factory()->draft()->for($user)->create();
        Post::factory()->create(['title' => 'Bukan Saya']);

        $response = $this->actingAs($user)->get('/dashboard/posts/export');

        $response->assertOk()->assertDownload('artikel-saya.xlsx');
        $this->assertNotEmpty($response->streamedContent());
    }

    public function test_member_imports_valid_rows_and_skips_invalid(): void
    {
        $user = User::factory()->create();

        $file = $this->makeXlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'],
            ['Artikel A', 'Teknologi', 'Ringkas A', 'Isi A', 'Published'],
            ['Artikel B', 'Tidak Valid', null, 'Isi B', 'Draft'],
            ['', 'Umum', 'x', 'tanpa judul', 'Draft'],
            ['Tanpa Isi', 'Umum', 'x', '', 'Draft'],
        ]);

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('success', '2 artikel berhasil diimpor.');

        $this->assertDatabaseHas('posts', ['title' => 'Artikel A', 'category' => 'Teknologi', 'is_published' => true, 'user_id' => $user->id]);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel B', 'category' => 'Umum', 'is_published' => false, 'excerpt' => null]);
        $this->assertDatabaseCount('posts', 2);
    }

    public function test_import_rejects_wrong_file_type(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/import', ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('file');
    }

    public function test_import_reports_unreadable_file(): void
    {
        $broken = UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan excel sungguhan');

        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/import', ['file' => $broken])
            ->assertSessionHas('error');
    }
}
