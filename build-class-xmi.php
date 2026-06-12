<?php

/**
 * Generator XMI 2.1 (UML 2.1) diagram kelas ATS v2 untuk impor ke Sparx EA 15.2.
 * Jalankan: php build-class-xmi.php  -> menghasilkan ats-class-diagram.xmi
 *
 * Konvensi mengikuti rekrutmen-azra-usecase.xmi (terbukti terimpor):
 *  - encoding windows-1252, namespace schema.omg.org/UML/2.1, Model name EA_Model.
 *  - Asosiasi: packagedElement uml:Association + 2 memberEnd + 2 ownedEnd(type).
 *  - Komposisi: ownedEnd sisi-whole pakai aggregation="composite".
 *  - TANPA multiplicity (lowerValue/upperValue) -> garis polos sesuai permintaan.
 *  - Diagram auto-draw via xmi:Extension (geometry grid per paket).
 */

// ---------- Tipe primitif ----------
$primitives = ['String', 'int', 'long', 'boolean', 'Date', 'DateTime'];

// ---------- Enum: nama => [literal...] ----------
$enums = [
    'Role' => ['HrAdmin', 'HrManager', 'UnitHead', 'Director', 'Employee'],
    'EmploymentType' => ['FullTime', 'PartTime', 'Contract', 'Internship'],
    'VacancyStatus' => ['Draft', 'Published', 'Closed'],
    'JenisKelamin' => ['LakiLaki', 'Perempuan'],
    'StatusPerkawinan' => ['BelumMenikah', 'Menikah', 'Duda', 'Janda'],
    'GolonganDarah' => ['A', 'B', 'AB', 'O'],
    'JenisPendidikan' => ['SD', 'SMP', 'SMAAtauSMK', 'D1', 'D2', 'D3', 'D4AtauS1', 'S2', 'S3'],
    'TingkatKemampuanBahasa' => ['Baik', 'Sedang', 'Kurang'],
    'ApplicationStageStatus' => ['Pending', 'Aktif', 'Reserved', 'Selesai', 'Gagal'],
    'QuestionType' => ['Mc', 'Essay'],
    'DiscDimension' => ['D', 'I', 'S', 'C'],
    'MbtiPole' => ['E', 'I', 'S', 'N', 'T', 'F', 'J', 'P'],
    'InterviewTemplateType' => ['KriteriaPenilaian', 'Kesiapan'],
    'OfferingLetterStatus' => ['Pending', 'Accepted', 'Rejected'],
    'McuStatus' => ['Lulus', 'Ditangguhkan', 'TidakLulus'],
];

// ---------- Kelas: nama => [pkg, attrs => [[name,type]...], ops => [[name,ret]...]] ----------
// type = primitif | nama enum | nama kelas (untuk return operasi). '' ret = void.
$classes = [
    // ===== Auth & Org =====
    'User' => ['AuthOrg', [
        ['name', 'String'], ['username', 'String'], ['role', 'Role'],
        ['must_change_password', 'boolean'], ['is_active', 'boolean'],
    ], [['hasRole', 'boolean'], ['isHrAdmin', 'boolean']]],
    'Employee' => ['AuthOrg', [
        ['nip', 'String'], ['nama_karyawan', 'String'], ['posisi_pekerjaan', 'String'],
        ['profesi', 'String'], ['jabatan', 'String'],
    ], []],
    'Unit' => ['AuthOrg', [['nama', 'String']], []],

    // ===== Workflow =====
    'Stage' => ['Workflow', [
        ['key', 'String'], ['nama', 'String'], ['is_locked_first', 'boolean'], ['is_locked_last', 'boolean'],
    ], []],
    'WorkflowTemplate' => ['Workflow', [['nama', 'String']], []],
    'WorkflowTemplateSnapshot' => ['Workflow', [['nama', 'String']], []],
    'WorkflowTemplateSnapshotStage' => ['Workflow', [
        ['position', 'int'], ['key', 'String'], ['nama', 'String'],
        ['is_locked_first', 'boolean'], ['is_locked_last', 'boolean'],
    ], []],

    // ===== Candidate Profile =====
    'Candidate' => ['Candidate', [
        ['nama_lengkap', 'String'], ['email', 'String'], ['no_telepon', 'String'],
        ['tempat_lahir', 'String'], ['tanggal_lahir', 'Date'], ['jenis_kelamin', 'JenisKelamin'],
        ['agama', 'String'], ['status_perkawinan', 'StatusPerkawinan'], ['golongan_darah', 'GolonganDarah'],
        ['alamat_ktp', 'String'], ['alamat_domisili', 'String'], ['no_ktp', 'String'], ['npwp', 'String'],
        ['nama_ibu_kandung', 'String'], ['kontak_darurat_nama', 'String'], ['kontak_darurat_no_telp', 'String'],
        ['kontak_darurat_hubungan', 'String'], ['ayah_nama', 'String'], ['ayah_usia', 'int'],
        ['ayah_pendidikan_terakhir', 'JenisPendidikan'], ['ayah_pekerjaan', 'String'],
        ['ibu_nama', 'String'], ['ibu_usia', 'int'], ['ibu_pendidikan_terakhir', 'JenisPendidikan'],
        ['ibu_pekerjaan', 'String'], ['saudara_anak_ke', 'int'], ['saudara_dari_bersaudara', 'int'],
        ['is_fresh_graduate', 'boolean'], ['pernah_sakit_serius', 'boolean'], ['diagnosis_sakit', 'String'],
        ['vaksinasi_covid', 'String'],
    ], []],
    'CandidateSibling' => ['Candidate', [
        ['nama', 'String'], ['usia', 'int'], ['jenis_kelamin', 'JenisKelamin'],
        ['pendidikan_terakhir', 'JenisPendidikan'], ['pekerjaan_jabatan', 'String'],
    ], []],
    'CandidateSpouse' => ['Candidate', [
        ['nama', 'String'], ['usia', 'int'], ['jenis_kelamin', 'JenisKelamin'],
        ['pendidikan_terakhir', 'JenisPendidikan'], ['pekerjaan_jabatan', 'String'],
    ], []],
    'CandidateChild' => ['Candidate', [
        ['nama', 'String'], ['usia', 'int'], ['jenis_kelamin', 'JenisKelamin'],
        ['pendidikan_terakhir', 'JenisPendidikan'], ['pekerjaan_jabatan', 'String'],
    ], []],
    'CandidateFormalEducation' => ['Candidate', [
        ['jenis_pendidikan', 'JenisPendidikan'], ['nama_sekolah', 'String'], ['kota', 'String'],
        ['tahun_lulus', 'int'], ['ip_nilai', 'String'], ['jurusan', 'String'],
    ], []],
    'CandidateInformalEducation' => ['Candidate', [
        ['nama', 'String'], ['topik', 'String'], ['periode_mulai', 'Date'],
        ['periode_selesai', 'Date'], ['penyelenggara', 'String'],
    ], []],
    'CandidateAchievement' => ['Candidate', [['nama_prestasi', 'String'], ['tahun', 'int']], []],
    'CandidateLanguageSkill' => ['Candidate', [
        ['nama_bahasa', 'String'], ['berbicara', 'TingkatKemampuanBahasa'],
        ['menulis', 'TingkatKemampuanBahasa'], ['membaca', 'TingkatKemampuanBahasa'],
    ], []],
    'CandidateOrganizationExperience' => ['Candidate', [
        ['nama_organisasi', 'String'], ['jabatan', 'String'], ['periode_mulai', 'Date'],
        ['periode_selesai', 'Date'], ['keterangan', 'String'],
    ], []],
    'CandidateWorkExperience' => ['Candidate', [
        ['nama_perusahaan', 'String'], ['jabatan', 'String'], ['alamat_perusahaan', 'String'],
        ['periode_mulai', 'Date'], ['periode_selesai', 'Date'], ['rincian_tugas', 'String'],
        ['gaji_terakhir', 'String'], ['alasan_meninggalkan', 'String'],
    ], []],

    // ===== Recruitment Core =====
    'Vacancy' => ['Recruitment', [
        ['judul_posisi', 'String'], ['jenis_pekerjaan', 'EmploymentType'], ['deskripsi_pekerjaan', 'String'],
        ['kualifikasi', 'String'], ['jumlah_posisi', 'int'], ['tenggat_lamaran', 'Date'], ['status', 'VacancyStatus'],
    ], [['scopePublished', '']]],
    'Application' => ['Recruitment', [
        ['token', 'String'], ['cv_path', 'String'], ['alasan_melamar', 'String'], ['gaji_diharapkan', 'int'],
        ['fasilitas_diharapkan', 'String'], ['kesiapan_kerja', 'String'], ['str_sip_path', 'String'],
        ['sumber_informasi', 'String'],
    ], [['currentStage', 'ApplicationStage']]],
    'ApplicationStage' => ['Recruitment', [
        ['position', 'int'], ['key', 'String'], ['nama', 'String'], ['status', 'ApplicationStageStatus'],
        ['catatan', 'String'], ['jadwal', 'DateTime'], ['lokasi', 'String'],
    ], []],
    'ApplicationReference' => ['Recruitment', [['nama_karyawan', 'String'], ['hubungan', 'String'], ['keterangan', 'String']], []],
    'ApplicationSocialMediaAccount' => ['Recruitment', [['platform', 'String'], ['link', 'String']], []],

    // ===== Assessment =====
    'QuestionBankTemplate' => ['Assessment', [['nama', 'String']], []],
    'Question' => ['Assessment', [
        ['tipe', 'QuestionType'], ['pertanyaan', 'String'], ['nilai_poin', 'int'], ['urutan', 'int'],
    ], [['correctOption', 'QuestionOption']]],
    'QuestionOption' => ['Assessment', [['teks_opsi', 'String'], ['is_correct', 'boolean']], []],
    'VacancyTest' => ['Assessment', [['batas_waktu_menit', 'int']], [['totalNilaiMaksimal', 'int']]],
    'VacancyTestQuestion' => ['Assessment', [['urutan', 'int']], []],
    'VacancyTestSnapshot' => ['Assessment', [['batas_waktu_menit', 'int']], [['totalNilaiMaksimal', 'int']]],
    'VacancyTestSnapshotQuestion' => ['Assessment', [
        ['urutan', 'int'], ['tipe', 'QuestionType'], ['pertanyaan', 'String'], ['nilai_poin', 'int'],
    ], []],
    'VacancyTestSnapshotOption' => ['Assessment', [['teks_opsi', 'String'], ['is_correct', 'boolean']], []],
    'TestSubmission' => ['Assessment', [
        ['token', 'String'], ['started_at', 'DateTime'], ['submitted_at', 'DateTime'], ['total_skor', 'int'],
    ], [['isSubmitted', 'boolean'], ['isExpired', 'boolean'], ['remainingSeconds', 'int']]],
    'TestAnswer' => ['Assessment', [['jawaban_teks', 'String'], ['skor', 'int'], ['is_reviewed', 'boolean']], []],
    'DiscQuestion' => ['Assessment', [['urutan', 'int']], []],
    'DiscQuestionWord' => ['Assessment', [['teks', 'String'], ['dimensi', 'DiscDimension']], []],
    'DiscSubmission' => ['Assessment', [
        ['token', 'String'], ['started_at', 'DateTime'], ['submitted_at', 'DateTime'],
    ], [['isSubmitted', 'boolean']]],
    'DiscAnswer' => ['Assessment', [], []],
    'DiscResult' => ['Assessment', [
        ['skor_d', 'int'], ['skor_i', 'int'], ['skor_s', 'int'], ['skor_c', 'int'],
        ['tipe_primer', 'DiscDimension'], ['tipe_sekunder', 'DiscDimension'],
    ], [['profilRingkasan', 'String']]],
    'MbtiQuestion' => ['Assessment', [
        ['urutan', 'int'], ['dikotomi', 'String'], ['pernyataan_a', 'String'], ['kutub_a', 'MbtiPole'], ['pernyataan_b', 'String'],
    ], [['kutubB', 'MbtiPole']]],
    'MbtiSubmission' => ['Assessment', [
        ['token', 'String'], ['started_at', 'DateTime'], ['submitted_at', 'DateTime'],
    ], [['isSubmitted', 'boolean']]],
    'MbtiAnswer' => ['Assessment', [['pilihan', 'String']], []],
    'MbtiResult' => ['Assessment', [
        ['skor_e', 'int'], ['skor_i', 'int'], ['skor_s', 'int'], ['skor_n', 'int'],
        ['skor_t', 'int'], ['skor_f', 'int'], ['skor_j', 'int'], ['skor_p', 'int'],
        ['tipe', 'String'], ['kekuatan_ei', 'int'], ['kekuatan_sn', 'int'], ['kekuatan_tf', 'int'], ['kekuatan_jp', 'int'],
    ], []],

    // ===== Interview & Outcomes =====
    'InterviewTemplate' => ['Interview', [['nama', 'String'], ['tipe', 'InterviewTemplateType']], []],
    'InterviewTemplateItem' => ['Interview', [['teks', 'String'], ['urutan', 'int']], []],
    'VacancyInterviewTemplate' => ['Interview', [['stage_key', 'String']], []],
    'InterviewResult' => ['Interview', [['keputusan', 'String'], ['catatan', 'String'], ['submitted_at', 'DateTime']], []],
    'InterviewResultRating' => ['Interview', [['nama_kriteria', 'String'], ['nilai', 'int']], []],
    'InterviewReadinessAnswer' => ['Interview', [['pertanyaan', 'String'], ['jawaban', 'boolean']], []],
    'OfferingLetter' => ['Interview', [
        ['jabatan_ditawarkan', 'String'], ['gaji', 'String'], ['tanggal_mulai', 'Date'], ['catatan', 'String'],
        ['sent_at', 'DateTime'], ['status', 'OfferingLetterStatus'], ['responded_at', 'DateTime'], ['rejection_reason', 'String'],
    ], [['isPending', 'boolean'], ['isResponded', 'boolean']]],
    'McuResult' => ['Interview', [
        ['keputusan', 'McuStatus'], ['dokumen_path', 'String'], ['catatan', 'String'], ['submitted_at', 'DateTime'],
    ], []],
    'OnboardingResult' => ['Interview', [['tanggal_bergabung', 'Date'], ['catatan', 'String'], ['sent_at', 'DateTime']], []],

    // ===== Shared =====
    'EmailTemplate' => ['Shared', [['key', 'String'], ['deskripsi', 'String'], ['subjek', 'String'], ['isi', 'String']], []],
];

// ---------- Relasi: [from, to, kind('comp'|'assoc'), label] ----------
// comp: from = WHOLE (diamond). assoc: garis polos. label opsional (role/peran).
$relations = [
    // Komposisi - Candidate memiliki sub-record profil
    ['Candidate', 'CandidateSibling', 'comp', ''],
    ['Candidate', 'CandidateSpouse', 'comp', ''],
    ['Candidate', 'CandidateChild', 'comp', ''],
    ['Candidate', 'CandidateFormalEducation', 'comp', ''],
    ['Candidate', 'CandidateInformalEducation', 'comp', ''],
    ['Candidate', 'CandidateAchievement', 'comp', ''],
    ['Candidate', 'CandidateLanguageSkill', 'comp', ''],
    ['Candidate', 'CandidateOrganizationExperience', 'comp', ''],
    ['Candidate', 'CandidateWorkExperience', 'comp', ''],
    // Komposisi - Application memiliki tahapan & hasil
    ['Application', 'ApplicationStage', 'comp', ''],
    ['Application', 'ApplicationReference', 'comp', ''],
    ['Application', 'ApplicationSocialMediaAccount', 'comp', ''],
    ['Application', 'TestSubmission', 'comp', ''],
    ['Application', 'DiscSubmission', 'comp', ''],
    ['Application', 'MbtiSubmission', 'comp', ''],
    ['Application', 'InterviewResult', 'comp', ''],
    ['Application', 'OfferingLetter', 'comp', ''],
    ['Application', 'McuResult', 'comp', ''],
    ['Application', 'OnboardingResult', 'comp', ''],
    // Komposisi - assessment internal
    ['InterviewResult', 'InterviewResultRating', 'comp', ''],
    ['InterviewResult', 'InterviewReadinessAnswer', 'comp', ''],
    ['DiscSubmission', 'DiscAnswer', 'comp', ''],
    ['DiscSubmission', 'DiscResult', 'comp', ''],
    ['MbtiSubmission', 'MbtiAnswer', 'comp', ''],
    ['MbtiSubmission', 'MbtiResult', 'comp', ''],
    ['TestSubmission', 'TestAnswer', 'comp', ''],
    ['DiscQuestion', 'DiscQuestionWord', 'comp', ''],
    ['Question', 'QuestionOption', 'comp', ''],
    ['QuestionBankTemplate', 'Question', 'comp', ''],
    ['VacancyTest', 'VacancyTestQuestion', 'comp', ''],
    ['VacancyTest', 'VacancyTestSnapshot', 'comp', ''],
    ['VacancyTestSnapshot', 'VacancyTestSnapshotQuestion', 'comp', ''],
    ['VacancyTestSnapshotQuestion', 'VacancyTestSnapshotOption', 'comp', ''],
    ['WorkflowTemplateSnapshot', 'WorkflowTemplateSnapshotStage', 'comp', ''],
    ['InterviewTemplate', 'InterviewTemplateItem', 'comp', ''],
    ['Vacancy', 'VacancyTest', 'comp', ''],

    // Asosiasi - referensi
    ['Application', 'Candidate', 'assoc', ''],
    ['Application', 'Vacancy', 'assoc', ''],
    ['Vacancy', 'Unit', 'assoc', ''],
    ['Vacancy', 'WorkflowTemplateSnapshot', 'assoc', 'snapshot-of'],
    ['WorkflowTemplateSnapshot', 'WorkflowTemplate', 'assoc', 'snapshot-of'],
    ['Employee', 'User', 'assoc', ''],
    ['Employee', 'Unit', 'assoc', ''],
    ['ApplicationStage', 'User', 'assoc', 'reviewedBy'],
    ['ApplicationStage', 'User', 'assoc', 'interviewer'],
    ['InterviewResult', 'User', 'assoc', 'interviewer'],
    ['InterviewResult', 'ApplicationStage', 'assoc', ''],
    ['McuResult', 'User', 'assoc', 'reviewer'],
    ['McuResult', 'ApplicationStage', 'assoc', ''],
    ['Stage', 'WorkflowTemplate', 'assoc', ''],
    ['VacancyTestQuestion', 'Question', 'assoc', ''],
    ['VacancyTestSnapshot', 'VacancyTest', 'assoc', 'snapshot-of'],
    ['TestSubmission', 'VacancyTestSnapshot', 'assoc', ''],
    ['TestAnswer', 'VacancyTestSnapshotQuestion', 'assoc', ''],
    ['TestAnswer', 'VacancyTestSnapshotOption', 'assoc', ''],
    ['DiscAnswer', 'DiscQuestion', 'assoc', ''],
    ['DiscAnswer', 'DiscQuestionWord', 'assoc', 'most'],
    ['DiscAnswer', 'DiscQuestionWord', 'assoc', 'least'],
    ['MbtiAnswer', 'MbtiQuestion', 'assoc', ''],
    ['VacancyInterviewTemplate', 'Vacancy', 'assoc', ''],
    ['VacancyInterviewTemplate', 'InterviewTemplate', 'assoc', ''],
    ['InterviewResultRating', 'InterviewTemplate', 'assoc', ''],
    ['InterviewReadinessAnswer', 'InterviewTemplate', 'assoc', ''],
];

// VacancyTest <-> VacancyTestSnapshot dimodelkan dua kali (comp owns + assoc snapshot-of)
// -> hapus duplikat: pertahankan composition saja.
$relations = array_values(array_filter($relations, function ($r) {
    return ! ($r[0] === 'VacancyTestSnapshot' && $r[1] === 'VacancyTest' && $r[2] === 'assoc');
}));

// ---------- Paket & diagram subdomain ----------
$packages = [
    'AuthOrg' => 'Auth & Org',
    'Workflow' => 'Workflow',
    'Candidate' => 'Candidate Profile',
    'Recruitment' => 'Recruitment Core',
    'Assessment' => 'Assessment',
    'Interview' => 'Interview & Outcomes',
    'Shared' => 'Shared',
];

// Diagram overview: kelas hub saja (atribut bisa di-suppress manual di EA).
$overview = [
    'User', 'Employee', 'Unit', 'WorkflowTemplateSnapshot', 'Vacancy', 'Candidate', 'Application',
    'QuestionBankTemplate', 'VacancyTest', 'TestSubmission', 'DiscSubmission', 'MbtiSubmission',
    'InterviewTemplate', 'InterviewResult', 'OfferingLetter', 'McuResult', 'OnboardingResult',
];

// =================== EMIT ===================
function esc($s) { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }
function id_prim($t) { return 'prim_'.$t; }
function id_enum($n) { return 'enum_'.$n; }
function id_cls($n) { return 'cls_'.$n; }

function resolveType($t, $enums, $classes)
{
    if ($t === '' ) { return null; }
    if (in_array($t, ['String', 'int', 'long', 'boolean', 'Date', 'DateTime'], true)) { return id_prim($t); }
    if (isset($enums[$t])) { return id_enum($t); }
    if (isset($classes[$t])) { return id_cls($t); }
    return id_prim('String');
}

$out = [];
$out[] = '<?xml version="1.0" encoding="windows-1252"?>';
$out[] = '<!--';
$out[] = '  Diagram Kelas - Sistem ATS Azra Hospital (ats-v2).';
$out[] = '  Format XMI 2.1 (UML 2.1) untuk impor ke Sparx Enterprise Architect 15.2.';
$out[] = '  Impor: Project Browser > klik kanan paket tujuan > Import/Export > Import Package from XMI.';
$out[] = '  Atribut = field bisnis (FK & timestamp disembunyikan; relasi diwakili garis).';
$out[] = '  Komposisi = diamond terisi (sub-record yang hidup-mati bergantung induk).';
$out[] = '  Garis tanpa angka multiplicity sesuai permintaan. Operasi = method domain nyata.';
$out[] = '  Untuk diagram Overview: pilih semua > klik kanan > Suppress Attributes/Operations.';
$out[] = '-->';
$out[] = '<xmi:XMI xmi:version="2.1"';
$out[] = '         xmlns:uml="http://schema.omg.org/spec/UML/2.1"';
$out[] = '         xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">';
$out[] = '  <xmi:Documentation exporter="Claude Code" exporterVersion="1.0"/>';
$out[] = '  <uml:Model xmi:type="uml:Model" name="EA_Model" visibility="public">';
$out[] = '    <packagedElement xmi:type="uml:Package" xmi:id="pkg_ats" name="ATS v2" visibility="public">';

// Primitives
$out[] = '      <packagedElement xmi:type="uml:Package" xmi:id="pkg_primitives" name="Primitives" visibility="public">';
foreach ($primitives as $p) {
    $out[] = '        <packagedElement xmi:type="uml:PrimitiveType" xmi:id="'.id_prim($p).'" name="'.$p.'"/>';
}
$out[] = '      </packagedElement>';

// Enums
$out[] = '      <packagedElement xmi:type="uml:Package" xmi:id="pkg_enums" name="Enums" visibility="public">';
foreach ($enums as $name => $literals) {
    $out[] = '        <packagedElement xmi:type="uml:Enumeration" xmi:id="'.id_enum($name).'" name="'.$name.'" visibility="public">';
    foreach ($literals as $i => $lit) {
        $out[] = '          <ownedLiteral xmi:type="uml:EnumerationLiteral" xmi:id="'.id_enum($name).'_'.$i.'" name="'.$lit.'"/>';
    }
    $out[] = '        </packagedElement>';
}
$out[] = '      </packagedElement>';

// Kelas dikelompokkan per paket
foreach ($packages as $pkgKey => $pkgName) {
    $out[] = '      <packagedElement xmi:type="uml:Package" xmi:id="pkg_'.$pkgKey.'" name="'.esc($pkgName).'" visibility="public">';
    foreach ($classes as $cname => $def) {
        if ($def[0] !== $pkgKey) { continue; }
        [$pkg, $attrs, $ops] = $def;
        $cid = id_cls($cname);
        $out[] = '        <packagedElement xmi:type="uml:Class" xmi:id="'.$cid.'" name="'.$cname.'" visibility="public">';
        foreach ($attrs as $j => $a) {
            $tid = resolveType($a[1], $enums, $classes);
            $typeAttr = $tid ? ' type="'.$tid.'"' : '';
            $out[] = '          <ownedAttribute xmi:type="uml:Property" xmi:id="'.$cid.'_a'.$j.'" name="'.$a[0].'" visibility="public"'.$typeAttr.'/>';
        }
        foreach ($ops as $k => $op) {
            $rid = resolveType($op[1], $enums, $classes);
            $out[] = '          <ownedOperation xmi:type="uml:Operation" xmi:id="'.$cid.'_o'.$k.'" name="'.$op[0].'" visibility="public">';
            if ($rid) {
                $out[] = '            <ownedParameter xmi:type="uml:Parameter" xmi:id="'.$cid.'_o'.$k.'_r" direction="return" name="return" type="'.$rid.'"/>';
            }
            $out[] = '          </ownedOperation>';
        }
        $out[] = '        </packagedElement>';
    }
    $out[] = '      </packagedElement>';
}

// Relasi (di level paket akar agar lintas-paket valid)
foreach ($relations as $n => $r) {
    [$from, $to, $kind, $label] = $r;
    $aid = 'as_'.$n;
    $fromEnd = $aid.'_a';
    $toEnd = $aid.'_b';
    $nameAttr = $label !== '' ? ' name="'.esc($label).'"' : '';
    $out[] = '      <packagedElement xmi:type="uml:Association" xmi:id="'.$aid.'"'.$nameAttr.'>';
    $out[] = '        <memberEnd xmi:idref="'.$fromEnd.'"/><memberEnd xmi:idref="'.$toEnd.'"/>';
    // sisi-from: untuk komposisi = whole (aggregation composite)
    $agg = $kind === 'comp' ? ' aggregation="composite"' : '';
    $out[] = '        <ownedEnd xmi:type="uml:Property" xmi:id="'.$fromEnd.'" association="'.$aid.'" type="'.id_cls($from).'"'.$agg.'/>';
    $out[] = '        <ownedEnd xmi:type="uml:Property" xmi:id="'.$toEnd.'" association="'.$aid.'" type="'.id_cls($to).'"/>';
    $out[] = '      </packagedElement>';
}

$out[] = '    </packagedElement>';
$out[] = '  </uml:Model>';

// =================== EA AUTO-DRAW ===================
$out[] = '  <xmi:Extension extender="Enterprise Architect" extenderID="6.5">';
$out[] = '    <diagrams>';

// Penomoran seqno/localID lokal per diagram (mengikuti file use-case yang terbukti).
$emitDiagram = function ($diaId, $title, $ownerPkg, $members) use (&$out) {
    $out[] = '      <diagram xmi:id="'.$diaId.'">';
    $out[] = '        <model package="'.$ownerPkg.'" owner="'.$ownerPkg.'" localID="1"/>';
    $out[] = '        <properties name="'.esc($title).'" type="Logical"/>';
    $out[] = '        <project author="Claude Code" version="1.0"/>';
    $out[] = '        <elements>';
    $cols = 4; $bw = 200; $bh = 150; $gx = 80; $gy = 70; $x0 = 40; $y0 = 40;
    $seq = 0;
    foreach (array_values($members) as $i => $cname) {
        $col = $i % $cols; $row = intdiv($i, $cols);
        $left = $x0 + $col * ($bw + $gx);
        $top = $y0 + $row * ($bh + $gy);
        $right = $left + $bw; $bottom = $top + $bh;
        $seq++;
        $out[] = '          <element geometry="Left='.$left.';Top='.$top.';Right='.$right.';Bottom='.$bottom.';" subject="'.id_cls($cname).'" seqno="'.$seq.'"/>';
    }
    $out[] = '        </elements>';
    $out[] = '      </diagram>';
};

// Overview
$emitDiagram('dia_overview', 'ATS v2 - Class Overview', 'pkg_ats', $overview);
// Per subdomain
foreach ($packages as $pkgKey => $pkgName) {
    $members = [];
    foreach ($classes as $cname => $def) {
        if ($def[0] === $pkgKey) { $members[] = $cname; }
    }
    if (! $members) { continue; }
    $emitDiagram('dia_'.$pkgKey, 'ATS v2 - '.$pkgName, 'pkg_'.$pkgKey, $members);
}

$out[] = '    </diagrams>';
$out[] = '  </xmi:Extension>';
$out[] = '</xmi:XMI>';

$xml = implode("\n", $out)."\n";
file_put_contents(__DIR__.'/ats-class-diagram.xmi', $xml);

// Ringkasan ke stdout
$nClasses = count($classes);
$nAttrs = array_sum(array_map(fn ($d) => count($d[1]), $classes));
$nOps = array_sum(array_map(fn ($d) => count($d[2]), $classes));
$nComp = count(array_filter($relations, fn ($r) => $r[2] === 'comp'));
$nAssoc = count(array_filter($relations, fn ($r) => $r[2] === 'assoc'));
echo "ats-class-diagram.xmi dibuat.\n";
echo "Kelas: $nClasses | Atribut: $nAttrs | Operasi: $nOps | Enum: ".count($enums)."\n";
echo "Relasi: komposisi $nComp, asosiasi $nAssoc\n";
echo "Diagram: 1 overview + ".count($packages)." subdomain\n";
