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
- Manage quiz records under **Quizzes**, switching between **Create a Quiz** and **Edit Quizzes** tabs. The create form saves quiz records using a quiz name plus class-association checkboxes, while the edit tab renders accordion rows for each saved quiz so admins can update quiz names/class checkbox associations and review saved quiz questions. Question rows now display titles like `Question #1 (Multiple Choice)` plus a full-width prompt textarea bound to `teqcidb_quiz_questions.prompt`. For `true_false` questions, the edit UI now includes a True/False answer select bound to `teqcidb_quiz_questions.choices_json` using the format `[{"correct":"true"}]` or `[{"correct":"false"}]`. Quiz question rows on Edit Quizzes now render as nested accordions (to keep large quizzes manageable), showing each question title plus the full prompt text preview until expanded. For `multi_select` and `multiple_choice` questions, each saved option renders as an answer row with an option textarea plus a True/False correctness select, and admins can add additional answer rows before saving. Multiple-choice option rows auto-enforce a single `True` selection in the editor and server-side validation requires exactly one correct option at save time. Per-question Save Changes persists prompt/choices_json/updated_at through AJAX for true/false, multi-select, and multiple-choice question types, and Delete now removes individual questions via AJAX, then reloads and re-opens the same quiz accordion in Edit Quizzes. The Add Quiz Question button now opens an inline new-question builder (question text + type selector), and selecting a type renders the matching true/false or option-row configuration UI before creation. Selected class IDs are stored as a comma-separated value in `teqcidb_quizzes.class_id` (for example: `41,32,1,19`).
- Activation now also provisions foundational quiz tables (`teqcidb_quizzes`, `teqcidb_quiz_questions`, `teqcidb_quiz_attempts`, `teqcidb_quiz_answers`) for upcoming high-concurrency class quiz workflows.
- Class-route feedback now reflects per-user quiz-attempt status (`2 = in progress`, `1 = failed`, `0 = passed`) with resume timing, pass guidance, and fail contact messaging.
- Class routes now render a full quiz runtime for logged-in students when a quiz is mapped to the class: one-question-at-a-time answering with required selections, a live `Question X of Y` indicator, horizontal completion progress bar, debounced autosave plus `beforeunload` beacon persistence, resume-from-last-save behavior, and final submission scoring/persistence through `teqcidb_quiz_attempts` + `teqcidb_quiz_answers` (pass thresholds: Initial = 75%, Refresher = 80%).
- Class-page student-facing exam/quiz terminology now follows class type: **Initial** classes display **QCI Exam** language with the 75% pass requirement messaging, while **Refresher** classes display **Refresher Quiz** language with the 80% pass requirement messaging.
- Class-page quiz runtime loading now honors per-class quiz access controls: when `allallowedquiz` is `blocked`, quiz content remains hidden unless the logged-in user's WordPress ID appears in that class's `quizstudentsallowed` list.
- Class-page blocked-access messaging is now class-type specific (`Your instructor has not enabled this Exam yet!` for Initial classes and `Your instructor has not enabled this Refresher Quiz yet!` for Refresher classes), and in-progress feedback now omits elapsed-save timing details.
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
