# Callback failed (Gagal) candidates into the next Vacancy period

## Status

accepted

## Context & Decision

HR Admin wants to invite candidates who **failed** a past hiring round back into the next
round for the same job. We add a **Callback** flow: when HR works a **Vacancy**, they open a
**Callback list** of candidates whose prior **Application** under the *same **JobTemplate***
ended **Gagal**, select some, and fire an invite email. The candidate **re-applies fresh** —
a new `Application` through the normal public apply flow — they are never auto-cloned into the
new Vacancy.

Scope and shape:

- **Target set**: prior `Gagal` applications, scoped to the new Vacancy's `JobTemplate`. One row
  per failure (failure history is kept, not deduped). Default view shows candidates who failed
  *past CV screening* (stage `key` ∉ the `skrining_cv_*` set); HR can widen.
- **Excludes**: candidates already hired and candidates who self-applied to the target Vacancy
  without an invite. "Hired" is derived as *a candidate with an Application whose onboarding
  completed* — there is no Candidate↔`Employee` foreign key, so the Employee table cannot be
  used. An invited candidate who later applies is **not** excluded; they get a "Responded ✓"
  badge. "Active elsewhere" is a badge, not an exclude.
- **Carry-over to a new period**: callback only sends candidates to an **open** Vacancy. If the
  Vacancy HR triggers from is Closed/expired and the `JobTemplate` has no open Vacancy, HR is
  routed to the normal **Publish** form (prefilled) to open the next period first; the invites
  then point there. An **Archived** `JobTemplate` blocks callback.
- **Permission**: `VacancyPolicy::callback`, HrAdmin-only (matches `create`/`update`).
- **Invite record**: persist `callback_invites (vacancy_id, candidate_id, invited_by,
  invited_at)`, unique on `(vacancy_id, candidate_id)`; dedup at send (one email per candidate);
  idempotent resend allowed. Email via a seeded `EmailTemplate` + `EmailNotificationService`.
- **Response** ("Responded ✓") is **derived** from the existence of the candidate's Application
  on the target Vacancy — no extra column.

## Why

- **Re-apply fresh, not auto-clone.** An `Application` carries period-specific data (`cv_path`,
  `gaji_diharapkan`, `kesiapan_kerja`). Cloning a year-old application inherits a stale CV and a
  stale salary expectation. Re-applying regenerates that data and respects the existing
  one-application-per-`(candidate, vacancy)` rule.
- **Scoped to JobTemplate.** `JobTemplate` is the stable "this job" identity (see ADR-0001);
  matching on free-text `judul_posisi` across templates is fragile. The obvious callback target
  for this quarter's "Perawat IGD" is whoever failed last quarter's "Perawat IGD".
- **Default filter past screening.** Screening rejects dominate by volume and are mostly
  unqualified-on-paper; defaulting to candidates who reached interview-or-later keeps the list a
  signal, not noise — while still letting HR widen.
- **Callback-from-closed publishes the next period.** Callbacks must land on an applyable
  Vacancy. Rather than dead links, an expired round routes HR through Publish — which needs real
  human inputs (deadline, headcount) and freezes a config snapshot, so it cannot be silently
  auto-generated.
- **Derived response.** Application-per-`(candidate, vacancy)` is unique and timestamped, so the
  ROI signal exists already; a computed badge avoids coupling a write into the public apply flow.

## Considered & rejected

- **Reuse / restore the talent pool feature**: rejected. Talent pool (added then reverted Jun
  2026) was a *manual candidate-level flag* on **Reserved** (Ditangguhkan) candidates with its
  own browse page. Callback is a different concept — it targets **Gagal** candidates via their
  prior `Application`, is triggered per Vacancy, and tracks invites. The talent-pool flag is not
  reintroduced.
- **Auto-clone failed applications into the new Vacancy**: rejected — inherits stale CV/salary
  and bypasses the apply flow.
- **Cross-template / unit-wide / system-wide candidate matching**: rejected — fragile free-text
  matching; JobTemplate scope is precise.
- **Silent auto-publish of the next period (auto deadline)**: rejected — guessing
  `tenggat_lamaran`/headcount is wrong and surprising; route through the Publish form instead.
- **Persisted `responded_at` / `application_id` on the invite**: rejected for v1 — derivable;
  revisit only if historical conversion reports that survive data changes are needed.

## Consequences

- The invite endpoint re-runs the eligibility query (`CallbackCandidateFinder::forVacancy`,
  widest set) against the submitted `candidate_ids` and persists/sends only to ids in that set;
  `exists:candidates,id` remains as a cheap first-pass type guard. A crafted POST cannot invite
  an out-of-list candidate (hired, wrong template, self-applied-without-invite). The screening
  filter is treated as a view toggle, not an eligibility rule, so screening-stage failures stay
  invitable.
