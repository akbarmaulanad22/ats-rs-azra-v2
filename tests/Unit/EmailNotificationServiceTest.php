<?php

namespace Tests\Unit;

use App\Services\EmailNotificationService;
use PHPUnit\Framework\TestCase;

class EmailNotificationServiceTest extends TestCase
{
    private EmailNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailNotificationService;
    }

    public function test_render_replaces_single_placeholder(): void
    {
        $result = $this->service->render('Halo {nama_kandidat}!', ['nama_kandidat' => 'Budi']);

        $this->assertSame('Halo Budi!', $result);
    }

    public function test_render_replaces_multiple_placeholders(): void
    {
        $result = $this->service->render(
            'Yth. {nama_kandidat}, lamaran untuk {judul_lowongan} diterima.',
            ['nama_kandidat' => 'Sari', 'judul_lowongan' => 'Perawat ICU'],
        );

        $this->assertSame('Yth. Sari, lamaran untuk Perawat ICU diterima.', $result);
    }

    public function test_render_leaves_unknown_placeholders_intact(): void
    {
        $result = $this->service->render('Halo {nama_kandidat}!', []);

        $this->assertSame('Halo {nama_kandidat}!', $result);
    }

    public function test_render_handles_empty_string(): void
    {
        $result = $this->service->render('', ['nama_kandidat' => 'Budi']);

        $this->assertSame('', $result);
    }

    public function test_render_does_not_execute_php_tags(): void
    {
        $result = $this->service->render('{php_tag}', ['php_tag' => '<?php system("id"); ?>']);

        $this->assertSame('<?php system("id"); ?>', $result);
    }

    public function test_render_replaces_in_both_subject_and_body(): void
    {
        $subject = $this->service->render('Lamaran {judul_lowongan}', ['judul_lowongan' => 'Dokter Umum']);
        $body = $this->service->render('Posisi: {judul_lowongan}', ['judul_lowongan' => 'Dokter Umum']);

        $this->assertSame('Lamaran Dokter Umum', $subject);
        $this->assertSame('Posisi: Dokter Umum', $body);
    }
}
