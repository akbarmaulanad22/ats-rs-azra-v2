// Screenshot driver for ATS v2 system-flow documentation.
//
// Usage:
//   node scripts/screenshot-flows.mjs slice      # candidate apply -> HR screening (review slice)
//   node scripts/screenshot-flows.mjs all         # every section
//   node scripts/screenshot-flows.mjs auth pipeline   # named sections
//
// Requires the app running on baseURL (see scripts/_fixtures.json) and the DB
// seeded via DummyCandidateSeeder. Screenshots land in docs/alur-sistem/screenshots/.

import { chromium } from 'playwright';
import { readFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, '..');
const fx = JSON.parse(readFileSync(resolve(__dirname, '_fixtures.json'), 'utf8'));
const SHOT_DIR = resolve(root, 'docs/alur-sistem/screenshots');
mkdirSync(SHOT_DIR, { recursive: true });

const base = fx.baseURL.replace(/\/$/, '');
const url = (path) => (/^https?:\/\//.test(path) ? path : `${base}/${path.replace(/^\//, '')}`);
const appByStage = (key) => fx.apps.find((a) => a.active_stage === key);

let browser;
const sessions = {}; // role -> authenticated browser context

async function contextFor(role) {
  if (role === 'public') {
    return browser.newContext({ viewport: { width: 1440, height: 900 } });
  }
  if (sessions[role]) {
    return sessions[role];
  }
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const creds = fx.users[role];
  if (!creds) {
    throw new Error(`No credentials for role ${role}`);
  }
  const page = await ctx.newPage();
  await page.goto(url('login'), { waitUntil: 'networkidle' });
  await page.fill('input[name="username"]', creds.u);
  await page.fill('input[name="password"]', creds.p);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.click('button[type="submit"]'),
  ]);
  // Skip the first-login password-change wall if it appears.
  if (page.url().includes('ubah-password')) {
    throw new Error(`Role ${role} hit must_change_password wall; reset it in the DB.`);
  }
  await page.close();
  sessions[role] = ctx;
  return ctx;
}

async function shot(role, name, path, prep) {
  const ctx = await contextFor(role);
  const page = await ctx.newPage();
  try {
    await page.goto(url(path), { waitUntil: 'networkidle' });
    if (prep) {
      await prep(page);
    }
    await page.waitForTimeout(400);
    const file = resolve(SHOT_DIR, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true });
    console.log(`  ok  ${name}  <-  ${path}  (HTTP ${'?'})`);
  } catch (err) {
    console.log(`  ERR ${name}  <-  ${path}  ::  ${err.message.split('\n')[0]}`);
  } finally {
    await page.close();
    if (role === 'public') {
      await ctx.close();
    }
  }
}

// ---- Sections -------------------------------------------------------------

const sections = {
  async auth() {
    await shot('public', '01-login', 'login');
    await shot('hr_admin', '02-dashboard-hr-admin', 'dashboard');
    await shot('unit_head', '03-dashboard-unit-head', 'dashboard');
    await shot('hr_manager', '04-dashboard-hr-manager', 'dashboard');
    await shot('director', '05-dashboard-direktur', 'dashboard');
    await shot('employee', '06-dashboard-employee', 'dashboard');
  },

  async candidate() {
    await shot('public', '10-karier-list', 'karier');
    await shot('public', '11-karier-detail', `karier/${fx.published_vacancy_id}`);
    await shot('public', '12-lamar-form', `karier/${fx.published_vacancy_id}/lamar`);
    const anyApp = fx.apps[0];
    await shot('public', '13-lamar-konfirmasi', `karier/lamaran/${anyApp.token}`);
    await shot('public', '14-status-kandidat', `lamaran/${anyApp.token}`);
  },

  // Filled-state variants of pipeline stages (data from DemoPipelineCaseSeeder),
  // captured as the role that owns each stage so the populated form/result shows.
  async cases() {
    const v = fx.demo_vacancy_id;
    const c = fx.cases ?? {};
    const detail = (id) => `lowongan/${v}/pipeline/${id}`;
    if (c.tes_kompetensi) {
      await shot('hr_admin', '25b-tes-kompetensi-terisi', detail(c.tes_kompetensi));
    }
    if (c.wawancara_user) {
      await shot('unit_head', '26b-wawancara-user-penilaian', detail(c.wawancara_user));
    }
    if (c.wawancara_manajer_hr) {
      await shot('hr_manager', '27b-wawancara-manajer-hr-penilaian', detail(c.wawancara_manajer_hr));
    }
    if (c.wawancara_direktur) {
      await shot('director', '28b-wawancara-direktur-penilaian', detail(c.wawancara_direktur));
    }
    if (c.tes_disc) {
      await shot('hr_admin', '29b-tes-disc-selesai', detail(c.tes_disc));
    }
    if (c.tes_mbti) {
      await shot('hr_admin', '30b-tes-mbti-selesai', detail(c.tes_mbti));
    }
    if (c.mcu) {
      await shot('hr_admin', '32b-mcu-input', detail(c.mcu));
    }
    // Callback list, now populated with eligible/invited/responded candidates.
    await shot('hr_admin', '34b-callback-terisi', `lowongan/${v}/panggil-kembali`);
    if (c.callback_reapply) {
      await shot('hr_admin', '34c-callback-melamar-kembali', detail(c.callback_reapply));
    }
  },

  // Public token-based candidate pages (no login): competency test, DiSC,
  // MBTI, and offering-letter response. Tokens come from _fixtures.json.
  async tokens() {
    const t = fx.tokens ?? {};
    if (t.tes_kompetensi) {
      await shot('public', '15-tes-kompetensi', `tes/${t.tes_kompetensi}`);
    }
    if (t.tes_disc) {
      await shot('public', '16-tes-disc', `tes-disc/${t.tes_disc}`);
    }
    if (t.tes_mbti) {
      await shot('public', '17-tes-mbti', `tes-mbti/${t.tes_mbti}`);
    }
    if (t.offering_url) {
      // Offering routes require a signed URL (see routes/web.php).
      await shot('public', '18-penawaran-terima', t.offering_url);
    }
  },

  async pipeline() {
    const v = fx.demo_vacancy_id;
    await shot('hr_admin', '20-lowongan-list', 'lowongan');
    await shot('hr_admin', '21-pipeline-board', `lowongan/${v}/pipeline`);

    // Per stage, capture the candidate detail as the role that owns the
    // stage so the actual decision form (Lulus/Gagal/Cadangan) is visible.
    // Map source: VacancyPipelineController showApplication() responsibility match.
    const stageRole = {
      lamaran: 'hr_admin',
      skrining_cv_hr: 'hr_admin',
      skrining_cv_user: 'unit_head',
      tes_kompetensi: 'hr_admin',
      wawancara_user: 'unit_head',
      wawancara_manajer_hr: 'hr_manager',
      wawancara_direktur: 'director',
      tes_disc: 'hr_admin',
      tes_mbti: 'hr_admin',
      surat_penawaran: 'hr_admin',
      mcu: 'hr_admin',
      onboarding: 'hr_admin',
    };
    let n = 22;
    for (const app of fx.apps) {
      const key = app.active_stage ?? 'onboarding';
      const role = stageRole[key] ?? 'hr_admin';
      const label = (app.active_stage ?? 'selesai').replace(/_/g, '-');
      await shot(role, `${n}-pipeline-${label}`, `lowongan/${v}/pipeline/${app.id}`);
      n += 1;
    }

    await shot('hr_admin', `${n}-callback`, `lowongan/${v}/panggil-kembali`);
  },

  async templates() {
    await shot('hr_admin', '50-template-alur-list', 'template-alur');
    await shot('hr_admin', '51-template-lowongan-list', 'template-lowongan');
    await shot('hr_admin', '52-template-wawancara-list', 'template-wawancara');
    await shot('hr_admin', '53-template-bank-soal-list', 'template-bank-soal');
    await shot('hr_admin', '54-template-email-list', 'pengaturan/template-email');
  },

  async management() {
    await shot('hr_admin', '60-karyawan-list', 'karyawan');
    await shot('hr_admin', '61-akun-list', 'akun');
    await shot('hr_admin', '62-unit-list', 'unit');
  },

  // Review slice: candidate apply -> HR screening, end to end.
  async slice() {
    await shot('public', '01-login', 'login');
    await shot('public', '10-karier-list', 'karier');
    await shot('public', '11-karier-detail', `karier/${fx.published_vacancy_id}`);
    await shot('public', '12-lamar-form', `karier/${fx.published_vacancy_id}/lamar`);
    await shot('hr_admin', '20-lowongan-list', 'lowongan');
    await shot('hr_admin', '21-pipeline-board', `lowongan/${fx.demo_vacancy_id}/pipeline`);
    const screening = appByStage('skrining_cv') ?? fx.apps[2];
    await shot('hr_admin', '22-pipeline-detail-skrining', `lowongan/${fx.demo_vacancy_id}/pipeline/${screening.id}`);
  },
};

// ---- Runner ---------------------------------------------------------------

const requested = process.argv.slice(2);
const toRun = requested.length === 0 ? ['slice'] : requested;
const order = ['auth', 'candidate', 'tokens', 'pipeline', 'templates', 'management'];
const names = toRun.includes('all') ? order : toRun;

browser = await chromium.launch();
try {
  for (const name of names) {
    if (!sections[name]) {
      console.log(`! unknown section: ${name}`);
      continue;
    }
    console.log(`\n== ${name} ==`);
    await sections[name]();
  }
} finally {
  for (const ctx of Object.values(sessions)) {
    await ctx.close();
  }
  await browser.close();
}
console.log('\ndone.');
