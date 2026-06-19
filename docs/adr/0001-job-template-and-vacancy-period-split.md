# Split Vacancy into JobTemplate + period-bound Vacancy, snapshot-on-publish

## Status

accepted

## Context & Decision

HR Admin needs to publish the same job repeatedly across hiring rounds. We split the job
concept in two: **JobTemplate** (reusable definition — judul, unit, jenis_pekerjaan,
deskripsi, default kualifikasi, and live references to workflow/test/interview templates)
and **Vacancy** (a time-boxed publication a candidate applies to — carries period, headcount,
status, and an optional per-period kualifikasi override). A `JobTemplate` has many `Vacancy`;
`Application.vacancy_id` keeps pointing at the period instance.

Publishing a Vacancy **snapshots/clones** the template's content onto the Vacancy (scalar text
copied; workflow + competency-test config frozen via the existing snapshot/clone machinery;
interview templates copied into the `vacancy_interview_templates` pivot). Editing a JobTemplate
later never mutates already-published Vacancies — the *next* publication picks up the change.

## Why

- A published Vacancy is an HR/legal record a candidate acted on; retroactively rewriting its
  text because someone tweaked a template is wrong. This is the same reason the codebase already
  snapshots `WorkflowTemplate→WorkflowTemplateSnapshot` and `VacancyTest→VacancyTestSnapshot`.
- Keeping the name **Vacancy** on the period instance means `Application`, the pipeline, the
  career portal, and the UC narrative ("Lamar Lowongan", "Lihat Lowongan") keep their meaning —
  we only *add* `JobTemplate` upstream rather than renaming the applyable entity.
- Live references from JobTemplate to workflow/test/interview templates (frozen only at publish)
  let templates improve over time without rotting; each round freezes the current best config.

## Considered & rejected

- **Live reference** (Vacancy reads template text through the FK): rejected — a template edit
  would silently rewrite every closed hiring round.
- **Freeze config at template-create time** (Option B in grilling): rejected — templates would
  rot; every round would inherit stale config.
- **Move competency-test authoring onto JobTemplate**: rejected — would break `vacancy_tests`
  unique key and re-plumb the test engine; instead the template carries a default test that is
  cloned into the per-Vacancy `VacancyTest` at publish.

## Consequences

- Fixing a typo across open Vacancies needs an explicit re-apply, not one template edit.
- Concurrent open periods per template are allowed (no DB constraint); duplicate applications are
  prevented only by the existing `unique(candidate_id, vacancy_id)` — one application per period.
- No scheduler: a single `tenggat_lamaran` deadline + manual publish (no start-date window yet).
- JobTemplate gets its own Active/Archived lifecycle, restrict-on-delete; Vacancy keeps Draft.
