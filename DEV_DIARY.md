# Development Diary

1. 2025-08-11: Initial commit with plugin boilerplate structure, documentation, and placeholder features.
2. 2025-08-12: Added content logging table, logger class, and admin tab for generated pages/posts.
3. 2025-08-12: Converted Student admin page to use tabs for creating and editing entries.
4. 2025-08-12: Moved top message center beneath navigation tabs on admin pages.
5. 2025-08-12: Split Settings into General and Style tabs and promoted Settings and Logs to top-level menus.
6. 2025-08-12: Expanded Student schema with placeholder fields and added responsive, tooltip-enabled form layout.
7. 2025-08-12: Replaced demo fields with twenty Placeholder inputs, varied types, image selector, and synchronized database schema.
8. 2025-08-12: Standardized field widths, implemented centralized hover tooltips, and added default options for Placeholder 14.
9. 2025-08-12: Added custom admin font, ensured all dropdowns default to "Make a Selection...", and widened the image selector button.
10. 2025-08-12: Swapped in Roobert admin font, restored dashicon tooltips, and added textarea, radio, checkbox, and color placeholders.
11. 2025-08-12: Removed fixed color picker width, replaced tooltips with placeholder text, and integrated TinyMCE editor for Placeholder 21.
12. 2025-08-12: Added opt-in preference fieldset, dynamic Items list, full-width Placeholder 25 editor, and fixed color picker width.
13. 2025-08-12: Tweaked placeholder widths, refined tooltip styling and layering, added per-option tooltips for Placeholder 22, and cleaned up opt-in markup.
14. 2025-08-12: Restored color picker as Placeholder 25, introduced separate Add Media button and TinyMCE editor as Placeholders 26 and 27, and updated scripts for new placeholders.
15. 2025-08-12: Removed Placeholder 20, shifted subsequent placeholders, updated tooltips and scripts, and adjusted schema accordingly.
16. 2025-08-12: Renamed initial field to Placeholder 1, shifted labels through Placeholder 27, and fixed the color picker width.
17. 2025-08-12: Genericized opt-in option labels, documented form layout guidelines, and redesigned top message with video, premium pitch, and logo.
18. 2025-08-12: Refactored top banner into two-column layout, moved upgrade button beneath text, and added centered logo row with contact links.
19. 2025-08-12: Replaced bottom message with logo row variant and added digital marketing section class.
20. 2025-08-12: Removed top logo row, added thank-you tagline to bottom message, and cleaned up unused premium logo styles.
21. 2025-08-12: Reintroduced logos, added US states and territories placeholder, and refreshed styles and scripts.
22. 2025-08-12: Wrapped "SO MUCH" in thank-you message with stylable span and added bold, italic styling.
23. 2025-08-12: Added Cron Jobs tab with automatic teqcidb_ hook discovery, manual run/delete controls, countdowns, and demo event.
24. 2025-08-12: Documented translation coverage expectations and cron tooltip description requirements for future work.
25. 2025-08-12: Enabled AJAX spinner transitions by toggling WordPress's is-active class to show progress without shifting the layout.
26. 2025-08-12: Wrapped spinner and feedback in a fixed-height container, added inline fade transitions, and surfaced a generic error message when AJAX requests fail.
27. 2025-08-12: Moved the feedback container beside form submit buttons, keeping the spinner and status text inline without triggering layout shifts on save.
28. 2025-08-12: Centered inline feedback controls with submit buttons and overlapped spinner fade-outs with status fade-ins for smoother confirmation cues.
29. 2025-08-12: Documented the inline spinner-and-message layout as the standard pattern for all admin feedback areas.
30. 2025-08-12: Added a Communications menu with an Email Templates accordion demo and placeholder notices for upcoming tabs.
31. 2025-08-12: Realigned Communications accordion metadata with equal-width columns and wrapped values for consistent headers.
32. 2025-08-12: Kept accordion metadata items inline with evenly distributed widths while allowing long values to wrap cleanly.
33. 2025-08-12: Fixed accordion metadata labels to sit with their values, added a 100px title column, and removed gaps that split label/value pairs.
34. 2025-08-12: Converted communications metadata rows to a responsive grid so columns align while labels hug their values without extra spacing.
35. 2025-08-12: Rebuilt the Communications email accordion into a table-based layout with aligned columns and row toggles that mirror WordPress list tables.
36. 2025-08-12: Lightened the first communications template header and added visual separators between accordion rows for improved scanning.
37. 2025-08-12: Removed the communications row focus outline and allowed accordion groups to overflow so tooltips remain fully visible.
38. 2025-08-12: Enlarged tooltip text styling and standardized a reusable title-and-description intro across every admin tab.
39. 2025-08-12: Trimmed tooltip sizing, reworked the demo cron seeding to keep a single six-month sample, and restyled cron tab pagination so it clears the bottom message banner.
40. 2025-08-12: Widened tooltip popovers and enforced equal-width cron action buttons for consistent control layouts.
41. 2025-08-12: Increased tooltip popover width by seventy percent to improve readability of longer descriptions.
42. 2025-08-12: Raised tooltip text size to 17px and enforced a 300px minimum width for clearer popup readability.
43. 2025-11-05: Rebuilt the Student edit tab with the communications accordion table, added paginated AJAX loading of records, and localized supporting scripts.
44. 2025-11-05: Streamlined the Student edit table by loading records immediately with alphabetical sorting, added the non-interactive edit cue, and centralized placeholder labels for future renames.
45. 2025-11-05: Removed the enforced AJAX delay from Student reads so the edit tab populates instantly on load.
46. 2025-11-05: Embedded the creation form inside each Student accordion, localized field metadata for client-side rendering, and wired AJAX save/delete actions with inline feedback and pagination refreshes.
47. 2025-11-05: Re-ran the inline Student editor deployment with refreshed feedback styling and corrected placeholder sanitization for saved values.
48. 2025-11-05: Hardened Student AJAX saving with normalized sanitization for date, time, and select fields plus explicit database error handling.
49. 2025-11-05: Synced the Student schema and AJAX handlers to persist all placeholders, state dropdowns, opt-ins, item lists, media, and editor content while mirroring the create form's TinyMCE setup.
50. 2025-11-05: Top-aligned Student accordion summary cells so row heights stay consistent when toggling inline editors.
51. 2025-11-05: Added a 50px minimum height to Student accordion summary cells to eliminate row shifts when toggling panels.
52. 2025-11-05: Evened accordion header column widths and mirrored the action-cell treatment on Communications templates for a consistent layout across tabs.
53. 2025-11-05: Built the Welcome Aboard template editor with subject, body, SMS fields, and token buttons sourced from Student placeholders.
54. 2025-11-05: Added a live Welcome Aboard email preview fed by the first Student record with blur-based updates and styled it alongside the existing template controls.
55. 2025-11-05: Added Save Template controls that persist Welcome Aboard subject, body, and SMS text via AJAX with inline spinner feedback and prefilled fields.
56. 2025-11-05: Enabled Welcome Aboard test emails with inline validation, shared preview helpers, and spinner-backed messaging.
57. 2025-11-05: Added configurable From name and email fields with sensible defaults, persisted them with template saves, and applied the values to test email headers.
58. 2025-11-05: Standardized email template buttons to a 165px minimum width and let token labels wrap so token grids stay aligned when text breaks.
59. 2025-11-05: Restyled the Email Templates accordion shells to mirror Student cards with padded headers, rounded borders, and coordinated open-state shadows.
60. 2025-11-05: Reverted the email template accordion styling to the baseline list-table treatment so it matches the proven Student appearance.
61. 2025-11-05: Scoped email template header cells to remove flex alignment and enforce a 50px row height without affecting other accordion tabs.
62. 2025-11-05: Cleared the email template action cell width constraints so the tab inherits the default table alignment.
63. 2025-11-05: Built the Email Logs tab with file-backed delivery history, styled entry cards, and clear/download controls wired to AJAX and admin-post handlers.
64. 2025-11-10: Introduced the API Settings tab with accordion-styled credential forms, reveal toggles, and inline feedback controls mirroring other admin sections.
65. 2025-11-10: Added a Category column to the API Settings accordion headers and styled the cells to accommodate service group labels.
66. 2025-11-10: Scoped the API Settings accordion headers to use table-cell alignment, enforced a 50px row height, and right-aligned the action heading for consistent spacing.
67. 2025-11-10: Wired API Settings forms to AJAX persistence with per-integration sanitization, inline feedback reuse, and saved credential prefills.
68. 2025-11-10: Added an SMS Service accordion with generic messaging credentials and select-driven environments alongside existing API settings.

69. 2025-11-10: Introduced error logging with sitewide and TEQCIDB-specific tabs, AJAX clear/download controls, and global handlers that track current and future plugin features.
70. 2025-11-10: Eliminated deprecated sleep warnings, extended the log helper for payment scopes, and added Payment Logs with clear/download tools; future payment integrations should capture full transaction context (names, contact info, purchase details, WordPress user data, allowed card fragments) while excluding sensitive card numbers.
71. 2025-11-10: Normalized placeholder label and value sanitization so apostrophes, dashes, and other legitimate characters save and render consistently across Student forms and related tooling.
72. 2025-11-10: Added a Student search dashboard with placeholder filters, inline spinner feedback, and paginated AJAX reads that honor active criteria.
73. 2025-11-10: Added a Clear Search control to reset Student filters and reload the default paginated results.
74. 2025-11-10: Added general settings toggles for all logging channels, persisted preferences via AJAX, and gated email/error log writers behind the new helper so disabled logs stop recording immediately.
75. 2025-11-10: Surfaced logging status indicators across communications and log tabs with styled settings links so admins can confirm which channels are currently recording entries.
76. 2025-11-10: Moved the payment log status indicator inside the log section so its layout matches the error log scopes while keeping other tabs unchanged.
77. 2025-11-11: Normalized error logger keyword matching to stringify stack traces and messages so array data no longer triggers PHP type errors during log writes.
78. 2025-11-11: Hardened error log helper formatting by stringifying complex values before sanitization to prevent array-to-string warnings when writing entries.
79. 2025-11-12: Rebranded the boilerplate to Thompson Engineering QCI Database, renamed CPB assets to teqcidb, and retargeted student tooling with updated table names, text domains, and plugin metadata.
80. 2025-11-12: Deferred text-domain bootstrapping to `plugins_loaded` and added recursion guards around log writes to prevent memory exhaustion when logging encounters filesystem warnings.
81. 2025-11-12: Bootstrapped plugin instantiation on `plugins_loaded` so localization and error logging initialize once WordPress is ready before other components register hooks.
82. 2025-11-12: Wrapped error handler entry points in a logging guard so translation lookups can no longer re-enter the logger and exhaust memory during activation.
83. 2025-11-12: Temporarily disabled communications, logging, settings, and cron bootstraps so only core Student admin/AJAX/shortcode features load while we isolate the activation memory exhaustion.
84. 2025-11-12: Re-enabled the content logger and cron manager to continue narrowing the activation culprit while leaving other subsystems offline.
85. 2025-11-12: Restored the Communications and Settings admin tabs with their AJAX and admin-post handlers so we can continue staged feature reactivation.
86. 2025-11-12: Re-enabled the Logs admin area, reinstated error log AJAX endpoints, and booted the error logger alongside other subsystems for the next activation test.
87. 2025-11-12: Added a per-request cap to error logging and disabled sitewide logging by default so third-party notices cannot exhaust memory before the settings page loads.
88. 2025-11-12: Disabled plugin error logging by default and gated the logger behind the settings toggles so we can test without the subsystem until the memory leak is isolated.
89. 2025-11-12: Cached logging toggles per request, skipped stack traces when only sitewide logging is active, and bypassed repeated option lookups during log writes to prevent runaway memory usage when PHP notices fire.
90. 2025-11-12: Removed sitewide PHP logging toggles, helpers, and UI so the error logger now focuses solely on Thompson Engineering QCI Database events.
91. 2025-11-12: Replaced the student activation schema with production fields for WordPress linkage, contact info, training dates, and metadata columns.
92. 2025-11-12: Rebuilt the Create a Student tab to mirror the production schema with named fields, contextual tooltips, and inputs sized for addresses, lists, and notes.
93. 2025-11-12: Split the student address into discrete inputs, expanded representative contact fields, and replaced association selection with dedicated checkboxes.
94. 2025-11-12: Simplified the Create a Student form by assuming US addresses and hiding representative lookup IDs so admins focus on contact info that drives later automation.
95. 2025-11-12: Matched address line widths to other location inputs, converted State to a US states-and-territories dropdown, renamed Zip labeling, and emphasized the representative toggle copy.
96. 2025-11-12: Removed markup from the representative toggle label and aligned the Previous Companies repeater inputs with the other full-width text fields for consistent sizing.
97. 2025-11-12: Narrowed the Previous Companies repeater to the standard field width by dropping its full-width class and custom CSS override.
98. 2025-11-12: Rewired Student saving to the production schema, persisting the new contact, address, and association fields with updated AJAX handlers and labels.
99. 2025-11-19: Enforced WordPress user creation for new students, prevented duplicate student emails, and aligned unique student IDs with the legacy email-plus-timestamp format.
100. 2025-11-19: Added live phone input masking, normalized stored phone numbers (including fax and representative contacts), and switched the student address JSON to a `zip_code` key for the postal value.
101. 2025-11-19: Removed the tutorial/promo banner markup and related styles from every admin tab so the UI stays focused on core plugin tools.
102. 2025-11-19: Added a legacy Upload tab to import old teqcidb_students rows, converting legacy fields into the new schema while guarding against missing data and duplicates.
103. 2025-11-19: Adjusted legacy Upload comment handling to leave new records blank when the original comment is empty instead of copying legacy notes.
104. 2025-11-19: Stopped appending legacy billing, image, and flag notes to imported comments so uploads preserve only the original comment text.
105. 2025-11-19: Added legacy Upload representative lookups to attach matching WordPress user IDs and unique student IDs when alternate contact emails already exist.
106. 2025-11-20: Fixed the Student editor to render Associations as checkboxes with saved selections, converted Admin Comments to a textarea, blanked item placeholders for Previous Companies, and renamed the expiration date label for consistency.
107. 2025-11-20: Rebranded the admin menu entry to "QCI Database," set a custom icon from plugin assets, and moved it directly beneath Posts in the dashboard navigation.
108. 2025-11-20: Removed the committed dashboard icon binary so the menu still references the asset path while allowing the file to be added manually outside version control.
109. 2025-11-20: Ignored the optional dashboard icon PNG so branch updates no longer choke on binary assets that will be supplied outside version control.
110. 2025-11-20: Renamed the primary submenu entry to "Students" so the QCI Database menu keeps its new branding while the student list remains easy to spot.
111. 2025-11-20: Force-set the QCI Database submenu root label to "Students" to override WordPress defaults when building the dashboard navigation.
112. 2025-11-20: Added activation-time creation of the teqcidb_classes table to store class metadata and enrollment counts.
113. 2025-11-20: Expanded the upload tab with selectable legacy record types and backend support for importing legacy class rows alongside students.
114. 2025-11-29: Added student allow/deny list columns to the initial classes table schema for course and quiz access controls.
115. 2025-11-29: Added a Classes admin submenu with a Create tab UI that mirrors the student form and a placeholder edit/manage tab.
116. 2025-11-29: Refined the Classes Create form by removing generated-only fields, splitting the address into discrete inputs, updating course/quiz access selectors, and adjusting schema types for those toggles.
117. 2025-11-29: Renamed class access columns to match new allow-all wording, updated the Create Class form labels to clearer questions, and retained explicit course/quiz restriction lists in the schema and UI.
118. 2025-11-29: Reworked class access controls with global allow toggles plus per-student allow and restrict lists for courses and quizzes.
119. 2025-11-29: Reordered Create Class fields so Hide this Class follows cost and instructors sit at the end of the form.
120. 2025-11-29: Added student autocomplete lookups for class access allow/restrict lists with a new AJAX endpoint to keep searches performant on large datasets.
121. 2025-11-29: Simplified class access autocomplete selections to show only names/emails while storing hidden WordPress and unique IDs for each chosen student.
122. 2025-11-29: Bound student autocomplete directly on newly added class access rows so dynamic fields offer lookup suggestions immediately.
123. 2025-11-29: Wired the Create Class form to save via AJAX, generating unique class IDs, persisting address/access fields, and storing selected student allow/restrict lists with their hidden IDs.
124. 2025-11-29: Moved the Class Description field below the quiz allow selector on the Create Class form to match the desired layout.
125. 2025-11-29: Updated class access allow/restrict list storage to use wpuserid/uniquestudentid keys and affirmed the sanitation path keeps special characters user-friendly when saving.
126. 2025-11-30: Built the Classes edit tab UI with search, accordion pagination, and read-only forms populated from saved class records while leaving editing functionality for a later update.
127. 2025-11-30: Corrected class edit UI to select saved states, surface student labels in allow/restrict lists, and add repeater buttons for all itemized fields including instructors.
128. 2025-11-30: Enabled full class editing with populated accordions, student autocomplete retention for allow/restrict lists, and AJAX saves that update existing class records.
129. 2025-11-30: Normalized legacy class uploads by mapping format labels and splitting comma-prefixed instructors so imported records display cleanly in the editor.
130. 2025-12-05: Added activation-time creation of the teqcidb_studenthistory table to track class registrations, attendance, and course/quiz progress flags.
131. 2025-12-05: Added legacy student history uploads with mapping for statuses, payments, and enrollment into the new teqcidb_studenthistory table and surfaced a Settings checkbox for the new import type.
132. 2025-12-05: Enabled multi-record legacy uploads for classes, students, and student history entries with per-row skip tracking and summary messaging.
133. 2025-12-05: Filled missing WordPress user IDs during legacy student history imports by deriving emails from unique student IDs and matching existing accounts.
134. 2025-12-05: Allowed legacy uploads with empty date/time fields to insert cleanly by normalizing those values to null instead of empty strings.
135. 2025-12-05: Removed duplicate detection for legacy student history uploads so admins can reinsert matching rows when needed.
136. 2025-12-05: Added an adminapproved column to the student history table schema with a default Pending Approval status.
137. 2025-12-05: Set adminapproved to default to null in the student history schema and mapped legacy student history uploads to import existing admin approval values.
138. 2025-12-05: Added CSV/text legacy upload support with higher execution limits so thousands of student history rows (and future bulk imports) can be processed from the Upload tab.
139. 2025-12-05: Surfaced per-row skipped reasons for legacy uploads and displayed the full skip list inline on the Upload tab feedback area.
140. 2025-12-05: Switched legacy upload guidance to use .sql/text exports containing parenthesized rows so bulk student history imports match the expected file format.
141. 2025-12-05: Fixed legacy upload parsing to handle lines with inner parentheses and trailing commas so .sql student history rows no longer get skipped for missing column counts.
142. 2025-12-05: Trimmed newline-split legacy upload rows to remove trailing commas/whitespace so large .sql student datasets import cleanly alongside history records.
143. 2025-12-05: Relaxed legacy student uploads to auto-generate unique placeholder emails when missing or duplicated so bulk imports continue without validation skips.
144. 2026-01-07: Displayed student history entries beneath edit-student comment fields so admins can review and edit related history details inline.
145. 2026-01-07: Refined student history edit fields with class dropdowns and updated status selections for registration, attendance, and outcomes.
146. 2026-01-07: Added admin approval dropdown options and default selection placeholders for student history selects.
147. 2026-01-07: Updated student history labels, status dropdowns, and removed the registered-by field.
148. 2026-01-07: Normalized student history select matching to handle lowercase saved values like "no".
149. 2026-01-07: Added read-only class date and class type fields to student history entries.
150. 2026-01-07: Added per-entry save/delete actions for student history with stateful reloads.
151. 2026-01-07: Smoothed student history reload UX with action spacing and restore animations.
152. 2026-01-07: Added inline history entry creation UI and strengthened reload highlight animations.
153. 2026-01-07: Tuned highlight timing for newly added history entry animations.
154. 2026-01-07: Added new history entry creation flow and auto-filled class metadata on selection.
155. 2026-01-07: Enabled new history entries to save and updated the new-entry notice text.
156. 2026-01-07: Added duplicate history grouping, class metadata autofill, and currency formatting for amounts.
157. 2026-01-07: Standardized the student name column to use labeled meta text in the edit accordion.
158. 2026-01-07: Updated main student save to persist history entries and normalized empty meta display.
159. 2026-01-07: Added the student dashboard template with responsive login and create account forms for unauthenticated visitors.
160. 2026-01-07: Moved the student dashboard auth layout into a dedicated shortcode with matching frontend styling.
161. 2026-01-07: Updated the student dashboard shortcode copy and added password creation fields with visibility toggles.
162. 2026-01-07: Switched the student dashboard password toggles to inline dashicon controls.
163. 2026-01-07: Added a dedicated student dashboard form feedback section with an inline spinner placeholder.
164. 2026-01-07: Added client-side validation and conditional feedback display for student dashboard account creation.
165. 2026-01-07: Wired the student dashboard create form to submit through the same student save flow as the admin form.
166. 2026-01-07: Updated student dashboard auth section styling and reset link behavior.
167. 2026-01-07: Aligned student dashboard state selection values with backend validation so states save correctly.
168. 2026-01-07: Updated the student dashboard create-account description copy.
169. 2026-01-07: Added front-end phone masking and improved duplicate email messaging for dashboard signups.
170. 2026-01-07: Added login and post-signup auto-login flows for the student dashboard forms.
171. 2026-01-07: Scoped dashboard auth reload behavior to the dashboard shortcode wrapper.
172. 2026-01-07: Updated the frontend spinner brand color for student dashboard feedback.
173. 2026-01-24: Added the logged-in student dashboard tab layout with representative-only navigation and responsive styling.
174. 2026-01-24: Updated active and hover tab colors for the student dashboard navigation.
175. 2026-01-24: Built the profile info tab layout with read-only fields and edit/save controls.
176. 2026-01-24: Added affiliated association checkboxes to the profile info tab.
177. 2026-01-24: Prevented admin-created students from auto-login and enriched representative metadata with WP and student IDs.
178. 2026-01-24: Added profile editing save flow with validation and cancel support on the student dashboard.
179. 2026-01-24: Fixed profile zip code population and enabled phone masking on profile edits.
180. 2026-01-24: Tweaked mobile dashboard tab behavior, save button sizing, and smooth scroll on tab selection.
181. 2026-01-24: Added a student dashboard Class History tab layout populated from student history records for logged-in users.
182. 2026-01-24: Updated student dashboard tab headings and class history description copy with new styling.
183. 2026-01-24: Refined class history registered-by labels to show self-registration or registrar contact details.
184. 2026-01-24: Fixed formatting in the student dashboard class history registrar helper.
185. 2026-01-24: Updated class history labels and self-registration handling for null registrars.
186. 2026-01-24: Added profile info tab description copy for the student dashboard.
187. 2026-01-24: Added student dashboard deep-link handling for tab query parameters.
188. 2026-01-24: Added editable old company entries to the student profile dashboard tab when available.
189. 2026-01-24: Adjusted profile fieldset spacing and label visibility for previous companies and associations.
190. 2026-01-24: Captured original company values to append to old companies when students change their company.
191. 2026-01-24: Added editable previous company rows with an add button and empty-state message.
192. 2026-01-24: Added a personalized welcome line with QCI number to the student dashboard.
193. 2026-01-24: Added spacing and mobile sizing for the previous companies add button.
194. 2026-01-24: Added a portable countdown display for student QCI expiration dates in the certificates tab.
195. 2026-01-24: Added weeks support to the student dashboard expiration countdown.
196. 2026-01-24: Added wallet card PDF generation and actions to the certificates tab.
197. 2026-02-12: Refined wallet card PDF layout spacing, centered contact/update blocks, and preserved logo aspect ratios to match legacy styling.
198. 2026-02-12: Tightened wallet card PDF spacing, lifted front text, and repositioned the back logo below centered bullet content.
199. 2026-02-12: Lifted wallet card front text blocks to tighten spacing beneath the top logo.
200. 2026-02-13: Added the student dashboard Your Students search accordion with detailed student and history displays.
201. 2026-02-13: Hid the student search results until a search runs and reduced summary columns to name, email, and company.
202. 2026-02-13: Removed summary meta labels from the Your Students search result rows.
203. 2026-02-13: Removed the Details column from the Your Students search results.
204. 2026-02-13: Adjusted student search results to auto-fit expanded accordion content.
205. 2026-02-13: Added an "Add This Student" action to assign representatives from the dashboard search results.
206. 2026-02-13: Added a dashboard section listing students assigned to the current representative.
207. 2026-02-13: Added editable assigned-student details with save actions in the dashboard.
208. 2026-02-13: Reworked assigned-student editing into the full profile-style form with accessible keyboard accordion toggles and representative field defaults that preserve unset values.
209. 2026-02-14: Removed the assigned-student representative toggle field and wired assigned-student state dropdown options from localized dashboard state values.
210. 2026-02-14: Fixed assigned-student state option localization by sourcing dashboard state values from a shared shortcode helper to prevent undefined-variable warnings and empty state dropdowns.
211. 2026-02-14: Normalized assigned-student state select values so legacy two-letter state codes (for example, AL) map to full state options and preselect correctly in the dashboard edit form.
212. 2026-02-14: Updated student save handling to only persist columns represented in submitted form fields, preventing assigned-student edits from clearing untouched values like their_representative.
213. 2026-02-14: Added phone input masking to assigned-student edit forms and automatically returned assigned-student panels to read-only mode after successful saves while keeping updated values visible.
214. 2026-02-14: Refined assigned-student post-save UX to immediately restore edit/save buttons to default state while keeping the success message visible for five seconds before auto-clearing.
215. 2026-02-14: Converted assigned-student history cards into accordions that show class name and enrollment date in collapsed headers and reveal full record details on expand.
216. 2026-02-14: Added spacing above assigned-student history blocks and standardized history date/value formatting with mm-dd-yyyy dates and capitalization for lowercase values.
217. 2026-02-14: Tuned the Your Students accordion layout for narrow screens with stacked rows, single-column tables, and vertical history summary headers.
218. 2026-02-14: Hid secondary Your Students summary columns on mobile, centered history summary headers, and stacked wallet card actions for narrow screens.
219. 2026-02-14: Locked dashboard company fields as non-editable in Profile Info and assigned-student forms, and removed dashboard company update/auto-archive logic tied to previous companies.
220. 2026-02-14: Removed front-end previous-company editing on dashboard tabs by keeping previous company inputs read-only, removing add-previous-company actions, and stopping old_companies submissions from dashboard forms.
221. 2026-02-14: Locked dashboard email fields as non-editable, removed dashboard profile email update checks/sync, and suppressed empty previous-company rows when old_companies is [] in assigned-student panels.
222. 2026-02-14: Fixed assigned-student save handling so email is only persisted when explicitly submitted, preventing front-end student edits from blanking existing email values.
223. 2026-02-14: Added assigned-student previous-company empty-state messaging and locked Initial Training Date, Last Refresher Date, Expiration Date, and QCI Number as non-editable/non-submitted in dashboard edit saves.
224. 2026-02-14: Locked Profile Info representative last name/email/phone as non-editable and removed their front-end/back-end dashboard update paths while preserving representative first-name updates.
225. 2026-02-14: Locked Profile Info representative first name as non-editable and removed its dashboard update path alongside other representative contact fields.
226. 2026-02-14: Removed the Your Students search/add-student UI and client-side search/assign handlers so the tab only displays and edits already assigned students.
227. 2026-02-14: Added a locked-field helper tooltip/message across the dashboard so hovering or touching non-editable fields explains how to request updates from Ilka Porter.
228. 2026-02-14: Scoped locked-field helper tooltips to edit mode only so non-editable field notices appear only after entering Profile Info or assigned-student editing.
229. 2026-02-14: Limited edit-mode locked-field tooltips to only permanently non-editable dashboard fields (company/email/representative contact fields, previous companies, and locked assigned-student date/QCI fields).
230. 2026-02-14: Added the new [teqcidb_student_registration_shortcode] front-end shortcode, reusing the existing student dashboard login/create-account UI for logged-out visitors and a logged-in placeholder for upcoming class registration content.
231. 2026-02-14: Fixed student registration shortcode auth form AJAX wiring by matching the dashboard script dependencies/localized keys (including teqcidb_ajax_nonce), restoring login submissions and create-account feedback messages.
232. 2026-02-14: Updated logged-in student registration shortcode output to list visible teqcidb_classes entries (classhide != 1), sorted with upcoming classes first by classstartdate, with accordion-style full-width accent buttons showing class name and mm-dd-yyyy start date.
233. 2026-02-14: Updated logged-in registration class accordions to stack centered class names over centered formatted class start dates and removed the dash separator from each class label.
234. 2026-02-14: Added clickable registration class accordions for logged-in users that reveal class details (description, cost, type, format, date, start/end times, and address fields) sourced from teqcidb_classes including decoded class address data.
235. 2026-02-14: Refined registration accordion panel spacing/border styling and added smooth open/close animation transitions so class detail panels expand and collapse with less abrupt motion.
236. 2026-02-14: Updated mobile registration accordion styles so class names wrap naturally (white-space: initial) and the registration section uses 0px padding on narrow screens.
237. 2026-02-11: Added Composer-based Authorize.Net SDK integration with a reusable service that reads teqcidb_api_settings payment gateway credentials and surfaces registration-shortcode data attributes for upcoming Accept Hosted iframe checkout.
238. 2026-02-11: Updated deployment packaging to track the Composer `vendor/` directory in git so WordPress dashboard ZIP installs include the Authorize.Net SDK without running Composer on production.
239. 2026-02-11: Added a new includes/accept-hosted-token.php REST endpoint for logged-in-only Authorize.Net Accept Hosted token generation using stored TEQCIDB credentials in production mode with placeholder return/cancel/iframe URLs.
240. 2026-02-11: Added includes/accept-hosted-communicator.php to serve /sp-authnet-communicator/ via rewrite/query var/template redirect and relay Accept Hosted iframe messages to the parent window, then loaded it from plugin bootstrap.
241. 2026-02-11: Updated the Accept Hosted token endpoint to use /wp-json/teqcidb/v1/accept-hosted/token, default return/cancel URLs to /register-for-a-class-qci/, force the communicator URL to /sp-authnet-communicator/, and select sandbox/live by saved payment gateway environment.
242. 2026-02-11: Added assets/js/teqcidb-accept-hosted.js and enqueued/localized it on student registration pages as TEQCIDB_AUTHNET with Accept Hosted base URL, token REST endpoint URL, and wp_rest nonce for iframe checkout token loading.
