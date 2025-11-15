# Codex Plugin Boilerplate

A performant, extensible boilerplate for building WordPress plugins.

## Installation

1. Copy the plugin into your WordPress `wp-content/plugins` directory.
2. Activate **Codex Plugin Boilerplate** from the Plugins page.

## Usage

 - Use the shortcode `[cpb-main-entity]` to display main entities.
 - Add the **Main Entity** block in the block editor.
- Manage entities under **CPB Main Entity**, switching between **Create a Main Entity** and **Edit Main Entity** tabs.
- The creation form showcases twenty-seven demo fields (**Placeholder 1**–**Placeholder 27**) with varied inputs (text, textarea, select, radio, checkbox, color), tooltips, and an image selector that opens the media library.
- Fields share a consistent 178px width, and hovering the help icon reveals centralized, translation-ready tooltips.
- **Placeholder 14** presents generic options ("Option 1"–"Option 3") with a default "Make a Selection..." prompt.
- Configure options in **CPB Settings**, switching between **General Settings** and **Style Settings** tabs.
- Monitor scheduled tasks in **CPB Settings → Cron Jobs**, where you can review countdowns, run hooks immediately, or delete plugin-created cron events.
- Plan customer touchpoints in **CPB Communications**, beginning with the **Email Templates** tab that showcases reusable accordion layouts for future automation work.
- View plugin-generated pages or posts under **CPB Logs → Generated Content**.

## Admin Form Guidelines

- Each field is wrapped in a `.cpb-field` container with a 178px width.
- Use `.cpb-field-full` for elements that should span the full width of the form.
- Prepend every label with a `.cpb-tooltip-icon` and provide tooltip text following the pattern "Tooltip placeholder text for Placeholder X".
- Inputs (except radios, checkboxes, and color pickers) stretch to fill their container.
- Dynamic lists such as the **Items** placeholder use add/remove buttons to manage additional rows.
- The TinyMCE editor (Placeholder 27) occupies the full width and uses WordPress' built-in editor scripts.

## Development

See `AGENTS.md` for workflow instructions. Update this document when features change.
