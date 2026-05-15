<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('no_telepon');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('jenis_kelamin')->nullable()->after('tanggal_lahir');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('status_perkawinan')->nullable()->after('agama');
            $table->string('golongan_darah')->nullable()->after('status_perkawinan');
            $table->text('alamat_ktp')->nullable()->after('golongan_darah');
            $table->text('alamat_domisili')->nullable()->after('alamat_ktp');
            $table->string('no_ktp', 20)->nullable()->after('alamat_domisili');
            $table->string('npwp', 30)->nullable()->after('no_ktp');
            $table->string('nama_ibu_kandung')->nullable()->after('npwp');
            $table->string('kontak_darurat_nama')->nullable()->after('nama_ibu_kandung');
            $table->string('kontak_darurat_no_telp', 20)->nullable()->after('kontak_darurat_nama');
            $table->string('kontak_darurat_hubungan')->nullable()->after('kontak_darurat_no_telp');
            $table->string('ayah_nama')->nullable()->after('kontak_darurat_hubungan');
            $table->unsignedSmallInteger('ayah_usia')->nullable()->after('ayah_nama');
            $table->string('ayah_pendidikan_terakhir')->nullable()->after('ayah_usia');
            $table->string('ayah_pekerjaan')->nullable()->after('ayah_pendidikan_terakhir');
            $table->string('ibu_nama')->nullable()->after('ayah_pekerjaan');
            $table->unsignedSmallInteger('ibu_usia')->nullable()->after('ibu_nama');
            $table->string('ibu_pendidikan_terakhir')->nullable()->after('ibu_usia');
            $table->string('ibu_pekerjaan')->nullable()->after('ibu_pendidikan_terakhir');
            $table->unsignedTinyInteger('saudara_anak_ke')->nullable()->after('ibu_pekerjaan');
            $table->unsignedTinyInteger('saudara_dari_bersaudara')->nullable()->after('saudara_anak_ke');
            $table->boolean('is_fresh_graduate')->default(false)->after('saudara_dari_bersaudara');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama',
                'status_perkawinan', 'golongan_darah', 'alamat_ktp', 'alamat_domisili',
                'no_ktp', 'npwp', 'nama_ibu_kandung',
                'kontak_darurat_nama', 'kontak_darurat_no_telp', 'kontak_darurat_hubungan',
                'ayah_nama', 'ayah_usia', 'ayah_pendidikan_terakhir', 'ayah_pekerjaan',
                'ibu_nama', 'ibu_usia', 'ibu_pendidikan_terakhir', 'ibu_pekerjaan',
                'saudara_anak_ke', 'saudara_dari_bersaudara', 'is_fresh_graduate',
            ]);
        });
    }
};
