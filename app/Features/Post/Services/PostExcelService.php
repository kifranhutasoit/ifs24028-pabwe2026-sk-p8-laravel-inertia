<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelService
{
    public const HEADERS = ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status', 'Tanggal Publikasi'];

    public function __construct(private readonly PostService $postService) {}

    public function download(Collection $posts, string $filename, bool $withAuthor = false): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        $headers = $withAuthor ? [...self::HEADERS, 'Penulis'] : self::HEADERS;
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($posts as $post) {
            $values = [
                $post->title,
                $post->category->value,
                $post->excerpt,
                $post->content,
                $post->is_published ? 'Published' : 'Draft',
                $post->published_at?->format('Y-m-d H:i:s'),
            ];

            if ($withAuthor) {
                $values[] = $post->user?->name;
            }

            $sheet->fromArray($values, null, 'A'.$row++);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Impor artikel dari Excel. Kolom: Judul, Kategori, Ringkasan, Isi, Status. */
    public function import(UploadedFile $file, User $user): int
    {
        // Hanya izinkan reader Xlsx/Xls agar file non-Excel (mis. teks biasa) melempar error.
        $sheet = IOFactory::load(
            $file->getRealPath(),
            0,
            [IOFactory::READER_XLSX, IOFactory::READER_XLS]
        )->getActiveSheet();

        $rows = $sheet->toArray(null, true, false, false);
        array_shift($rows);

        $imported = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[3] ?? ''));

            if ($title === '' || $content === '') {
                continue;
            }

            $category = PostCategory::tryFrom(trim((string) ($row[1] ?? '')))?->value ?? PostCategory::Umum->value;

            $this->postService->create($user, [
                'title' => $title,
                'category' => $category,
                'excerpt' => ($row[2] ?? null) !== null ? (string) $row[2] : null,
                'content' => $content,
                'is_published' => strtolower(trim((string) ($row[4] ?? ''))) === 'published',
            ]);

            $imported++;
        }

        return $imported;
    }
}