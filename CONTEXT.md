# Recruitment (Rekrutmen Azra)

Applicant tracking context: HR Admins define jobs, publish them for fixed periods, and candidates apply and move through a recruitment pipeline.

## Language

### Job posting

**JobTemplate** (Template Lowongan):
A reusable job definition that HR Admin calls up to publish a vacancy.
_Avoid_: Job posting template, Lowongan (that is the period instance).

**Vacancy** (Lowongan):
A time-boxed publication of a **JobTemplate** that candidates apply to; carries the application period (`tenggat_lamaran`), headcount, status, and an optional per-period `kualifikasi` override.
_Avoid_: Posting, Periode (it is the applyable thing, not the template).

**Publish**:
The act of creating/opening a **Vacancy** from a **JobTemplate** — snapshots the template's content and pipeline config onto the Vacancy so later template edits never alter it.

## Relationships

- A **JobTemplate** has many **Vacancies**; it holds the default content + live references to the workflow / competency-test / interview templates
- A **Vacancy** belongs to exactly one **JobTemplate**, carries the period, and freezes its own snapshot of the config at **Publish** time
- An **Application** belongs to exactly one **Vacancy** (the period instance, not the template); at most one application per `(candidate, vacancy)`
- A **JobTemplate** is Active or Archived (archived = not publishable, history kept); a **Vacancy** is Draft → Published → Closed

## Example dialogue

> **Dev:** "If HR fixes a typo in a **JobTemplate**'s description, do the open **Vacancies** update?"
> **HR Admin:** "No — a **Vacancy** keeps what it was published with. The fix only shows on the *next* **Vacancy** I publish from that template."
> **Dev:** "And a candidate who applied to last quarter's round can apply again this quarter?"
> **HR Admin:** "Yes — that's a different **Vacancy**. They just can't apply to the same one twice."

## Flagged ambiguities

- "Template" is overloaded: workflow (template alur), interview (template wawancara), test bank, and email all have templates. **JobTemplate** is a new, distinct concept — the reusable job definition.
