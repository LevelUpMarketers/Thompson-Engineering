# Thompson Engineering QCI Database

A Thompson Engineering–specific foundation for managing QCI student data, training activity, and communications inside WordPress.

## Installation

1. Copy the plugin into your WordPress `wp-content/plugins` directory.
2. Activate **Thompson Engineering QCI Database** from the Plugins page.

> The repository now commits the Composer-generated `vendor/` directory so direct WordPress ZIP installs work on servers that do not run Composer.

## Usage

 - Use the shortcode `[teqcidb-student]` to display student information on the front-end.
 - Use the shortcode `[teqcidb_student_dashboard_shortcode]` to render the student dashboard login/create-account section.
 - Use the shortcode `[teqcidb_student_registration_shortcode]` to show student class registration content and reuse the login/create-account experience for logged-out visitors.
 - Add the **Student** block in the block editor.
- Manage entities under **Students**, switching between **Create a Student** and **Edit Students** tabs.
- Manage classes under **Classes**, including storing each session's **Teams Link**, auto-generating a dedicated lightweight **Class URL** route on class creation, and maintaining **Class Resources** rows (name, type, and URL) saved as structured JSON per class. Class routes now require login, render a dedicated login-only form (without account-creation UI) for logged-out users, and load a class-route-specific stylesheet for quiz/resources/login layouts across desktop and mobile viewports.
- Manage quiz records under **Quizzes**, switching between **Create a Quiz** and **Edit Quizzes** tabs. The create form saves quiz records using a quiz name plus class-association checkboxes, while the edit tab renders accordion rows for each saved quiz so admins can update quiz names/class checkbox associations and now see a Quiz Questions section with saved-question counts, an empty-state message, and an Add Quiz Question action. Selected class IDs are stored as a comma-separated value in `teqcidb_quizzes.class_id` (for example: `41,32,1,19`).
- Activation now also provisions foundational quiz tables (`teqcidb_quizzes`, `teqcidb_quiz_questions`, `teqcidb_quiz_attempts`, `teqcidb_quiz_answers`) for upcoming high-concurrency class quiz workflows.
- Class-route feedback now reflects per-user quiz-attempt status (`2 = in progress`, `1 = failed`, `0 = passed`) with resume timing, pass guidance, and fail contact messaging.
- The creation form showcases twenty-seven demo fields (**Placeholder 1**–**Placeholder 27**) with varied inputs (text, textarea, select, radio, checkbox, color), tooltips, and an image selector that opens the media library.
- Fields share a consistent 178px width, and hovering the help icon reveals centralized, translation-ready tooltips.
- **Placeholder 14** presents generic options ("Option 1"–"Option 3") with a default "Make a Selection..." prompt.
- Configure options in **TEQCIDB Settings**, switching between **General Settings** and **Style Settings** tabs.
- Monitor scheduled tasks in **TEQCIDB Settings → Cron Jobs**, where you can review countdowns, run hooks immediately, or delete plugin-created cron events.
- Plan customer touchpoints in **TEQCIDB Communications**, beginning with the **Email Templates** tab that showcases reusable accordion layouts for future automation work.
- View plugin-generated pages or posts under **TEQCIDB Logs → Generated Content**.
- Enable plugin PHP error logging from **TEQCIDB Settings → General Settings** when you need diagnostics. The logger records message details and stack traces for Thompson Engineering QCI Database functionality when enabled.
- Configure Authorize.Net credentials in **TEQCIDB Settings → API Settings → Payment Gateway** (Environment, Login ID, Transaction Key, and Client Key). These values are saved in the `teqcidb_api_settings` option and are now used by each class accordion's **Register & Pay Online** action to request an Accept Hosted token and load the embedded checkout iframe. The embedded communicator now uses the public path `/teqcidb-authorize-communicator/` instead of an admin-ajax URL to better align with host CSP policies. Each class panel also includes a **Print & Email Your Registration Form** button that opens the latest QCI registration PDF in a new tab for offline completion. Each class panel now also shows a policy/instructions text block above the registration action buttons with cancellation terms, completion notes, and contact/mailing guidance. After a successful payment, the iframe now fades out, the class panel collapses smoothly, and the feedback area shows transaction details with a receipt-download link that generates a branded PDF copy of the transaction. Successful registration payments are also recorded in the `teqcidb_paymenthistory` table with user/class/payment/transaction metadata for reporting. Those successful payments now also create matching `teqcidb_studenthistory` records (registered/payment status, amount paid, enrollment date, and class linkage) for downstream student timeline views.

## Admin Form Guidelines

- Each field is wrapped in a `.teqcidb-field` container with a 178px width.
- Use `.teqcidb-field-full` for elements that should span the full width of the form.
- Prepend every label with a `.teqcidb-tooltip-icon` and provide tooltip text following the pattern "Tooltip placeholder text for Placeholder X".
- Inputs (except radios, checkboxes, and color pickers) stretch to fill their container.
- Dynamic lists such as the **Items** placeholder use add/remove buttons to manage additional rows.
- The TinyMCE editor (Placeholder 27) occupies the full width and uses WordPress' built-in editor scripts.

## Development

See `AGENTS.md` for workflow instructions. Update this document when features change.
