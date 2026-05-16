<?php

namespace Database\Seeders;

use App\Enums\DiscDimension;
use App\Models\DiscQuestion;
use Illuminate\Database\Seeder;

class DiscQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // 28 forced-choice question groups. Each group has 4 words tagged D/I/S/C.
        // Scoring: "most like me" count per dimension determines profile.
        $questions = [
            [
                ['teks' => 'Tegas', 'dimensi' => DiscDimension::D],
                ['teks' => 'Antusias', 'dimensi' => DiscDimension::I],
                ['teks' => 'Sabar', 'dimensi' => DiscDimension::S],
                ['teks' => 'Teliti', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Berani', 'dimensi' => DiscDimension::D],
                ['teks' => 'Ramah', 'dimensi' => DiscDimension::I],
                ['teks' => 'Setia', 'dimensi' => DiscDimension::S],
                ['teks' => 'Cermat', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Kompetitif', 'dimensi' => DiscDimension::D],
                ['teks' => 'Berpengaruh', 'dimensi' => DiscDimension::I],
                ['teks' => 'Tenang', 'dimensi' => DiscDimension::S],
                ['teks' => 'Sistematis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Langsung', 'dimensi' => DiscDimension::D],
                ['teks' => 'Ekspresif', 'dimensi' => DiscDimension::I],
                ['teks' => 'Konsisten', 'dimensi' => DiscDimension::S],
                ['teks' => 'Analitis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Dominan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Menginspirasi', 'dimensi' => DiscDimension::I],
                ['teks' => 'Kooperatif', 'dimensi' => DiscDimension::S],
                ['teks' => 'Akurat', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Percaya Diri', 'dimensi' => DiscDimension::D],
                ['teks' => 'Optimistis', 'dimensi' => DiscDimension::I],
                ['teks' => 'Stabil', 'dimensi' => DiscDimension::S],
                ['teks' => 'Hati-Hati', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Mandiri', 'dimensi' => DiscDimension::D],
                ['teks' => 'Persuasif', 'dimensi' => DiscDimension::I],
                ['teks' => 'Dapat Diandalkan', 'dimensi' => DiscDimension::S],
                ['teks' => 'Terorganisir', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tegas dalam Keputusan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Menarik Perhatian', 'dimensi' => DiscDimension::I],
                ['teks' => 'Pendengar yang Baik', 'dimensi' => DiscDimension::S],
                ['teks' => 'Berorientasi Detail', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Proaktif', 'dimensi' => DiscDimension::D],
                ['teks' => 'Mudah Bergaul', 'dimensi' => DiscDimension::I],
                ['teks' => 'Tidak Suka Konflik', 'dimensi' => DiscDimension::S],
                ['teks' => 'Mengikuti Prosedur', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Pemimpin', 'dimensi' => DiscDimension::D],
                ['teks' => 'Menghibur', 'dimensi' => DiscDimension::I],
                ['teks' => 'Suportif', 'dimensi' => DiscDimension::S],
                ['teks' => 'Perfeksionis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tangguh', 'dimensi' => DiscDimension::D],
                ['teks' => 'Bersemangat', 'dimensi' => DiscDimension::I],
                ['teks' => 'Sabar Menghadapi Masalah', 'dimensi' => DiscDimension::S],
                ['teks' => 'Berbasis Data', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Berorientasi Hasil', 'dimensi' => DiscDimension::D],
                ['teks' => 'Kreatif', 'dimensi' => DiscDimension::I],
                ['teks' => 'Harmonis', 'dimensi' => DiscDimension::S],
                ['teks' => 'Logis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tegas Menegakkan Aturan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Mudah Antusias', 'dimensi' => DiscDimension::I],
                ['teks' => 'Setia pada Kelompok', 'dimensi' => DiscDimension::S],
                ['teks' => 'Menganalisis Sebelum Bertindak', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Berambisi', 'dimensi' => DiscDimension::D],
                ['teks' => 'Penuh Energi', 'dimensi' => DiscDimension::I],
                ['teks' => 'Setia Kawan', 'dimensi' => DiscDimension::S],
                ['teks' => 'Metodis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tidak Mudah Menyerah', 'dimensi' => DiscDimension::D],
                ['teks' => 'Suka Berbagi Cerita', 'dimensi' => DiscDimension::I],
                ['teks' => 'Menghindari Perubahan Mendadak', 'dimensi' => DiscDimension::S],
                ['teks' => 'Memeriksa Ulang Pekerjaan', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Suka Tantangan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Penuh Ide', 'dimensi' => DiscDimension::I],
                ['teks' => 'Menghargai Kebersamaan', 'dimensi' => DiscDimension::S],
                ['teks' => 'Mengutamakan Kualitas', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tegas Mengambil Keputusan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Suka Bersosialisasi', 'dimensi' => DiscDimension::I],
                ['teks' => 'Menjaga Keseimbangan', 'dimensi' => DiscDimension::S],
                ['teks' => 'Suka Standar yang Tinggi', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Cepat Mengambil Tindakan', 'dimensi' => DiscDimension::D],
                ['teks' => 'Komunikatif', 'dimensi' => DiscDimension::I],
                ['teks' => 'Tidak Terburu-Buru', 'dimensi' => DiscDimension::S],
                ['teks' => 'Berdasar Fakta', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tidak Mudah Dipengaruhi', 'dimensi' => DiscDimension::D],
                ['teks' => 'Terbuka dan Hangat', 'dimensi' => DiscDimension::I],
                ['teks' => 'Mendukung Orang Lain', 'dimensi' => DiscDimension::S],
                ['teks' => 'Presisi', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Berani Berpendapat', 'dimensi' => DiscDimension::D],
                ['teks' => 'Mudah Percaya Orang', 'dimensi' => DiscDimension::I],
                ['teks' => 'Lebih Suka Rutinitas', 'dimensi' => DiscDimension::S],
                ['teks' => 'Suka Aturan Jelas', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Keras Kepala', 'dimensi' => DiscDimension::D],
                ['teks' => 'Impulsif', 'dimensi' => DiscDimension::I],
                ['teks' => 'Mudah Beradaptasi', 'dimensi' => DiscDimension::S],
                ['teks' => 'Hati-Hati dalam Bertindak', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Inisiatif Tinggi', 'dimensi' => DiscDimension::D],
                ['teks' => 'Suka Diakui', 'dimensi' => DiscDimension::I],
                ['teks' => 'Suka Membantu', 'dimensi' => DiscDimension::S],
                ['teks' => 'Berpikir Kritis', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Gigih', 'dimensi' => DiscDimension::D],
                ['teks' => 'Menyenangkan', 'dimensi' => DiscDimension::I],
                ['teks' => 'Toleran', 'dimensi' => DiscDimension::S],
                ['teks' => 'Terstruktur', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Tegas Menetapkan Batas', 'dimensi' => DiscDimension::D],
                ['teks' => 'Spontan', 'dimensi' => DiscDimension::I],
                ['teks' => 'Mengedepankan Harmoni', 'dimensi' => DiscDimension::S],
                ['teks' => 'Suka Evaluasi', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Otoriter', 'dimensi' => DiscDimension::D],
                ['teks' => 'Optimis', 'dimensi' => DiscDimension::I],
                ['teks' => 'Fleksibel', 'dimensi' => DiscDimension::S],
                ['teks' => 'Konservatif', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Orientasi pada Target', 'dimensi' => DiscDimension::D],
                ['teks' => 'Spontanitas Tinggi', 'dimensi' => DiscDimension::I],
                ['teks' => 'Menjaga Perasaan Orang', 'dimensi' => DiscDimension::S],
                ['teks' => 'Tepat dan Akurat', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Suka Mengontrol', 'dimensi' => DiscDimension::D],
                ['teks' => 'Mudah Bersemangat', 'dimensi' => DiscDimension::I],
                ['teks' => 'Suka Ketertiban', 'dimensi' => DiscDimension::S],
                ['teks' => 'Waspada', 'dimensi' => DiscDimension::C],
            ],
            [
                ['teks' => 'Berani Mengambil Risiko', 'dimensi' => DiscDimension::D],
                ['teks' => 'Mudah Membuat Teman', 'dimensi' => DiscDimension::I],
                ['teks' => 'Tidak Suka Tekanan', 'dimensi' => DiscDimension::S],
                ['teks' => 'Teliti dalam Detail', 'dimensi' => DiscDimension::C],
            ],
        ];

        foreach ($questions as $urutan => $words) {
            $question = DiscQuestion::firstOrCreate(['urutan' => $urutan + 1]);

            if ($question->wasRecentlyCreated) {
                foreach ($words as $word) {
                    $question->words()->create([
                        'teks' => $word['teks'],
                        'dimensi' => $word['dimensi'],
                    ]);
                }
            }
        }
    }
}
