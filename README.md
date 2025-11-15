# Thompson Engineering QCI Database

A Thompson Engineering–specific foundation for managing QCI student data, training activity, and communications inside WordPress.

## Installation

1. Copy the plugin into your WordPress `wp-content/plugins` directory.
2. Activate **Thompson Engineering QCI Database** from the Plugins page.

## Usage

 - Use the shortcode `[teqcidb-student]` to display student information on the front-end.
 - Add the **Student** block in the block editor.
- Manage entities under **Students**, switching between **Create a Student** and **Edit Students** tabs.
- The creation form showcases twenty-seven demo fields (**Placeholder 1**–**Placeholder 27**) with varied inputs (text, textarea, select, radio, checkbox, color), tooltips, and an image selector that opens the media library.
- Fields share a consistent 178px width, and hovering the help icon reveals centralized, translation-ready tooltips.
- **Placeholder 14** presents generic options ("Option 1"–"Option 3") with a default "Make a Selection..." prompt.
- Configure options in **TEQCIDB Settings**, switching between **General Settings** and **Style Settings** tabs.
- Monitor scheduled tasks in **TEQCIDB Settings → Cron Jobs**, where you can review countdowns, run hooks immediately, or delete plugin-created cron events.
- Plan customer touchpoints in **TEQCIDB Communications**, beginning with the **Email Templates** tab that showcases reusable accordion layouts for future automation work.
- View plugin-generated pages or posts under **TEQCIDB Logs → Generated Content**.
- Enable plugin PHP error logging from **TEQCIDB Settings → General Settings** when you need diagnostics. The logger records message details and stack traces for Thompson Engineering QCI Database functionality when enabled.

## Admin Form Guidelines

- Each field is wrapped in a `.teqcidb-field` container with a 178px width.
- Use `.teqcidb-field-full` for elements that should span the full width of the form.
- Prepend every label with a `.teqcidb-tooltip-icon` and provide tooltip text following the pattern "Tooltip placeholder text for Placeholder X".
- Inputs (except radios, checkboxes, and color pickers) stretch to fill their container.
- Dynamic lists such as the **Items** placeholder use add/remove buttons to manage additional rows.
- The TinyMCE editor (Placeholder 27) occupies the full width and uses WordPress' built-in editor scripts.

## Development

See `AGENTS.md` for workflow instructions. Update this document when features change.
