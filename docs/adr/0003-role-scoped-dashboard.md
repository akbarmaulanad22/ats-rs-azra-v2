# Role-scoped recruitment dashboard for all internal roles

## Status

accepted

## Context & Decision

Today only **HR Admin** sees the analytics dashboard (funnel, time-to-hire, pass/fail
rates, bottlenecks, vacancy summary). Every other internal role — **HR Manager**,
**Director**, **Unit Head**, **Employee** — lands on a "quick action" card with no data.
We extend the same analytics dashboard to all internal roles, **scoped to what each role
may see**, and retire the quick-action landing.

Scope model — two tiers, derived from the existing `VacancyPolicy`:

- **Org tier — HR Admin, HR Manager, Director.** Full org-wide dashboard, all units, the
  exact metrics HR Admin sees today, with the full filter bar (date / unit / vacancy).
  HR Manager and Director already hold org-wide `viewInterview` / `viewCandidateDetail`
  rights, so this exposes no candidate data they could not already reach. Implemented by
  widening the gate in `DashboardController` to `hasRole(HrAdmin, HrManager, Director)`.
- **Unit tier — Unit Head, Employee.** The same dashboard, **scoped to the user's own
  `employee.unit_id`**. The unit selector is removed and the user's `unit_id` is forced
  server-side (any `unit_id` in the request is ignored), so a unit-tier user can only ever
  see their own unit's numbers. Date and vacancy filters stay, but the vacancy dropdown is
  scoped to that unit's vacancies.

Scope is the user's `employee.unit_id`. A unit-tier user with **no linked employee or no
`unit_id`** gets the dashboard shell with an explicit empty state ("belum terhubung ke
unit"), achieved by scoping the metrics to a **non-existent unit id**, never `null`.

UI: the dashboard body no longer carries the quick-action card. **Profil Saya**,
**Ubah Kata Sandi**, and **Keluar** move into a header dropdown on the user name/badge,
available on every page for every role (this also gives HR Admin a password-change link it
never had).

## Why

- **Operators want visibility, even on data they can't act on.** Asked directly, the team
  chose scoped analytics over a personal task queue. The funnel/bottleneck view is a
  transparency tool, not an action list; the action lives in the pipeline screens.
- **Scope follows the policy, not a new rule.** Org tier = roles already cleared for
  org-wide candidate detail; unit tier = the unit-screening/interview roles, scoped to
  their unit exactly as `viewScreening` / `viewInterview` already scope them. No new
  authorization surface is invented.
- **Reuse over rebuild.** `GetRecruitmentMetrics` already filters every metric by
  `unit_id`. The unit-tier dashboard is the *same* action and *same* Blade with a forced
  `unit_id`, not a parallel implementation.
- **Force unit server-side.** A unit-tier user must not widen their own scope. The unit
  filter is removed from the UI *and* the `unit_id` is overridden in the controller, so a
  crafted URL cannot cross units.
- **Impossible-unit, never null.** In `GetRecruitmentMetrics` a `null` `unit_id` means *no
  filter* — i.e. org-wide data. A user with no unit must see **nothing**, so their scope is
  forced to an id that matches no row, not left null.
- **Quick-action links move, not disappear.** "Ubah Kata Sandi" / "Profil Saya" existed
  only on the retired card; relocating them to the always-present header dropdown keeps them
  reachable for every role (and fixes HR Admin, which had no password-change link).

## Considered & rejected

- **Personal task queue instead of analytics** (candidates currently awaiting *this* user's
  action): rejected by the team in favor of scoped analytics. Revisit if operators ask "what
  do I need to do" rather than "how is my unit doing".
- **Keep HR Manager / Director on the quick-action landing**: rejected — they have org-wide
  view rights and gain most from the full dashboard.
- **Give Unit Head analytics but leave Employee on the landing**: rejected — an Employee
  performs the same unit-side screening/interview as a Unit Head when `unit_id` matches, and
  there is no flag separating "screening employee" from "regular staff"; scoping by `unit_id`
  is consistent and exposes no data they can't already browse.
- **Reduced metric set per role / read-only filter bar**: rejected — extra design for no
  clear win; the filters are harmless reads and the metrics are already cheap.
- **No-unit user → org-wide or `null` scope**: rejected — leaks all-unit data to someone
  with no unit. Must resolve to an empty, unit-scoped view.
- **Drop the quick-action card with no replacement**: rejected — "Ubah Kata Sandi" and
  "Profil Saya" have no other entry point; they move to the header dropdown.

## Consequences

- **Two leak vectors are the security surface of this change and must be covered by tests:**
  1. The `vacancies` (and hidden `units`) dropdown query in `GetRecruitmentMetrics` is
     unconditional today; for unit-tier roles it must be scoped to `unit_id`, or a Unit Head
     sees every unit's vacancy titles in the dropdown.
  2. A unit-tier user passing another unit's valid `unit_id` **and** `vacancy_id` in the URL
     must still see only their own (empty) data. Foreign `vacancy_id` is harmless *only*
     because `unit_id` is always applied and yields an empty intersection — prove it with a
     test rather than assume it.
- The four `ReportingDashboardTest` cases asserting HR Manager / Director / Unit Head /
  Employee *do not* see the dashboard (the old behavior) are rewritten to assert the new
  scoped behavior; a new boundary test covers the URL-override case above.
- The header dropdown is a shared-layout change affecting every page and role, not a
  dashboard-only change.
