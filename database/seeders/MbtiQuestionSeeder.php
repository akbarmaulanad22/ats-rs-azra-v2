<?php

namespace Database\Seeders;

use App\Models\MbtiQuestion;
use Illuminate\Database\Seeder;

class MbtiQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // 18 EI + 17 SN + 18 TF + 17 JP = 70 questions
        // kutub_a = pole that option A represents
        $questions = [
            // ===== E/I (18 questions) =====
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya merasa berenergi setelah berinteraksi dengan banyak orang.', 'pernyataan_b' => 'Saya merasa lelah setelah lama berinteraksi dengan banyak orang dan butuh waktu sendiri.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya mudah memulai percakapan dengan orang yang baru saya kenal.', 'pernyataan_b' => 'Saya lebih suka menunggu orang lain yang memulai percakapan terlebih dahulu.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya lebih suka bekerja sendiri atau dalam kelompok kecil yang sudah saya kenal.', 'pernyataan_b' => 'Saya merasa nyaman bekerja dalam kelompok besar dengan banyak orang.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya sering berbagi pendapat saya secara spontan tanpa berpikir panjang.', 'pernyataan_b' => 'Saya biasanya berpikir matang terlebih dahulu sebelum berbicara.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Keramaian dan suasana ramai membuat saya bersemangat.', 'pernyataan_b' => 'Keramaian membuat saya merasa terkuras dan butuh istirahat setelahnya.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya membutuhkan waktu sendiri untuk merasa nyaman dengan orang baru.', 'pernyataan_b' => 'Saya mudah bergaul dan cepat akrab dengan siapa saja.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya lebih suka diskusi kelompok untuk memecahkan masalah.', 'pernyataan_b' => 'Saya lebih suka merenung sendiri untuk memecahkan masalah.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya membutuhkan waktu sendirian untuk mengisi ulang energi saya.', 'pernyataan_b' => 'Bertemu orang banyak dan bersosialisasi mengisi ulang energi saya.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya cenderung berpikir keras dan bicara saat memecahkan masalah.', 'pernyataan_b' => 'Saya cenderung berpikir dalam diam sebelum menyampaikan kesimpulan.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya lebih menikmati pekerjaan yang membutuhkan fokus dan konsentrasi mendalam.', 'pernyataan_b' => 'Saya lebih menikmati pekerjaan yang melibatkan interaksi dengan banyak orang.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya lebih suka pertemuan tatap muka daripada komunikasi melalui tulisan.', 'pernyataan_b' => 'Saya lebih suka berkomunikasi melalui tulisan daripada bertemu langsung.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya senang menjadi pusat perhatian dalam kelompok.', 'pernyataan_b' => 'Saya lebih suka berada di latar belakang dan mengamati.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya mendapat lebih banyak inspirasi dari dunia dalam diri saya sendiri.', 'pernyataan_b' => 'Saya mendapat lebih banyak energi dan inspirasi dari lingkungan dan orang sekitar.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya merasa lebih produktif ketika bekerja bersama orang lain.', 'pernyataan_b' => 'Saya merasa lebih produktif ketika bekerja sendirian.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya memilih dengan hati-hati kepada siapa saya berbagi hal-hal pribadi.', 'pernyataan_b' => 'Saya mudah berbagi cerita dan informasi pribadi dengan orang yang baru dikenal.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya lebih senang menghadiri acara sosial daripada menyendiri di rumah.', 'pernyataan_b' => 'Saya lebih senang menikmati waktu tenang sendiri daripada menghadiri acara ramai.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'E', 'pernyataan_a' => 'Saya sering mengambil inisiatif untuk memulai pembicaraan dalam kelompok.', 'pernyataan_b' => 'Saya lebih suka membiarkan percakapan berkembang secara alami tanpa berinisiatif memulai.'],
            ['dikotomi' => 'EI', 'kutub_a' => 'I', 'pernyataan_a' => 'Saya lebih memilih memiliki sedikit teman dekat daripada banyak kenalan.', 'pernyataan_b' => 'Saya senang memiliki jaringan pertemanan yang luas dengan banyak orang.'],

            // ===== S/N (17 questions) =====
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih percaya pada fakta dan pengalaman yang sudah terbukti.', 'pernyataan_b' => 'Saya lebih percaya pada intuisi dan kemungkinan yang belum tentu terjadi.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih menikmati pekerjaan yang praktis, konkret, dan dapat langsung diterapkan.', 'pernyataan_b' => 'Saya lebih menikmati pekerjaan yang teoritis, konseptual, dan berorientasi masa depan.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya lebih suka gambaran besar dan kebebasan menentukan cara untuk mencapai tujuan.', 'pernyataan_b' => 'Saya lebih suka instruksi yang jelas, terperinci, dan langkah demi langkah.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih fokus pada apa yang nyata dan terjadi saat ini.', 'pernyataan_b' => 'Saya sering memikirkan kemungkinan dan peluang di masa depan.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih memperhatikan detail-detail kecil daripada gambaran besar.', 'pernyataan_b' => 'Saya lebih memperhatikan pola dan gambaran besar daripada detail-detail kecil.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya sering terinspirasi oleh ide-ide baru yang belum pernah dicoba sebelumnya.', 'pernyataan_b' => 'Saya lebih suka melanjutkan cara yang sudah terbukti berhasil.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya menggunakan pengalaman masa lalu sebagai panduan utama dalam membuat keputusan.', 'pernyataan_b' => 'Saya menggunakan imajinasi dan inspirasi baru dalam membuat keputusan.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih menyukai pekerjaan dengan langkah-langkah yang jelas dan terstruktur.', 'pernyataan_b' => 'Saya lebih menyukai pekerjaan yang membutuhkan kreativitas dan inovasi.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih suka deskripsi yang literal, tepat, dan mudah diverifikasi.', 'pernyataan_b' => 'Saya lebih suka deskripsi yang metaforis, imajinatif, dan penuh makna.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya lebih tertarik belajar dari teori dan konsep daripada praktik langsung.', 'pernyataan_b' => 'Saya lebih suka belajar dari pengalaman praktis dan langsung.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih tertarik pada hal-hal yang nyata dan dapat saya lihat serta sentuh.', 'pernyataan_b' => 'Saya lebih tertarik pada hal-hal yang mungkin terjadi dan dapat saya bayangkan.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya cenderung mencari pendekatan baru yang belum pernah dicoba sebelumnya.', 'pernyataan_b' => 'Saya cenderung menangani masalah dengan cara yang sudah terbukti berhasil.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih memperhatikan fakta spesifik daripada kemungkinan yang lebih luas.', 'pernyataan_b' => 'Saya lebih memperhatikan kemungkinan yang lebih luas daripada fakta spesifik.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya lebih senang menciptakan dan merancang sesuatu yang sama sekali baru.', 'pernyataan_b' => 'Saya lebih senang menyempurnakan dan mengoptimalkan yang sudah ada.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Ketika membaca petunjuk, saya mengikutinya langkah demi langkah dengan teliti.', 'pernyataan_b' => 'Ketika membaca petunjuk, saya mencari pola dan prinsip umumnya terlebih dahulu.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'N', 'pernyataan_a' => 'Saya sering melihat hubungan dan pola tersembunyi antar hal yang berbeda.', 'pernyataan_b' => 'Saya lebih fokus pada informasi yang tersedia secara langsung dan konkret.'],
            ['dikotomi' => 'SN', 'kutub_a' => 'S', 'pernyataan_a' => 'Saya lebih nyaman mengerjakan tugas yang sudah saya kuasai dengan baik.', 'pernyataan_b' => 'Saya lebih tertantang oleh tugas-tugas baru yang memerlukan pemikiran kreatif.'],

            // ===== T/F (18 questions) =====
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya membuat keputusan berdasarkan logika, analisis, dan data objektif.', 'pernyataan_b' => 'Saya membuat keputusan berdasarkan perasaan, nilai-nilai pribadi, dan dampak pada orang lain.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih mengutamakan keadilan dan konsistensi daripada keharmonisan.', 'pernyataan_b' => 'Saya lebih mengutamakan keharmonisan hubungan daripada keadilan yang kaku.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'F', 'pernyataan_a' => 'Ketika ada konflik, saya mencari solusi yang memuaskan semua pihak yang terlibat.', 'pernyataan_b' => 'Ketika ada konflik, saya mencari solusi yang paling logis meskipun tidak semua pihak puas.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih mudah menyampaikan kritik secara jujur meskipun mungkin menyakitkan.', 'pernyataan_b' => 'Saya selalu mempertimbangkan perasaan orang lain sebelum menyampaikan kritik.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih mengandalkan nalar dan logika daripada perasaan dalam mengambil keputusan.', 'pernyataan_b' => 'Saya lebih mengandalkan empati dan intuisi sosial daripada logika dalam mengambil keputusan.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'F', 'pernyataan_a' => 'Saya cenderung menyampaikan penilaian dengan cara yang tidak menyakiti perasaan orang.', 'pernyataan_b' => 'Saya cenderung memberi penilaian yang jujur dan langsung meskipun kurang menyenangkan.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya percaya konsistensi dalam penerapan aturan lebih penting dari membuat pengecualian.', 'pernyataan_b' => 'Saya percaya pengecualian dalam aturan bisa dibuat jika situasi dan kondisinya memerlukan.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Ketika berdebat, saya fokus pada fakta, data, dan argumen yang dapat dibuktikan.', 'pernyataan_b' => 'Ketika berdebat, saya mempertimbangkan dampak emosional dan hubungan antar orang.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih suka dipuji atas kompetensi dan kemampuan intelektual saya.', 'pernyataan_b' => 'Saya lebih suka dipuji atas kepedulian dan perhatian saya kepada orang lain.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'F', 'pernyataan_a' => 'Saya percaya menjaga perasaan orang lain sama pentingnya dengan menyampaikan kebenaran.', 'pernyataan_b' => 'Saya percaya kebenaran dan kejujuran harus diutamakan di atas perasaan.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'F', 'pernyataan_a' => 'Ketika membantu orang, saya fokus pada memberikan dukungan emosional dan empati.', 'pernyataan_b' => 'Ketika membantu orang, saya fokus pada memberikan solusi praktis yang efektif.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih mementingkan efisiensi dan hasil daripada keharmonisan dalam tim.', 'pernyataan_b' => 'Saya lebih mementingkan keharmonisan tim daripada efisiensi semata.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya bisa bersikap tegas pada keputusan yang tidak populer jika saya yakin itu benar.', 'pernyataan_b' => 'Saya cenderung mempertimbangkan pendapat orang banyak sebelum membuat keputusan besar.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya merasa keputusan yang baik harus didasarkan pada data dan fakta yang kuat.', 'pernyataan_b' => 'Saya merasa keputusan yang baik harus mempertimbangkan dampaknya pada semua orang yang terlibat.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih mudah mengabaikan perasaan pribadi demi mencapai tujuan bersama.', 'pernyataan_b' => 'Saya sulit mengabaikan perasaan orang lain demi tujuan yang lebih besar.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya cenderung melihat masalah secara impersonal dan objektif tanpa melibatkan emosi.', 'pernyataan_b' => 'Saya cenderung melihat masalah dari sudut pandang orang-orang yang terlibat di dalamnya.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Dalam diskusi, saya lebih menghargai argumen yang kuat daripada kesepakatan semua pihak.', 'pernyataan_b' => 'Dalam diskusi, saya lebih menghargai keselarasan kelompok daripada mempertahankan argumen.'],
            ['dikotomi' => 'TF', 'kutub_a' => 'T', 'pernyataan_a' => 'Saya lebih ingin diakui sebagai orang yang cerdas dan kompeten.', 'pernyataan_b' => 'Saya lebih ingin diakui sebagai orang yang peduli dan hangat.'],

            // ===== J/P (17 questions) =====
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka merencanakan kegiatan jauh-jauh hari sebelum dilaksanakan.', 'pernyataan_b' => 'Saya lebih suka membiarkan kegiatan terjadi secara spontan dan menyesuaikan situasi.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa nyaman dan lebih produktif ketika segalanya terorganisir dengan baik.', 'pernyataan_b' => 'Saya merasa nyaman meskipun segalanya tidak terstruktur dan berjalan fleksibel.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya membuat daftar tugas dan berusaha mengikutinya secara konsisten.', 'pernyataan_b' => 'Saya lebih suka menyesuaikan diri dengan situasi yang berkembang tanpa daftar yang kaku.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka menyelesaikan satu pekerjaan secara tuntas sebelum memulai yang lain.', 'pernyataan_b' => 'Saya lebih suka mengerjakan beberapa hal sekaligus dan berpindah-pindah sesuai mood.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa tidak nyaman dan terganggu ketika jadwal berubah secara mendadak.', 'pernyataan_b' => 'Saya menikmati ketika rencana berubah dan ada hal-hal baru yang tidak terduga.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka membuat keputusan segera daripada terus menunda-nunda.', 'pernyataan_b' => 'Saya lebih suka menunda keputusan agar lebih banyak opsi dan informasi tersedia.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa lega dan puas setelah menyelesaikan dan menutup sebuah pekerjaan.', 'pernyataan_b' => 'Saya lebih menikmati proses pengerjaan daripada selesainya sebuah pekerjaan.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka rutinitas yang teratur, terjadwal, dan dapat diprediksi.', 'pernyataan_b' => 'Saya lebih suka variasi, fleksibilitas, dan kejutan dalam rutinitas sehari-hari.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya biasanya menyelesaikan pekerjaan jauh sebelum batas waktu tiba.', 'pernyataan_b' => 'Saya merasa bekerja paling baik justru ketika mendekati batas waktu.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa sulit untuk bersantai jika masih ada pekerjaan yang belum selesai.', 'pernyataan_b' => 'Saya bisa bersantai dan menikmati waktu meskipun masih ada pekerjaan yang menunggu.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka lingkungan kerja yang teratur, rapi, dan tertata dengan baik.', 'pernyataan_b' => 'Saya tidak terlalu terganggu dengan lingkungan kerja yang kurang teratur.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'P', 'pernyataan_a' => 'Saya lebih suka menjaga berbagai opsi tetap terbuka selama mungkin sebelum memutuskan.', 'pernyataan_b' => 'Saya lebih suka keputusan yang sudah final dan tidak perlu dipertimbangkan lagi.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa kurang nyaman dengan situasi yang ambigu dan tidak pasti.', 'pernyataan_b' => 'Saya cukup nyaman dengan ketidakpastian dan ambiguitas dalam pekerjaan.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya mengikuti jadwal yang sudah dibuat dengan cukup ketat dan disiplin.', 'pernyataan_b' => 'Saya mengikuti jadwal secara longgar dan fleksibel sesuai kebutuhan situasi.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih suka menyelesaikan proyek yang sedang berjalan sebelum memulai yang baru.', 'pernyataan_b' => 'Saya lebih bersemangat memulai proyek baru daripada menyelesaikan yang sudah lama berjalan.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya merasa lebih tenang dan fokus ketika semua rencana sudah tersusun dengan baik.', 'pernyataan_b' => 'Saya merasa lebih bersemangat ketika ada kebebasan untuk berimprovisasi dan bereksperimen.'],
            ['dikotomi' => 'JP', 'kutub_a' => 'J', 'pernyataan_a' => 'Saya lebih nyaman dengan kepastian dan kejelasan dalam setiap langkah pekerjaan.', 'pernyataan_b' => 'Saya lebih nyaman dengan fleksibilitas dan keterbukaan dalam menjalani pekerjaan.'],
        ];

        foreach ($questions as $index => $data) {
            MbtiQuestion::firstOrCreate(
                ['urutan' => $index + 1],
                $data
            );
        }
    }
}
