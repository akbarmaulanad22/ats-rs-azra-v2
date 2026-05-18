<x-layouts.public title="Lamar - {{ $vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

<style>
    .apply-wrap {
        max-width: 1320px; margin: 0 auto;
        padding: 56px 28px 80px;
        overflow-x: hidden;
    }
    .apply-back {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; color: #5a6864;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
        margin-bottom: 40px;
        transition: color 0.15s;
    }
    .apply-back:hover { color: rgb(0,119,116); }

    /* Wizard header */
    .apply-header {
        /*border-bottom: 1px solid #d9ddd9;*/
        padding-bottom: 32px;
        margin-bottom: 40px;
    }
    .apply-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #005f5c;
        text-transform: uppercase; letter-spacing: 0.14em;
        display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
    }
    .apply-eyebrow::before { content:""; width:28px; height:1px; background:rgb(0,119,116); }
    .apply-title {
        font-family: "IBM Plex Serif", serif;
        font-weight: 500; font-size: clamp(24px,3vw,36px);
        letter-spacing: -0.02em; margin: 0 0 6px; color: #0d1614;
    }
    .apply-subtitle { font-size: 14px; color: #5a6864; }

    /* Step progress */
    .step-progress {
        display: flex; gap: 0; margin: 32px 0 0;
        overflow-x: auto; padding-bottom: 2px;
    }
    .step-dot {
        display: flex; align-items: center; flex: 1; min-width: 0;
        cursor: default;
    }
    .step-dot:not(:last-child)::after {
        content: ""; flex: 1; height: 2px;
        background: #d9ddd9;
        margin: 0 4px; transition: background 0.3s;
    }
    .step-dot.done:not(:last-child)::after { background: rgb(0,119,116); }
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-family: "IBM Plex Mono", monospace;
        font-size: 12px; font-weight: 600;
        background: #efede5; color: #8a948f;
        border: 2px solid #d9ddd9;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .step-dot.active .step-circle {
        background: rgb(0,119,116); color: white;
        border-color: rgb(0,119,116);
    }
    .step-dot.done .step-circle {
        background: #e5f1f0; color: rgb(0,119,116);
        border-color: rgb(0,119,116);
    }

    /* Form layout */
    .apply-body {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 48px;
        align-items: start;
    }
    .apply-form-main { min-width: 0; }
    .apply-sidebar {
        position: sticky; top: 100px;
        min-width: 0;
    }

    /* Step panels */
    .step-panel { display: none; }
    .step-panel.active { display: block; }

    /* Section headings */
    .form-section-h {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; font-weight: 600;
        color: #0d1614; margin: 0 0 20px;
        border-top: 2px solid #0d1614;
        padding-top: 14px;
    }
    .form-section-sub {
        font-size: 13px; color: #5a6864; margin-bottom: 20px;
        font-style: italic;
    }

    /* Fields */
    .form-row {
        display: grid; gap: 16px;
        margin-bottom: 16px;
    }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .field {}
    .field label {
        display: block;
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; text-transform: uppercase;
        letter-spacing: 0.08em; color: #5a6864; font-weight: 600;
        margin-bottom: 6px;
    }
    .field label .req { color: #b54327; }
    .field input:not([type="checkbox"]):not([type="radio"]), .field select, .field textarea {
        width: 100%;
        border: 1px solid #d9ddd9;
        background: #fafaf9;
        padding: 10px 12px;
        font-size: 14px; color: #0d1614;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        outline: none;
        transition: border-color 0.15s;
        border-radius: 0;
        appearance: none;
    }
    .field input[type="checkbox"], .field input[type="radio"] { appearance: auto; }
    .field input:focus, .field select:focus, .field textarea:focus {
        border-color: rgb(0,119,116);
        background: #fff;
    }
    .field textarea { resize: vertical; min-height: 80px; }
    .field-error {
        font-size: 12px; color: #b54327; margin-top: 4px;
    }
    .field input.error, .field select.error, .field textarea.error {
        border-color: #b54327; background: #fdf4f2;
    }

    /* Adjustable sections */
    .adj-section { margin-bottom: 28px; }
    .adj-item {
        border: 1px solid #d9ddd9;
        padding: 18px;
        margin-bottom: 12px;
        position: relative;
        background: #fff;
    }
    .adj-item-num {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; text-transform: uppercase;
        letter-spacing: 0.06em; color: #8a948f; margin-bottom: 14px;
    }
    .adj-remove {
        position: absolute; top: 12px; right: 12px;
        background: none; border: none; cursor: pointer;
        color: #b54327; padding: 2px; line-height: 1;
        font-size: 18px;
    }
    .adj-add {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 16px;
        border: 1px dashed #0d1614;
        background: none; cursor: pointer;
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.08em; color: #2a3835;
        transition: border-color 0.15s, color 0.15s;
    }
    .adj-add:hover { border-color: rgb(0,119,116); color: rgb(0,119,116); }

    /* Nav buttons */
    .step-nav {
        display: flex; align-items: center;
        justify-content: space-between;
        margin-top: 40px; padding-top: 24px;
        border-top: 1px solid #d9ddd9;
        gap: 16px;
    }
    .btn-prev {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 20px;
        border: 1px solid #0d1614;
        background: white; color: #0d1614;
        font-size: 13px; font-weight: 600; cursor: pointer;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        transition: background 0.15s;
        border-radius: 0;
    }
    .btn-prev:hover { background: #efede5; }
    .btn-next, .btn-submit {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 24px;
        background: rgb(0,119,116); color: white;
        border: none; font-size: 13px; font-weight: 600;
        cursor: pointer; font-family: "IBM Plex Sans", system-ui, sans-serif;
        transition: background 0.15s;
        border-radius: 0;
    }
    .btn-next:hover, .btn-submit:hover { background: rgb(0,88,85); }

    /* Sidebar summary */
    .sidebar-card {
        border: 1px solid #0d1614;
        background: #fff;
        overflow: hidden;
    }
    .sidebar-card-h {
        /*background: #0d1614; color: #fff;*/
        padding: 14px 18px;
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; font-weight: 600;
    }
    .sidebar-body { padding: 18px; }
    .sidebar-pos {
        font-family: "IBM Plex Serif", serif;
        font-size: 17px; font-weight: 500; color: #0d1614;
        margin-bottom: 4px;
    }
    .sidebar-unit { font-size: 13px; color: #5a6864; margin-bottom: 16px; }
    .sidebar-meta-row {
        display: flex; justify-content: space-between;
        font-size: 12px; color: #5a6864;
        border-top: 1px solid #efede5; padding: 8px 0;
    }
    .sidebar-meta-row strong { color: #0d1614; }
    .step-list { padding: 18px; }
    .step-list-item {
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; color: #8a948f;
        padding: 6px 0; border-bottom: 1px solid #efede5;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
    }
    .step-list-item.active { color: #0d1614; font-weight: 600; }
    .step-list-item.done { color: var(--color-secondary); }
    .step-list-num {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; width: 20px; text-align: center;
        flex-shrink: 0;
    }

    @media (max-width: 960px) {
        .apply-wrap { padding: 32px 16px 60px; }
        .apply-body { grid-template-columns: 1fr; }
        .apply-sidebar { position: static; }
        .form-row.cols-3 { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .form-row.cols-2, .form-row.cols-3 { grid-template-columns: 1fr; }
        .step-progress { gap: 0; }
        .step-circle { width: 26px; height: 26px; font-size: 10px; }
        .step-counter { display: none; }
    }
</style>

<div class="apply-wrap" x-data="applyWizard()" x-init="init()">

    <a href="{{ route('karier.show', $vacancy) }}" class="apply-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
        Kembali ke Detail Lowongan
    </a>

    {{-- Header --}}
    <div class="apply-header">
        <div class="apply-eyebrow">{{ $vacancy->unit->nama }}</div>
        <h1 class="apply-title">Formulir Lamaran</h1>
        <p class="apply-subtitle">{{ $vacancy->judul_posisi }}</p>

        {{-- Step progress bar --}}
        <div class="step-progress">
            @foreach (['Identitas', 'Keluarga', 'Pendidikan', 'Organisasi', 'Kerja', 'Minat', 'Referensi', 'Lain-Lain'] as $i => $label)
                <div class="step-dot"
                     :class="{ active: step === {{ $i + 1 }}, done: step > {{ $i + 1 }} }">
                    <div class="step-circle">
                        <span x-show="step <= {{ $i + 1 }}">{{ $i + 1 }}</span>
                        <span x-show="step > {{ $i + 1 }}">✓</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('karier.lamar.store', $vacancy) }}" enctype="multipart/form-data" id="apply-form">
        @csrf

        <div class="apply-body">
            <div class="apply-form-main">

                @if ($errors->any())
                    <div style="background:#fdf4f2;border:1px solid #f5c6bc;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#7a1f0f;">
                        Mohon periksa kembali data yang diisi.
                    </div>
                @endif

                {{-- Restore banner --}}
                <div id="ats-restore-banner" x-show="hasSavedData" x-cloak style="background:#f0f9f6;border:1px solid #a3d4cc;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#1a4a46;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                    <div>
                        <strong>Terdapat data yang tersimpan.</strong>
                        Lanjutkan dari sesi sebelumnya? <em>File CV dan STR/SIP perlu diunggah ulang.</em>
                    </div>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <button type="button" @click="restoreProgress()" style="background:#007774;color:#fff;border:none;padding:6px 14px;font-size:12px;cursor:pointer;border-radius:3px;">Lanjutkan</button>
                        <button type="button" @click="discardProgress()" style="background:transparent;color:#1a4a46;border:1px solid #a3d4cc;padding:6px 14px;font-size:12px;cursor:pointer;border-radius:3px;">Hapus</button>
                    </div>
                </div>

                {{-- ═══ STEP 1: Identitas Diri ═══ --}}
                <div class="step-panel" :class="{ active: step === 1 }">
                    <h2 class="form-section-h">I. Identitas Diri</h2>

                    <div class="form-row cols-2">
                        <div class="field">
                            <label for="nama_lengkap">Nama Lengkap <span class="req">*</span></label>
                            <input id="nama_lengkap" name="nama_lengkap" type="text" value="{{ old('nama_lengkap') }}"
                                   placeholder="Sesuai KTP" class="{{ $errors->has('nama_lengkap') ? 'error' : '' }}">
                            @error('nama_lengkap')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="no_ktp">Nomor KTP <span class="req">*</span></label>
                            <input id="no_ktp" name="no_ktp" type="text" value="{{ old('no_ktp') }}"
                                   placeholder="16 digit" maxlength="16" class="{{ $errors->has('no_ktp') ? 'error' : '' }}">
                            @error('no_ktp')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-3">
                        <div class="field">
                            <label for="tempat_lahir">Tempat Lahir <span class="req">*</span></label>
                            <input id="tempat_lahir" name="tempat_lahir" type="text" value="{{ old('tempat_lahir') }}"
                                   class="{{ $errors->has('tempat_lahir') ? 'error' : '' }}">
                            @error('tempat_lahir')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="tanggal_lahir">Tanggal Lahir <span class="req">*</span></label>
                            <input id="tanggal_lahir" name="tanggal_lahir" type="date" value="{{ old('tanggal_lahir') }}"
                                   class="{{ $errors->has('tanggal_lahir') ? 'error' : '' }}">
                            @error('tanggal_lahir')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="jenis_kelamin">Jenis Kelamin <span class="req">*</span></label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="{{ $errors->has('jenis_kelamin') ? 'error' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach ($jenisKelaminOptions as $opt)
                                    <option value="{{ $opt->value }}" {{ old('jenis_kelamin') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                                @endforeach
                            </select>
                            @error('jenis_kelamin')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-3">
                        <div class="field">
                            <label for="agama">Agama <span class="req">*</span></label>
                            <select id="agama" name="agama" class="{{ $errors->has('agama') ? 'error' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya'] as $ag)
                                    <option value="{{ $ag }}" {{ old('agama') === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                                @endforeach
                            </select>
                            @error('agama')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="status_perkawinan">Status Perkawinan <span class="req">*</span></label>
                            <select id="status_perkawinan" name="status_perkawinan" class="{{ $errors->has('status_perkawinan') ? 'error' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach ($statusPerkawinanOptions as $opt)
                                    <option value="{{ $opt->value }}" {{ old('status_perkawinan') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                                @endforeach
                            </select>
                            @error('status_perkawinan')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="golongan_darah">Golongan Darah</label>
                            <select id="golongan_darah" name="golongan_darah">
                                <option value="">-- Pilih --</option>
                                @foreach ($golonganDarahOptions as $opt)
                                    <option value="{{ $opt->value }}" {{ old('golongan_darah') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label for="alamat_ktp">Alamat KTP <span class="req">*</span></label>
                            <textarea id="alamat_ktp" name="alamat_ktp" class="{{ $errors->has('alamat_ktp') ? 'error' : '' }}">{{ old('alamat_ktp') }}</textarea>
                            @error('alamat_ktp')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label for="alamat_domisili">Alamat Domisili <span class="req">*</span></label>
                            <textarea id="alamat_domisili" name="alamat_domisili" class="{{ $errors->has('alamat_domisili') ? 'error' : '' }}">{{ old('alamat_domisili') }}</textarea>
                            @error('alamat_domisili')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="field">
                            <label for="no_telepon">Telepon/HP <span class="req">*</span></label>
                            <input id="no_telepon" name="no_telepon" type="text" value="{{ old('no_telepon') }}"
                                   placeholder="08xxxxxxxxxx" class="{{ $errors->has('no_telepon') ? 'error' : '' }}">
                            @error('no_telepon')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="email">Email <span class="req">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                   placeholder="contoh@email.com" class="{{ $errors->has('email') ? 'error' : '' }}">
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="field">
                            <label for="npwp">NPWP</label>
                            <input id="npwp" name="npwp" type="text" value="{{ old('npwp') }}">
                        </div>
                        <div class="field">
                            <label for="nama_ibu_kandung">Nama Ibu Kandung</label>
                            <input id="nama_ibu_kandung" name="nama_ibu_kandung" type="text" value="{{ old('nama_ibu_kandung') }}">
                        </div>
                    </div>
                    <h3 class="form-section-h" style="margin-top:32px;">Kontak Darurat</h3>
                    <div class="form-row cols-3">
                        <div class="field">
                            <label>Nama</label>
                            <input name="kontak_darurat_nama" type="text" value="{{ old('kontak_darurat_nama') }}">
                        </div>
                        <div class="field">
                            <label>No. Telp</label>
                            <input name="kontak_darurat_no_telp" type="text" value="{{ old('kontak_darurat_no_telp') }}">
                        </div>
                        <div class="field">
                            <label>Hubungan</label>
                            <input name="kontak_darurat_hubungan" type="text" value="{{ old('kontak_darurat_hubungan') }}" placeholder="mis. Ibu, Suami">
                        </div>
                    </div>
                </div>

                {{-- ═══ STEP 2: Latar Belakang Keluarga ═══ --}}
                <div class="step-panel" :class="{ active: step === 2 }">
                    <h2 class="form-section-h">II. Latar Belakang Keluarga</h2>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:0 0 14px;">A. Data Orang Tua</h3>
                    <div class="flex flex-col gap-6">
                        <div>
                            <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#5a6864;margin-bottom:10px;">Data Ayah</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-[16px] md:gap-[16px]">
                                <div class="field"><label>Nama</label><input name="ayah_nama" type="text" value="{{ old('ayah_nama') }}"></div>
                                <div class="field"><label>Usia</label><input name="ayah_usia" type="number" min="1" max="150" value="{{ old('ayah_usia') }}"></div>
                                <div class="field">
                                    <label>Pendidikan Terakhir</label>
                                    <select name="ayah_pendidikan_terakhir">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($jenisPendidikanOptions as $opt)
                                            <option value="{{ $opt->value }}" {{ old('ayah_pendidikan_terakhir') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Pekerjaan / Jabatan</label><input name="ayah_pekerjaan" type="text" value="{{ old('ayah_pekerjaan') }}"></div>
                            </div>
                        </div>
                        <div>
                            <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#5a6864;margin-bottom:10px;">Data Ibu</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-[16px] md:gap-[16px]">
                                <div class="field"><label>Nama</label><input name="ibu_nama" type="text" value="{{ old('ibu_nama') }}"></div>
                                <div class="field"><label>Usia</label><input name="ibu_usia" type="number" min="1" max="150" value="{{ old('ibu_usia') }}"></div>
                                <div class="field">
                                    <label>Pendidikan Terakhir</label>
                                    <select name="ibu_pendidikan_terakhir">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($jenisPendidikanOptions as $opt)
                                            <option value="{{ $opt->value }}" {{ old('ibu_pendidikan_terakhir') === $opt->value ? 'selected' : '' }}>{{ $opt->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Pekerjaan / Jabatan</label><input name="ibu_pekerjaan" type="text" value="{{ old('ibu_pekerjaan') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:24px;">
                        <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#5a6864;margin-bottom:10px;">Data Anda</p>
                        <div class="form-row cols-2">
                        <div class="field">
                            <label>Anda anak ke</label>
                            <input name="saudara_anak_ke" type="number" min="1" value="{{ old('saudara_anak_ke') }}">
                        </div>
                        <div class="field">
                            <label>Dari ... bersaudara</label>
                            <input name="saudara_dari_bersaudara" type="number" min="1" value="{{ old('saudara_dari_bersaudara') }}">
                        </div>
                        </div>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:24px 0 6px;">Saudara/i Kandung</h3>
                    <p class="form-section-sub">Termasuk Anda sendiri. Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                    <div class="adj-section" x-data="adjSection('siblings', @js(old('siblings', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Saudara/i <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)" title="Hapus">&times;</button>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Nama</label>
                                        <input :name="`siblings[${idx}][nama]`" type="text" x-model="item.nama" :class="{ error: $root.hasFieldError('siblings', idx, 'nama') }">
                                        <p class="field-error" x-show="$root.hasFieldError('siblings', idx, 'nama')" x-text="$root.fieldError('siblings', idx, 'nama')"></p>
                                    </div>
                                    <div class="field"><label>Usia</label>
                                        <input :name="`siblings[${idx}][usia]`" type="number" min="0" x-model="item.usia" :class="{ error: $root.hasFieldError('siblings', idx, 'usia') }">
                                        <p class="field-error" x-show="$root.hasFieldError('siblings', idx, 'usia')" x-text="$root.fieldError('siblings', idx, 'usia')"></p>
                                    </div>
                                    <div class="field"><label>Jenis Kelamin</label>
                                        <select :name="`siblings[${idx}][jenis_kelamin]`" x-model="item.jenis_kelamin" :class="{ error: $root.hasFieldError('siblings', idx, 'jenis_kelamin') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisKelaminOptions as $opt)
                                                <option value="{{ $opt->value }}">{{ $opt->label() }}</option>
                                            @endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('siblings', idx, 'jenis_kelamin')" x-text="$root.fieldError('siblings', idx, 'jenis_kelamin')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Pendidikan Terakhir</label>
                                        <select :name="`siblings[${idx}][pendidikan_terakhir]`" x-model="item.pendidikan_terakhir" :class="{ error: $root.hasFieldError('siblings', idx, 'pendidikan_terakhir') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisPendidikanOptions as $opt)
                                                <option value="{{ $opt->value }}">{{ $opt->label() }}</option>
                                            @endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('siblings', idx, 'pendidikan_terakhir')" x-text="$root.fieldError('siblings', idx, 'pendidikan_terakhir')"></p>
                                    </div>
                                    <div class="field"><label>Pekerjaan / Jabatan</label>
                                        <input :name="`siblings[${idx}][pekerjaan_jabatan]`" type="text" x-model="item.pekerjaan_jabatan" :class="{ error: $root.hasFieldError('siblings', idx, 'pekerjaan_jabatan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('siblings', idx, 'pekerjaan_jabatan')" x-text="$root.fieldError('siblings', idx, 'pekerjaan_jabatan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Saudara/i
                        </button>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:24px 0 6px;">B. Data Suami/Istri</h3>
                    <p class="form-section-sub">Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                    <div class="adj-section" x-data="adjSection('spouses', @js(old('spouses', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Suami/Istri <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Nama</label>
                                        <input :name="`spouses[${idx}][nama]`" type="text" x-model="item.nama" :class="{ error: $root.hasFieldError('spouses', idx, 'nama') }">
                                        <p class="field-error" x-show="$root.hasFieldError('spouses', idx, 'nama')" x-text="$root.fieldError('spouses', idx, 'nama')"></p>
                                    </div>
                                    <div class="field"><label>Usia</label>
                                        <input :name="`spouses[${idx}][usia]`" type="number" min="0" x-model="item.usia" :class="{ error: $root.hasFieldError('spouses', idx, 'usia') }">
                                        <p class="field-error" x-show="$root.hasFieldError('spouses', idx, 'usia')" x-text="$root.fieldError('spouses', idx, 'usia')"></p>
                                    </div>
                                    <div class="field"><label>Jenis Kelamin</label>
                                        <select :name="`spouses[${idx}][jenis_kelamin]`" x-model="item.jenis_kelamin" :class="{ error: $root.hasFieldError('spouses', idx, 'jenis_kelamin') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisKelaminOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('spouses', idx, 'jenis_kelamin')" x-text="$root.fieldError('spouses', idx, 'jenis_kelamin')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Pendidikan Terakhir</label>
                                        <select :name="`spouses[${idx}][pendidikan_terakhir]`" x-model="item.pendidikan_terakhir" :class="{ error: $root.hasFieldError('spouses', idx, 'pendidikan_terakhir') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisPendidikanOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('spouses', idx, 'pendidikan_terakhir')" x-text="$root.fieldError('spouses', idx, 'pendidikan_terakhir')"></p>
                                    </div>
                                    <div class="field"><label>Pekerjaan / Jabatan</label>
                                        <input :name="`spouses[${idx}][pekerjaan_jabatan]`" type="text" x-model="item.pekerjaan_jabatan" :class="{ error: $root.hasFieldError('spouses', idx, 'pekerjaan_jabatan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('spouses', idx, 'pekerjaan_jabatan')" x-text="$root.fieldError('spouses', idx, 'pekerjaan_jabatan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Suami/Istri
                        </button>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:24px 0 6px;">C. Data Anak</h3>
                    <p class="form-section-sub">Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                    <div class="adj-section" x-data="adjSection('children', @js(old('children', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Anak <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Nama</label>
                                        <input :name="`children[${idx}][nama]`" type="text" x-model="item.nama" :class="{ error: $root.hasFieldError('children', idx, 'nama') }">
                                        <p class="field-error" x-show="$root.hasFieldError('children', idx, 'nama')" x-text="$root.fieldError('children', idx, 'nama')"></p>
                                    </div>
                                    <div class="field"><label>Usia</label>
                                        <input :name="`children[${idx}][usia]`" type="number" min="0" x-model="item.usia" :class="{ error: $root.hasFieldError('children', idx, 'usia') }">
                                        <p class="field-error" x-show="$root.hasFieldError('children', idx, 'usia')" x-text="$root.fieldError('children', idx, 'usia')"></p>
                                    </div>
                                    <div class="field"><label>Jenis Kelamin</label>
                                        <select :name="`children[${idx}][jenis_kelamin]`" x-model="item.jenis_kelamin" :class="{ error: $root.hasFieldError('children', idx, 'jenis_kelamin') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisKelaminOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('children', idx, 'jenis_kelamin')" x-text="$root.fieldError('children', idx, 'jenis_kelamin')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Pendidikan Terakhir</label>
                                        <select :name="`children[${idx}][pendidikan_terakhir]`" x-model="item.pendidikan_terakhir" :class="{ error: $root.hasFieldError('children', idx, 'pendidikan_terakhir') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisPendidikanOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('children', idx, 'pendidikan_terakhir')" x-text="$root.fieldError('children', idx, 'pendidikan_terakhir')"></p>
                                    </div>
                                    <div class="field"><label>Pekerjaan / Jabatan</label>
                                        <input :name="`children[${idx}][pekerjaan_jabatan]`" type="text" x-model="item.pekerjaan_jabatan" :class="{ error: $root.hasFieldError('children', idx, 'pekerjaan_jabatan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('children', idx, 'pekerjaan_jabatan')" x-text="$root.fieldError('children', idx, 'pekerjaan_jabatan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Anak
                        </button>
                    </div>
                </div>

                {{-- ═══ STEP 3: Pendidikan ═══ --}}
                <div class="step-panel" :class="{ active: step === 3 }">
                    <h2 class="form-section-h">III. Pendidikan</h2>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:0 0 6px;">Pendidikan Formal <span style="color:#b54327;">*</span></h3>
                    <p class="form-section-sub">Dimulai dari pendidikan terakhir. Wajib diisi minimal 1.</p>
                    @error('formal_educations')<p class="field-error" style="margin-bottom:12px;">{{ $message }}</p>@enderror
                    <div class="adj-section" x-data="adjSection('formal_educations', @js(old('formal_educations', [[]])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Pendidikan <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" x-show="items.length > 1" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Jenis Pendidikan <span class="req">*</span></label>
                                        <select :name="`formal_educations[${idx}][jenis_pendidikan]`" x-model="item.jenis_pendidikan" :class="{ error: $root.hasFieldError('formal_educations', idx, 'jenis_pendidikan') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($jenisPendidikanOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'jenis_pendidikan')" x-text="$root.fieldError('formal_educations', idx, 'jenis_pendidikan')"></p>
                                    </div>
                                    <div class="field"><label>Nama Sekolah/Institusi <span class="req">*</span></label>
                                        <input :name="`formal_educations[${idx}][nama_sekolah]`" type="text" x-model="item.nama_sekolah" :class="{ error: $root.hasFieldError('formal_educations', idx, 'nama_sekolah') }">
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'nama_sekolah')" x-text="$root.fieldError('formal_educations', idx, 'nama_sekolah')"></p>
                                    </div>
                                    <div class="field"><label>Kota <span class="req">*</span></label>
                                        <input :name="`formal_educations[${idx}][kota]`" type="text" x-model="item.kota" :class="{ error: $root.hasFieldError('formal_educations', idx, 'kota') }">
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'kota')" x-text="$root.fieldError('formal_educations', idx, 'kota')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Tahun Lulus <span class="req">*</span></label>
                                        <input :name="`formal_educations[${idx}][tahun_lulus]`" type="number" min="1900" max="2100" x-model="item.tahun_lulus" :class="{ error: $root.hasFieldError('formal_educations', idx, 'tahun_lulus') }">
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'tahun_lulus')" x-text="$root.fieldError('formal_educations', idx, 'tahun_lulus')"></p>
                                    </div>
                                    <div class="field"><label>IP/Nilai</label>
                                        <input :name="`formal_educations[${idx}][ip_nilai]`" type="text" x-model="item.ip_nilai" placeholder="mis. 3.75" :class="{ error: $root.hasFieldError('formal_educations', idx, 'ip_nilai') }">
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'ip_nilai')" x-text="$root.fieldError('formal_educations', idx, 'ip_nilai')"></p>
                                    </div>
                                    <div class="field"><label>Jurusan</label>
                                        <input :name="`formal_educations[${idx}][jurusan]`" type="text" x-model="item.jurusan" :class="{ error: $root.hasFieldError('formal_educations', idx, 'jurusan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('formal_educations', idx, 'jurusan')" x-text="$root.fieldError('formal_educations', idx, 'jurusan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Pendidikan Formal
                        </button>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 6px;">Prestasi / Karya Luar Biasa</h3>
                    <p class="form-section-sub">Prestasi selama pendidikan. Opsional, tapi jika diisi sebagian wajib lengkap.</p>
                    <div class="adj-section" x-data="adjSection('achievements', @js(old('achievements', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Prestasi <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Nama Prestasi</label>
                                        <input :name="`achievements[${idx}][nama_prestasi]`" type="text" x-model="item.nama_prestasi" :class="{ error: $root.hasFieldError('achievements', idx, 'nama_prestasi') }">
                                        <p class="field-error" x-show="$root.hasFieldError('achievements', idx, 'nama_prestasi')" x-text="$root.fieldError('achievements', idx, 'nama_prestasi')"></p>
                                    </div>
                                    <div class="field"><label>Tahun</label>
                                        <input :name="`achievements[${idx}][tahun]`" type="number" min="1900" max="2100" x-model="item.tahun" :class="{ error: $root.hasFieldError('achievements', idx, 'tahun') }">
                                        <p class="field-error" x-show="$root.hasFieldError('achievements', idx, 'tahun')" x-text="$root.fieldError('achievements', idx, 'tahun')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Prestasi
                        </button>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 6px;">Pendidikan Informal <span style="color:#b54327;">*</span></h3>
                    <p class="form-section-sub">Pelatihan, kursus, seminar. Wajib minimal 1.</p>
                    @error('informal_educations')<p class="field-error" style="margin-bottom:12px;">{{ $message }}</p>@enderror
                    <div class="adj-section" x-data="adjSection('informal_educations', @js(old('informal_educations', [[]])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Pendidikan Informal <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" x-show="items.length > 1" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Nama Pendidikan <span class="req">*</span></label>
                                        <input :name="`informal_educations[${idx}][nama]`" type="text" x-model="item.nama" :class="{ error: $root.hasFieldError('informal_educations', idx, 'nama') }">
                                        <p class="field-error" x-show="$root.hasFieldError('informal_educations', idx, 'nama')" x-text="$root.fieldError('informal_educations', idx, 'nama')"></p>
                                    </div>
                                    <div class="field"><label>Topik <span class="req">*</span></label>
                                        <input :name="`informal_educations[${idx}][topik]`" type="text" x-model="item.topik" :class="{ error: $root.hasFieldError('informal_educations', idx, 'topik') }">
                                        <p class="field-error" x-show="$root.hasFieldError('informal_educations', idx, 'topik')" x-text="$root.fieldError('informal_educations', idx, 'topik')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Periode Mulai <span class="req">*</span></label>
                                        <input :name="`informal_educations[${idx}][periode_mulai]`" type="date" x-model="item.periode_mulai" :class="{ error: $root.hasFieldError('informal_educations', idx, 'periode_mulai') }">
                                        <p class="field-error" x-show="$root.hasFieldError('informal_educations', idx, 'periode_mulai')" x-text="$root.fieldError('informal_educations', idx, 'periode_mulai')"></p>
                                    </div>
                                    <div class="field"><label>Periode Selesai <span class="req">*</span></label>
                                        <input :name="`informal_educations[${idx}][periode_selesai]`" type="date" x-model="item.periode_selesai" :class="{ error: $root.hasFieldError('informal_educations', idx, 'periode_selesai') }">
                                        <p class="field-error" x-show="$root.hasFieldError('informal_educations', idx, 'periode_selesai')" x-text="$root.fieldError('informal_educations', idx, 'periode_selesai')"></p>
                                    </div>
                                    <div class="field"><label>Penyelenggara <span class="req">*</span></label>
                                        <input :name="`informal_educations[${idx}][penyelenggara]`" type="text" x-model="item.penyelenggara" :class="{ error: $root.hasFieldError('informal_educations', idx, 'penyelenggara') }">
                                        <p class="field-error" x-show="$root.hasFieldError('informal_educations', idx, 'penyelenggara')" x-text="$root.fieldError('informal_educations', idx, 'penyelenggara')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Pendidikan Informal
                        </button>
                    </div>

                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 6px;">Kemampuan Bahasa Asing / Daerah</h3>
                    <p class="form-section-sub">Opsional, tapi jika diisi sebagian wajib lengkap.</p>
                    <div class="adj-section" x-data="adjSection('language_skills', @js(old('language_skills', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Bahasa <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr;">
                                    <div class="field"><label>Nama Bahasa</label>
                                        <input :name="`language_skills[${idx}][nama_bahasa]`" type="text" x-model="item.nama_bahasa" :class="{ error: $root.hasFieldError('language_skills', idx, 'nama_bahasa') }">
                                        <p class="field-error" x-show="$root.hasFieldError('language_skills', idx, 'nama_bahasa')" x-text="$root.fieldError('language_skills', idx, 'nama_bahasa')"></p>
                                    </div>
                                    @foreach (['berbicara' => 'Berbicara', 'menulis' => 'Menulis', 'membaca' => 'Membaca'] as $field => $lbl)
                                    <div class="field"><label>{{ $lbl }}</label>
                                        <select :name="`language_skills[${idx}][{{ $field }}]`" x-model="item.{{ $field }}" :class="{ error: $root.hasFieldError('language_skills', idx, '{{ $field }}') }">
                                            <option value="">--</option>
                                            @foreach ($tingkatBahasaOptions as $opt)<option value="{{ $opt->value }}">{{ $opt->label() }}</option>@endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('language_skills', idx, '{{ $field }}')" x-text="$root.fieldError('language_skills', idx, '{{ $field }}')"></p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Bahasa
                        </button>
                    </div>
                </div>

                {{-- ═══ STEP 4: Organisasi ═══ --}}
                <div class="step-panel" :class="{ active: step === 4 }">
                    <h2 class="form-section-h">IV. Pengalaman Organisasi</h2>
                    <p class="form-section-sub">Opsional. Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                    <div class="adj-section" x-data="adjSection('organization_experiences', @js(old('organization_experiences', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Organisasi <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Nama Organisasi</label>
                                        <input :name="`organization_experiences[${idx}][nama_organisasi]`" type="text" x-model="item.nama_organisasi" :class="{ error: $root.hasFieldError('organization_experiences', idx, 'nama_organisasi') }">
                                        <p class="field-error" x-show="$root.hasFieldError('organization_experiences', idx, 'nama_organisasi')" x-text="$root.fieldError('organization_experiences', idx, 'nama_organisasi')"></p>
                                    </div>
                                    <div class="field"><label>Jabatan</label>
                                        <input :name="`organization_experiences[${idx}][jabatan]`" type="text" x-model="item.jabatan" :class="{ error: $root.hasFieldError('organization_experiences', idx, 'jabatan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('organization_experiences', idx, 'jabatan')" x-text="$root.fieldError('organization_experiences', idx, 'jabatan')"></p>
                                    </div>
                                </div>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Periode Mulai</label>
                                        <input :name="`organization_experiences[${idx}][periode_mulai]`" type="date" x-model="item.periode_mulai" :class="{ error: $root.hasFieldError('organization_experiences', idx, 'periode_mulai') }">
                                        <p class="field-error" x-show="$root.hasFieldError('organization_experiences', idx, 'periode_mulai')" x-text="$root.fieldError('organization_experiences', idx, 'periode_mulai')"></p>
                                    </div>
                                    <div class="field"><label>Periode Selesai</label>
                                        <input :name="`organization_experiences[${idx}][periode_selesai]`" type="date" x-model="item.periode_selesai" :class="{ error: $root.hasFieldError('organization_experiences', idx, 'periode_selesai') }">
                                        <p class="field-error" x-show="$root.hasFieldError('organization_experiences', idx, 'periode_selesai')" x-text="$root.fieldError('organization_experiences', idx, 'periode_selesai')"></p>
                                    </div>
                                    <div class="field"><label>Keterangan</label>
                                        <input :name="`organization_experiences[${idx}][keterangan]`" type="text" x-model="item.keterangan" :class="{ error: $root.hasFieldError('organization_experiences', idx, 'keterangan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('organization_experiences', idx, 'keterangan')" x-text="$root.fieldError('organization_experiences', idx, 'keterangan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Organisasi
                        </button>
                    </div>
                </div>

                {{-- ═══ STEP 5: Pengalaman Kerja ═══ --}}
                <div class="step-panel" :class="{ active: step === 5 }">
                    <h2 class="form-section-h">V. Pengalaman Kerja</h2>
                    <div class="field" style="margin-bottom:20px;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;text-transform:none;letter-spacing:0;color:#0d1614;font-family:'IBM Plex Sans',system-ui,sans-serif;">
                            <input type="checkbox" name="is_fresh_graduate" value="1" x-model="isFreshGraduate" style="width:16px;height:16px;accent-color:rgb(0,119,116);">
                            Saya adalah Fresh Graduate (belum pernah bekerja)
                        </label>
                        <input type="hidden" name="is_fresh_graduate" :value="isFreshGraduate ? '1' : '0'">
                    </div>

                    <div x-show="!isFreshGraduate">
                        <p class="form-section-sub">Dimulai dari pengalaman kerja terakhir. Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                        <div class="adj-section" x-data="adjSection('work_experiences', @js(old('work_experiences', [])))">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div class="adj-item">
                                    <div class="adj-item-num">Pengalaman Kerja <span x-text="idx+1"></span></div>
                                    <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                    <div class="form-row cols-2">
                                        <div class="field"><label>Nama Perusahaan</label>
                                            <input :name="`work_experiences[${idx}][nama_perusahaan]`" type="text" x-model="item.nama_perusahaan" :class="{ error: $root.hasFieldError('work_experiences', idx, 'nama_perusahaan') }">
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'nama_perusahaan')" x-text="$root.fieldError('work_experiences', idx, 'nama_perusahaan')"></p>
                                        </div>
                                        <div class="field"><label>Jabatan</label>
                                            <input :name="`work_experiences[${idx}][jabatan]`" type="text" x-model="item.jabatan" :class="{ error: $root.hasFieldError('work_experiences', idx, 'jabatan') }">
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'jabatan')" x-text="$root.fieldError('work_experiences', idx, 'jabatan')"></p>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="field"><label>Alamat Perusahaan</label>
                                            <textarea :name="`work_experiences[${idx}][alamat_perusahaan]`" x-model="item.alamat_perusahaan" style="min-height:60px;" :class="{ error: $root.hasFieldError('work_experiences', idx, 'alamat_perusahaan') }"></textarea>
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'alamat_perusahaan')" x-text="$root.fieldError('work_experiences', idx, 'alamat_perusahaan')"></p>
                                        </div>
                                    </div>
                                    <div class="form-row cols-3">
                                        <div class="field"><label>Periode Mulai</label>
                                            <input :name="`work_experiences[${idx}][periode_mulai]`" type="date" x-model="item.periode_mulai" :class="{ error: $root.hasFieldError('work_experiences', idx, 'periode_mulai') }">
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'periode_mulai')" x-text="$root.fieldError('work_experiences', idx, 'periode_mulai')"></p>
                                        </div>
                                        <div class="field"><label>Periode Selesai</label>
                                            <input :name="`work_experiences[${idx}][periode_selesai]`" type="date" x-model="item.periode_selesai" :class="{ error: $root.hasFieldError('work_experiences', idx, 'periode_selesai') }">
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'periode_selesai')" x-text="$root.fieldError('work_experiences', idx, 'periode_selesai')"></p>
                                        </div>
                                        <div class="field"><label>Gaji Terakhir</label>
                                            <input :name="`work_experiences[${idx}][gaji_terakhir]`" type="text" x-model="item.gaji_terakhir" placeholder="mis. 5.000.000" :class="{ error: $root.hasFieldError('work_experiences', idx, 'gaji_terakhir') }">
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'gaji_terakhir')" x-text="$root.fieldError('work_experiences', idx, 'gaji_terakhir')"></p>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="field"><label>Rincian Tugas</label>
                                            <textarea :name="`work_experiences[${idx}][rincian_tugas]`" x-model="item.rincian_tugas" :class="{ error: $root.hasFieldError('work_experiences', idx, 'rincian_tugas') }"></textarea>
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'rincian_tugas')" x-text="$root.fieldError('work_experiences', idx, 'rincian_tugas')"></p>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="field"><label>Alasan Meninggalkan</label>
                                            <textarea :name="`work_experiences[${idx}][alasan_meninggalkan]`" x-model="item.alasan_meninggalkan" style="min-height:60px;" :class="{ error: $root.hasFieldError('work_experiences', idx, 'alasan_meninggalkan') }"></textarea>
                                            <p class="field-error" x-show="$root.hasFieldError('work_experiences', idx, 'alasan_meninggalkan')" x-text="$root.fieldError('work_experiences', idx, 'alasan_meninggalkan')"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" class="adj-add" @click="add()">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                Tambah Pengalaman Kerja
                            </button>
                        </div>
                    </div>
                    <div x-show="isFreshGraduate" style="padding:24px;background:#e5f1f0;font-size:13px;color:#005f5c;border:1px solid #b8dbd9;">
                        Anda memilih sebagai fresh graduate. Bagian pengalaman kerja dikosongkan.
                    </div>
                </div>

                {{-- ═══ STEP 6: Minat ═══ --}}
                <div class="step-panel" :class="{ active: step === 6 }">
                    <h2 class="form-section-h">VI. Minat</h2>
                    <div class="form-row">
                        <div class="field">
                            <label for="alasan_melamar">Alasan / Tujuan melamar di perusahaan ini <span class="req">*</span></label>
                            <textarea id="alasan_melamar" name="alasan_melamar" style="min-height:120px;"
                                      class="{{ $errors->has('alasan_melamar') ? 'error' : '' }}">{{ old('alasan_melamar') }}</textarea>
                            @error('alasan_melamar')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="field">
                            <label for="gaji_diharapkan">Gaji yang Diharapkan (Nett) <span class="req">*</span></label>
                            <input id="gaji_diharapkan" name="gaji_diharapkan" type="number" min="0"
                                   value="{{ old('gaji_diharapkan') }}" placeholder="mis. 6000000"
                                   class="{{ $errors->has('gaji_diharapkan') ? 'error' : '' }}">
                            @error('gaji_diharapkan')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="fasilitas_diharapkan">Fasilitas yang Diharapkan</label>
                            <input id="fasilitas_diharapkan" name="fasilitas_diharapkan" type="text"
                                   value="{{ old('fasilitas_diharapkan') }}" placeholder="mis. BPJS, transport, makan">
                        </div>
                    </div>
                </div>

                {{-- ═══ STEP 7: Referensi ═══ --}}
                <div class="step-panel" :class="{ active: step === 7 }">
                    <h2 class="form-section-h">VII. Referensi / Rekomendasi</h2>
                    <p class="form-section-sub">Tuliskan karyawan/ti yang Anda kenal di RS Azra dan jelaskan hubungan Anda.</p>
                    <div class="adj-section" x-data="adjSection('references', @js(old('references', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Referensi <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-3">
                                    <div class="field"><label>Nama Karyawan</label>
                                        <input :name="`references[${idx}][nama_karyawan]`" type="text" x-model="item.nama_karyawan" :class="{ error: $root.hasFieldError('references', idx, 'nama_karyawan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('references', idx, 'nama_karyawan')" x-text="$root.fieldError('references', idx, 'nama_karyawan')"></p>
                                    </div>
                                    <div class="field"><label>Hubungan</label>
                                        <input :name="`references[${idx}][hubungan]`" type="text" x-model="item.hubungan" placeholder="mis. Teman, Saudara" :class="{ error: $root.hasFieldError('references', idx, 'hubungan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('references', idx, 'hubungan')" x-text="$root.fieldError('references', idx, 'hubungan')"></p>
                                    </div>
                                    <div class="field"><label>Keterangan</label>
                                        <input :name="`references[${idx}][keterangan]`" type="text" x-model="item.keterangan" :class="{ error: $root.hasFieldError('references', idx, 'keterangan') }">
                                        <p class="field-error" x-show="$root.hasFieldError('references', idx, 'keterangan')" x-text="$root.fieldError('references', idx, 'keterangan')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Referensi
                        </button>
                    </div>
                </div>

                {{-- ═══ STEP 8: Lain-Lain ═══ --}}
                <div class="step-panel" :class="{ active: step === 8 }">
                    <h2 class="form-section-h">VIII. Lain-Lain</h2>

                    {{-- A. Riwayat Penyakit --}}
                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:0 0 14px;">A. Riwayat Penyakit</h3>
                    <div x-data="{ pernahSakit: @js(old('pernah_sakit_serius', '')) }">
                        <div class="field">
                            <label>Pernah menderita sakit/kecelakaan serius? <span class="req">*</span></label>
                            @error('pernah_sakit_serius')<p class="field-error">{{ $message }}</p>@enderror
                            <div style="display:flex;gap:20px;margin-top:6px;">
                                <label style="display:flex;align-items:center;gap:8px;font-family:'IBM Plex Sans',system-ui,sans-serif;font-size:14px;text-transform:none;letter-spacing:0;color:#0d1614;cursor:pointer;">
                                    <input type="radio" name="pernah_sakit_serius" value="ya" x-model="pernahSakit" style="accent-color:rgb(0,119,116);"> Ya
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;font-family:'IBM Plex Sans',system-ui,sans-serif;font-size:14px;text-transform:none;letter-spacing:0;color:#0d1614;cursor:pointer;">
                                    <input type="radio" name="pernah_sakit_serius" value="tidak" x-model="pernahSakit" style="accent-color:rgb(0,119,116);"> Tidak
                                </label>
                            </div>
                        </div>
                        <div class="field" x-show="pernahSakit === 'ya'" style="margin-top:16px;">
                            <label>Diagnosis dan kejadian</label>
                            <textarea name="diagnosis_sakit" class="{{ $errors->has('diagnosis_sakit') ? 'error' : '' }}">{{ old('diagnosis_sakit') }}</textarea>
                            @error('diagnosis_sakit')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- B. Dokumen dan Kesiapan Kerja --}}
                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 14px;">B. Dokumen dan Kesiapan Kerja</h3>
                    <div class="form-row">
                        <div class="field">
                            <label for="kesiapan_kerja">Kapan siap bekerja? Sertakan alasan <span class="req">*</span></label>
                            <textarea id="kesiapan_kerja" name="kesiapan_kerja" style="min-height:100px;"
                                      class="{{ $errors->has('kesiapan_kerja') ? 'error' : '' }}">{{ old('kesiapan_kerja') }}</textarea>
                            @error('kesiapan_kerja')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="field">
                            <label for="cv">CV (CV, Ijazah & Transkrip jadi 1 file) <span class="req">*</span></label>
                            <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx"
                                   class="{{ $errors->has('cv') ? 'error' : '' }}" style="padding:8px 10px;">
                            <p style="font-size:11px;color:#8a948f;margin-top:4px;">Format PDF/DOC/DOCX, maks. 3 MB</p>
                            @error('cv')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="str_sip">STR/SIP/STRA/STRTTK</label>
                            <input id="str_sip" name="str_sip" type="file" accept=".jpg,.jpeg,.png,.pdf"
                                   class="{{ $errors->has('str_sip') ? 'error' : '' }}" style="padding:8px 10px;">
                            <p style="font-size:11px;color:#8a948f;margin-top:4px;">Format JPG/PNG/PDF, maks. 3 MB</p>
                            @error('str_sip')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Sudah vaksinasi Covid-19? <span class="req">*</span></label>
                            @error('vaksinasi_covid')<p class="field-error">{{ $message }}</p>@enderror
                            <div style="display:flex;gap:20px;margin-top:6px;flex-wrap:wrap;">
                                @foreach (['sudah_1' => 'Sudah 1 kali', 'sudah_2' => 'Sudah 2 kali', 'belum' => 'Belum pernah'] as $val => $lbl)
                                <label style="display:flex;align-items:center;gap:8px;font-family:'IBM Plex Sans',system-ui,sans-serif;font-size:14px;text-transform:none;letter-spacing:0;color:#0d1614;cursor:pointer;">
                                    <input type="radio" name="vaksinasi_covid" value="{{ $val }}" {{ old('vaksinasi_covid') === $val ? 'checked' : '' }} style="accent-color:rgb(0,119,116);"> {{ $lbl }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- C. Akun Sosial Media --}}
                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 6px;">C. Akun Sosial Media</h3>
                    <p class="form-section-sub">Opsional. Jika diisi sebagian, wajib lengkapi semua kolom.</p>
                    <div class="adj-section" x-data="adjSection('social_media_accounts', @js(old('social_media_accounts', [])))">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="adj-item">
                                <div class="adj-item-num">Akun <span x-text="idx+1"></span></div>
                                <button type="button" class="adj-remove" @click="remove(idx)">&times;</button>
                                <div class="form-row cols-2">
                                    <div class="field"><label>Platform</label>
                                        <select :name="`social_media_accounts[${idx}][platform]`" x-model="item.platform" :class="{ error: $root.hasFieldError('social_media_accounts', idx, 'platform') }">
                                            <option value="">-- Pilih --</option>
                                            @foreach (['Facebook', 'Instagram', 'LinkedIn', 'TikTok', 'Twitter/X', 'Lainnya'] as $platform)
                                                <option value="{{ $platform }}">{{ $platform }}</option>
                                            @endforeach
                                        </select>
                                        <p class="field-error" x-show="$root.hasFieldError('social_media_accounts', idx, 'platform')" x-text="$root.fieldError('social_media_accounts', idx, 'platform')"></p>
                                    </div>
                                    <div class="field"><label>Link / Username</label>
                                        <input :name="`social_media_accounts[${idx}][link]`" type="text" x-model="item.link" placeholder="mis. https://instagram.com/username" :class="{ error: $root.hasFieldError('social_media_accounts', idx, 'link') }">
                                        <p class="field-error" x-show="$root.hasFieldError('social_media_accounts', idx, 'link')" x-text="$root.fieldError('social_media_accounts', idx, 'link')"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="adj-add" @click="add()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Akun
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="sumber_informasi">Darimana mengetahui informasi lowongan ini? <span class="req">*</span></label>
                            <select id="sumber_informasi" name="sumber_informasi" class="{{ $errors->has('sumber_informasi') ? 'error' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach (['Facebook', 'Instagram', 'LinkedIn', 'Teman/Keluarga', 'Website RS Azra', 'Job Portal', 'Lainnya'] as $src)
                                    <option value="{{ $src }}" {{ old('sumber_informasi') === $src ? 'selected' : '' }}>{{ $src }}</option>
                                @endforeach
                            </select>
                            @error('sumber_informasi')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- D. Pernyataan --}}
                    <h3 style="font-size:13px;font-weight:600;color:#0d1614;margin:28px 0 14px;">D. Pernyataan</h3>
                    <div class="field">
                        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-family:'IBM Plex Sans',system-ui,sans-serif;font-size:14px;text-transform:none;letter-spacing:0;color:#0d1614;line-height:1.6;">
                            <input type="checkbox" name="pernyataan" value="1" {{ old('pernyataan') ? 'checked' : '' }}
                                   style="width:16px;height:16px;flex-shrink:0;margin-top:3px;accent-color:rgb(0,119,116);"
                                   class="{{ $errors->has('pernyataan') ? 'error' : '' }}">
                            <span>Formulir lamaran kerja ini saya isi sendiri dengan sejujur-jujurnya sesuai dengan kenyataan sebenarnya. Apabila dikemudian hari data isian ini tidak sesuai dengan kenyataan yang sebenarnya maka saya bersedia diberikan sanksi sesuai dengan peraturan yang berlaku.</span>
                        </label>
                        @error('pernyataan')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="step-nav">
                    <button type="button" class="btn-prev" x-show="step > 1" @click="prev()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
                        Sebelumnya
                    </button>
                    <div x-show="step < 1" style="flex:1;"></div>

                    <div class="step-counter" style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#8a948f;text-transform:uppercase;letter-spacing:.06em;">
                        Langkah <span x-text="step"></span> dari 8
                    </div>

                    <button type="button" class="btn-next" x-show="step < 8" @click="next()">
                        Selanjutnya
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>
                    <button type="button" class="btn-submit" x-show="step === 8" @click="submitForm()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                        Kirim Lamaran
                    </button>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="apply-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-card-h bg-primary text-white">Posisi Dilamar</div>
                    <div class="sidebar-body">
                        <div class="sidebar-pos">{{ $vacancy->judul_posisi }}</div>
                        <div class="sidebar-unit">{{ $vacancy->unit->nama }}</div>
                        <div class="sidebar-meta-row">
                            <span>Jenis</span>
                            <strong>{{ $vacancy->jenis_pekerjaan->label() }}</strong>
                        </div>
                        <div class="sidebar-meta-row">
                            <span>Tenggat</span>
                            <strong>{{ $vacancy->tenggat_lamaran->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card" style="margin-top:16px;">
                    <div class="sidebar-card-h bg-primary text-white">Langkah-Langkah</div>
                    <div class="step-list">
                        @foreach (['Identitas Diri', 'Latar Belakang Keluarga', 'Pendidikan', 'Pengalaman Organisasi', 'Pengalaman Kerja', 'Minat', 'Referensi', 'Lain-Lain'] as $i => $label)
                            <div class="step-list-item"
                                 :class="{ active: step === {{ $i + 1 }}, done: step > {{ $i + 1 }} }">
                                <span class="step-list-num" x-text="step > {{ $i + 1 }} ? '✓' : '{{ $i + 1 }}'"></span>
                                {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<script>
window.__adjRegistry = {};
window.__atsFormKey = 'ats_apply_' + @js($vacancy->id);
window.__atsHasErrors = {{ $errors->any() ? 'true' : 'false' }};
window.__atsValidationErrors = @js($errors->toArray());

function applyWizard() {
    return {
        step: @php
            $errorStep = 1;
            if ($errors->any()) {
                $stepFields = [
                    1 => ['nama_lengkap','tempat_lahir','tanggal_lahir','jenis_kelamin','agama','status_perkawinan','golongan_darah','alamat_ktp','alamat_domisili','no_telepon','email','no_ktp','npwp','nama_ibu_kandung','kontak_darurat_'],
                    2 => ['ayah_','ibu_','saudara_','siblings','spouses','children'],
                    3 => ['formal_educations','achievements','informal_educations','language_skills'],
                    4 => ['organization_experiences'],
                    5 => ['is_fresh_graduate','work_experiences'],
                    6 => ['alasan_melamar','gaji_diharapkan','fasilitas_diharapkan'],
                    7 => ['references'],
                    8 => ['pernah_sakit_serius','diagnosis_sakit','kesiapan_kerja','cv','str_sip','vaksinasi_covid','social_media_accounts','sumber_informasi','pernyataan'],
                ];
                foreach ($stepFields as $step => $prefixes) {
                    foreach ($errors->keys() as $key) {
                        foreach ($prefixes as $prefix) {
                            if (str_starts_with($key, $prefix)) {
                                $errorStep = $step;
                                break 3;
                            }
                        }
                    }
                }
            }
        @endphp {{ $errorStep }},
        isFreshGraduate: {{ old('is_fresh_graduate', '0') === '1' ? 'true' : 'false' }},
        hasSavedData: false,
        _submitting: false,
        _errors: window.__atsValidationErrors || {},

        init() {
            this._validFields = new Set(
                Array.from(document.querySelectorAll('#apply-form [name]'))
                    .map(el => el.getAttribute('name'))
                    .filter(n => n && !n.includes('['))
            );
            if (!window.__atsHasErrors) {
                const saved = this._load();
                if (saved) { this.hasSavedData = true; }
            }
            window.addEventListener('beforeunload', () => { this._save(); });
            const debouncedSave = this._debounce(() => this._save(), 800);
            document.getElementById('apply-form').addEventListener('input', debouncedSave);
            document.getElementById('apply-form').addEventListener('change', debouncedSave);
        },

        restoreProgress() {
            const saved = this._load();
            if (!saved) { return; }
            this.step = saved.step || 1;
            this.isFreshGraduate = !!saved.isFreshGraduate;
            this.hasSavedData = false;

            this.$nextTick(() => {
                if (saved.fields) {
                    Object.entries(saved.fields).forEach(([name, val]) => {
                        if (!this._validFields.has(name)) { return; }
                        if (name === 'is_fresh_graduate') { return; }

                        const el = document.querySelector(`#apply-form [name="${CSS.escape(name)}"]`);
                        if (!el || el.type === 'file') { return; }

                        if (el.type === 'radio') {
                            const target = document.querySelector(`#apply-form [name="${CSS.escape(name)}"][value="${CSS.escape(val)}"]`);
                            if (target) { target.checked = true; target.dispatchEvent(new Event('change', { bubbles: true })); }
                            return;
                        }

                        if (el.type === 'checkbox') {
                            el.checked = (val === el.value);
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                            return;
                        }

                        el.value = val;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }

                if (saved.adjItems) {
                    Object.entries(saved.adjItems).forEach(([prefix, items]) => {
                        const comp = window.__adjRegistry[prefix];
                        if (comp) { comp.items = items; }
                    });
                }
            });
        },

        discardProgress() {
            this._clear();
            this.hasSavedData = false;
        },

        _save() {
            if (window.__atsHasErrors || this._submitting) { return; }
            const fields = {};
            document.querySelectorAll('#apply-form [name]').forEach(el => {
                const name = el.getAttribute('name');
                if (!name || name.includes('[') || el.type === 'file' || el.type === 'submit') { return; }
                if (el.type === 'radio' || el.type === 'checkbox') { return; }
                fields[name] = el.value;
            });
            document.querySelectorAll('#apply-form input[type="radio"]:checked, #apply-form input[type="checkbox"]:checked').forEach(el => {
                const name = el.getAttribute('name');
                if (name && !name.includes('[')) { fields[name] = el.value; }
            });
            const adjItems = {};
            Object.entries(window.__adjRegistry).forEach(([k, comp]) => {
                adjItems[k] = comp.items;
            });
            const data = { step: this.step, isFreshGraduate: this.isFreshGraduate, fields, adjItems, savedAt: Date.now() };
            try { localStorage.setItem(window.__atsFormKey, JSON.stringify(data)); } catch {}
        },

        _load() {
            try {
                const raw = localStorage.getItem(window.__atsFormKey);
                if (!raw) { return null; }
                const data = JSON.parse(raw);
                if (!data || !data.savedAt) { return null; }
                if (Date.now() - data.savedAt > 7 * 24 * 60 * 60 * 1000) { this._clear(); return null; }
                return data;
            } catch { return null; }
        },

        _clear() {
            try { localStorage.removeItem(window.__atsFormKey); } catch {}
        },

        _debounce(fn, ms) {
            let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
        },

        fieldError(prefix, idx, field) {
            const key = prefix + '.' + idx + '.' + field;
            return this._errors[key] ? this._errors[key][0] : '';
        },

        hasFieldError(prefix, idx, field) {
            return !!this._errors[prefix + '.' + idx + '.' + field];
        },

        next() {
            this._save();
            if (this.step < 8) { this.step++; this.scrollTop(); }
        },

        prev() {
            this._save();
            if (this.step > 1) { this.step--; this.scrollTop(); }
        },

        scrollTop() {
            window.scrollTo({ top: document.querySelector('.apply-header').offsetTop - 20, behavior: 'smooth' });
        },

        submitForm() {
            this._submitting = true;
            this._clear();
            document.getElementById('apply-form').submit();
        },
    };
}

function adjSection(prefix, initialItems) {
    const blank = () => ({});
    return {
        items: (initialItems && initialItems.length) ? initialItems : [],
        init() {
            window.__adjRegistry[prefix] = this;
        },
        add() { this.items.push(blank()); },
        remove(idx) { this.items.splice(idx, 1); },
    };
}
</script>

</x-layouts.public>
