<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul_posisi' => $this->faker->jobTitle(),
            'unit_id' => Unit::factory(),
            'workflow_template_snapshot_id' => WorkflowTemplateSnapshot::factory(),
            'jenis_pekerjaan' => $this->faker->randomElement(EmploymentType::cases()),
            'deskripsi_pekerjaan' => $this->faker->paragraph(),
            'kualifikasi' => $this->faker->paragraph(),
            'flyer_path' => 'flyers/'.Str::random(40).'.jpg',
            'jumlah_posisi' => $this->faker->numberBetween(1, 10),
            'tenggat_lamaran' => $this->faker->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'status' => VacancyStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => VacancyStatus::Published]);
    }

    /**
     * Generate a real portrait poster image on the public disk.
     * Use for demo/seed data so the career page renders actual flyers.
     */
    public function withGeneratedFlyer(): static
    {
        return $this->state(function (array $attributes): array {
            $path = 'flyers/'.Str::random(40).'.png';

            Storage::disk('public')->put($path, $this->generateFlyerImage($attributes['judul_posisi'] ?? 'Lowongan'));

            return ['flyer_path' => $path];
        });
    }

    /**
     * Generate a simple portrait poster PNG (600x800) via GD with the title text.
     */
    private function generateFlyerImage(string $title): string
    {
        $image = imagecreatetruecolor(600, 800);
        $bg = imagecolorallocate($image, random_int(0, 80), random_int(80, 160), random_int(100, 150));
        $fg = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 600, 800, $bg);
        imagestring($image, 5, 40, 360, wordwrap($title, 40), $fg);
        imagestring($image, 3, 40, 400, 'RS Azra · Lowongan Kerja', $fg);

        ob_start();
        imagepng($image);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return $contents;
    }

    public function closed(): static
    {
        return $this->state(['status' => VacancyStatus::Closed]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => VacancyStatus::Published,
            'tenggat_lamaran' => now()->subDay()->format('Y-m-d'),
        ]);
    }
}
