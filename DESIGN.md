---
name: ATS RS Azra
description: Internal HR and Applicant Tracking System for Rumah Sakit Azra
colors:
  primary: "#007774"
  primary-dark: "#005855"
  primary-light: "#009490"
  secondary: "#81BD41"
  secondary-dark: "#679932"
  page: "#f0f2f5"
  surface: "#ffffff"
  text-default: "#111827"
  text-muted: "#6b7280"
  text-faint: "#9ca3af"
  border-default: "#d1d5db"
typography:
  title:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "normal"
  body:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "normal"
  caption:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 400
    lineHeight: 1.4
    letterSpacing: "0.01em"
rounded:
  sm: "6px"
  md: "8px"
  lg: "12px"
  full: "9999px"
spacing:
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.md}"
    padding: "10px 16px"
  button-primary-hover:
    backgroundColor: "{colors.primary-dark}"
    textColor: "{colors.surface}"
    rounded: "{rounded.md}"
    padding: "10px 16px"
  input-default:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-default}"
    rounded: "{rounded.md}"
    padding: "10px 12px"
  input-focus:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-default}"
    rounded: "{rounded.md}"
    padding: "10px 12px"
  role-badge:
    backgroundColor: "rgba(0,119,116,0.10)"
    textColor: "{colors.primary}"
    rounded: "{rounded.full}"
    padding: "4px 10px"
  sidebar-nav-active:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary}"
    rounded: "{rounded.md}"
    padding: "10px 12px"
  sidebar-nav-inactive:
    backgroundColor: "transparent"
    textColor: "rgba(255,255,255,0.80)"
    rounded: "{rounded.md}"
    padding: "10px 12px"
  auth-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-default}"
    rounded: "{rounded.lg}"
    padding: "32px"
---

# Design System: ATS RS Azra

## 1. Overview

**Creative North Star: "The Steady Hand"**

ATS RS Azra is a system that acts before you ask, responds without shouting, and never makes you hunt for what you need. It carries the weight of hospital HR operations — recruitment pipelines, employee records, role access — without demanding attention for itself. The interface is warm enough that staff return to it every day without friction, precise enough that nothing is ambiguous. It earns trust by being predictable.

Instrument Sans carries the entire typographic load: one font family, differentiated by weight and size alone. There is no display typeface competing for drama. The teal sidebar is the system's backbone, a permanent anchor; everything in the content area breathes around it. Color is restrained to function: Measured Teal signals action and ownership, Vitality Lime signals status or confirmation, and nothing else competes.

This system explicitly rejects the visual language of government portals (dense tables, form-heavy gray walls, inconsistent hierarchy) and the generic SaaS template look (off-white backgrounds with rounded teal cards repeated in a grid). There is no decorative chrome. Nothing on screen exists without a job.

**Key Characteristics:**
- One humanist font, hierarchy through weight and size only
- Teal sidebar as fixed identity anchor, content area free to breathe
- Color used as signal, not decoration — two accents, both purposeful
- Shadow vocabulary is minimal: ambient and shallow, not structural
- Every interactive element has an obvious affordance; no mystery controls
- Indonesian-language interface with professional, direct copy

---

## 2. Colors: The Measured Palette

The palette is restrained with a committed primary. Measured Teal dominates the structural chrome (sidebar, active states, buttons). Vitality Lime appears in status indicators and confirmations. Everything else is neutral.

### Primary

- **Measured Teal** (`#007774` / `oklch(47% 0.080 190)`): The backbone color. Fills the sidebar, active navigation states, primary buttons, input focus rings, and interactive highlights. Used confidently at large scale; its restraint in the content area makes its presence in chrome feel deliberate, not heavy.
- **Measured Teal Dark** (`#005855` / `oklch(36% 0.072 190)`): Hover and pressed states for primary actions. Also the deeper end of the login page gradient.
- **Measured Teal Light** (`#009490` / `oklch(58% 0.088 190)`): Subtle highlights, lighter gradient stops, secondary emphasis on the primary hue.

### Secondary

- **Vitality Lime** (`#81BD41` / `oklch(73% 0.155 130)`): Reserved for positive status signals — confirmations, active badges, "present" or "approved" indicators. High chroma at medium lightness makes it legible against neutral backgrounds without overpowering.
- **Vitality Lime Dark** (`#679932` / `oklch(60% 0.145 130)`): Hover states and secondary accents for lime-colored elements.

### Neutral

- **Slate Mist** (`#f0f2f5`): Page background. Slightly blue-shifted gray keeps the canvas from feeling stark. Every content surface sits against this.
- **Surface** (`#ffffff`): Cards, sidebar active items, header. In practice, tint this to `#fafbfd` (oklch ~98.5%, chroma 0.006, hue 190) to eliminate pure white. The code uses `#ffffff`; update to the tinted value when components are rebuilt.
- **Text Default** (`#111827`): Body copy, headings, form labels. Not pure black — inherently dark enough for WCAG AA at all used sizes.
- **Text Muted** (`#6b7280`): Secondary descriptions, subtitles, placeholder context.
- **Text Faint** (`#9ca3af`): Placeholder text, disabled labels, metadata.
- **Border Default** (`#d1d5db`): Input borders at rest, dividers, table rules.

### Named Rules

**The Measured Rule.** Measured Teal belongs to structure and action. Never apply it as a decorative fill, background wash, or typographic emphasis in the content area. The sidebar earns it; a content card does not.

**The One Signal Rule.** Vitality Lime signals positive status. It does not appear on buttons, backgrounds, or decorative elements. If something is lime, it means something.

---

## 3. Typography

**Display / Body Font:** Instrument Sans (with `ui-sans-serif, system-ui, sans-serif` fallback)

**Character:** A single humanist sans-serif handles every weight class. No serif, no script, no secondary face. Hierarchy emerges entirely from scale and weight contrast — 700 against 400, 1.5rem against 0.875rem. The result is coherent and fast to scan; the absence of typographic variety forces layout and whitespace to do the hierarchy work.

### Hierarchy

- **Title** (700, 1.5rem / 24px, line-height 1.2): Page headings and modal titles. Used once per screen. The largest bold element in the content area.
- **Heading** (700, 1.125rem / 18px, line-height 1.3): Section headings, card titles, named groups. Provides structure within a page without competing with Title.
- **Label** (500, 0.875rem / 14px, line-height 1.4): Form labels, nav item text, button copy, column headers. The working weight of the interface.
- **Body** (400, 0.875rem / 14px, line-height 1.5): Descriptions, paragraph content, table cells. Caps at 65ch for multi-line reading.
- **Caption** (400, 0.75rem / 12px, line-height 1.4, letter-spacing 0.01em): Badges, metadata, timestamps, role labels.

### Named Rules

**The Weight Rule.** Only two weights are used: 700 for identity (headings, brand name) and 500 for controls (labels, buttons, nav). Body text is 400. Introducing 600 or 300 breaks the contrast that makes hierarchy legible.

---

## 4. Elevation

This system is flat by default. Surfaces are differentiated by background color (`page` vs `surface`), not by shadow stacking. Shadows appear only as ambient signals of lift — never as structural borders or dramatic depth.

### Shadow Vocabulary

- **Ambient Lift** (`box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.05)`): Used on the header bar and auth cards. Signals that an element is above the page plane. Never deepened with spread.
- **None (flat)**: Sidebar, content cards in the main area, form containers. Separated from the canvas by background color alone.

### Named Rules

**The Flat-By-Default Rule.** Shadows appear only in two contexts: the page header (always present, signals z-index) and auth cards (floating on a page with no other chrome). Adding shadow to a content card inside the main layout is prohibited — the surface background already lifts it.

---

## 5. Components

### Buttons

Gentle precision — rounded but not pill-shaped. Every button has a clear, obvious function. No ghost buttons for primary actions.

- **Shape:** Gently rounded edges (8px / `rounded-lg`)
- **Primary:** Measured Teal fill (`#007774`), white label (0.875rem, 500), padding 10px 16px. Full-width inside forms.
- **Hover / Focus:** Transitions to Measured Teal Dark (`#005855`) in 150ms ease-out. Focus ring: 2px solid `#007774`, offset 2px.
- **Destructive / Secondary:** Not yet defined in the system. When needed: ghost with border-default border, text-default color. No red fills.

### Inputs / Fields

- **Style:** `surface` background, `border-default` border (1px), 8px radius. Padding 10px 12px. Label sits above at label weight (500, 0.875rem).
- **Focus:** Border transitions to `primary` (`#007774`); ring `rgba(0,119,116,0.2)` offset 0. No color change on the background.
- **Error:** Border to `#dc2626`; error message below in caption size (0.75rem), red.
- **Disabled:** Opacity 0.5, cursor not-allowed. No background change.

### Navigation (Sidebar)

The sidebar is the permanent identity anchor. Measured Teal background, white logo text. Nav items use the full sidebar width.

- **Active:** White background, Measured Teal text, 8px radius. The inverted colors signal "you are here" without any additional indicator.
- **Inactive:** `rgba(255,255,255,0.80)` text, transparent background. Hover: `rgba(255,255,255,0.10)` background wash.
- **Structure:** Icon (20px, stroke 1.75) + text label side by side. Icon and text never separate; both are part of the click target.

### Role Badge (Chip)

- **Style:** `rgba(0,119,116,0.10)` background, Measured Teal text, full pill radius (9999px), padding 4px 10px, caption size (0.75rem, 400).
- **Appears in:** Header user block, dashboard user context. One per user session; not repeated in content rows.

### Auth Card

The only "card" component in the system. Appears on the right panel of login and change-password screens, centered in the page-colored panel.

- **Corner Style:** Generous radius (12px / `rounded-xl`)
- **Background:** Surface white
- **Shadow:** Ambient Lift (single shadow, not layered)
- **Internal Padding:** 32px (`p-8`)
- **No border.** The shadow is the separation.

### Header

Fixed at top, above the sidebar, full width. Slides right when sidebar is open (64px wider than content area).

- **Background:** Surface white, Ambient Lift shadow below.
- **Height:** 64px (fixed)
- **Left:** Hamburger toggle (20px icon, gray-500 at rest, gray-700 hover)
- **Right:** User name + role badge + logout link (text-muted, no button styling)

---

## 6. Do's and Don'ts

### Do:

- **Do** use Measured Teal (`#007774`) only for structure and action: sidebar, active nav, primary buttons, input focus. Everywhere else, neutrals.
- **Do** use Vitality Lime (`#81BD41`) only as a status signal — positive states, confirmations, presence indicators. Never on buttons.
- **Do** keep the page background as Slate Mist (`#f0f2f5`). Content surfaces are white; the contrast between them is the only depth signal needed in the content area.
- **Do** apply `shadow-sm` only to the header and floating auth card. Content panels inside the main layout: flat, no shadow.
- **Do** use 700 for headings, 500 for labels and controls, 400 for body and descriptions. No other weights.
- **Do** write Indonesian copy that is direct and short. "Masuk" not "Silakan Masuk ke Akun Anda". The interface knows what you mean.
- **Do** ensure all text meets WCAG AA contrast (4.5:1 for body, 3:1 for large text) against its background. Test Measured Teal text on `rgba(0,119,116,0.10)` badge backgrounds specifically.

### Don't:

- **Don't** use the government portal aesthetic: dense form walls, table-heavy gray screens, labels in ALL CAPS, inconsistent padding between sections. Any screen that looks like a 2005 Indonesian government form is a regression.
- **Don't** use the generic SaaS template look: off-white background with rounded teal cards repeated in a grid, hero stat numbers, identical icon-heading-body card grids. If a competitor's product could have the same screen, it's wrong.
- **Don't** use `border-left` greater than 1px as a colored accent stripe on cards, list items, or callouts. Prohibited. Rewrite with background tint, full border, or nothing.
- **Don't** use gradient text (`background-clip: text`). Any text that needs emphasis gets it through weight (700) or size, never gradient fill.
- **Don't** add a second typeface. Instrument Sans handles every weight class. Additional fonts break the system's coherence.
- **Don't** stack shadows. One ambient shadow or none. `shadow-md` / `shadow-lg` / `shadow-xl` are outside the vocabulary.
- **Don't** use pure white (`#ffffff`) for surface backgrounds in new components. Tint to `#fafbfd` to eliminate the stark contrast with the slightly tinted page background.
- **Don't** add animation to layout properties (width, height, margin, padding, display). Transition only opacity and transform. The sidebar toggle transitions `width`; that is an existing constraint, not a precedent.
