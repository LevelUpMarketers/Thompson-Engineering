(function () {
    const settings = window.teqcidbStudentDashboard || {};

    const handleToggle = (button) => {
        const targetId = button.dataset.teqcidbToggleTarget;
        if (!targetId) {
            return;
        }

        const input = document.getElementById(targetId);
        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const screenReaderText = button.querySelector('.screen-reader-text');
        if (screenReaderText) {
            screenReaderText.textContent = isPassword
                ? settings.toggleHideLabel || 'Hide'
                : settings.toggleShowLabel || 'Show';
        }
        button.setAttribute(
            'aria-label',
            isPassword
                ? settings.toggleHideAria || 'Hide password'
                : settings.toggleShowAria || 'Show password'
        );
        button.setAttribute(
            'title',
            isPassword
                ? settings.toggleHideAria || 'Hide password'
                : settings.toggleShowAria || 'Show password'
        );
        button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

        const icon = button.querySelector('.dashicons');
        if (icon) {
            icon.classList.toggle('dashicons-visibility', !isPassword);
            icon.classList.toggle('dashicons-hidden', isPassword);
        }
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.teqcidb-password-toggle');
        if (!button) {
            return;
        }

        handleToggle(button);
    });

    const extractPhoneDigits = (value) => {
        let digits = (value || '').replace(/\D/g, '');

        if (digits.length > 10 && digits.charAt(0) === '1') {
            digits = digits.substring(1);
        }

        return digits.substring(0, 10);
    };

    const formatDigitsAsPhone = (digits) => {
        if (!digits) {
            return '';
        }

        const area = digits.substring(0, 3);
        const prefix = digits.substring(3, 6);
        const line = digits.substring(6, 10);
        let formatted = '';

        if (area) {
            formatted = `(${area}`;

            if (area.length === 3) {
                formatted += ')';
            }
        }

        if (prefix) {
            formatted += area.length === 3 ? ` ${prefix}` : prefix;

            if (prefix.length === 3 && line) {
                formatted += '-';
            }
        }

        if (line) {
            formatted += line;
        }

        return formatted;
    };

    const applyPhoneMask = (input) => {
        if (!input) {
            return;
        }

        const digits = extractPhoneDigits(input.value);
        input.value = formatDigitsAsPhone(digits);
    };

    const phoneSelectors = [
        '#teqcidb-create-cell-phone',
        '#teqcidb-create-office-phone',
        '#teqcidb-create-rep-phone',
        '#teqcidb-profile-cell-phone',
        '#teqcidb-profile-office-phone',
        '#teqcidb-profile-rep-phone',
    ];

    phoneSelectors.forEach((selector) => {
        const input = document.querySelector(selector);
        if (!input) {
            return;
        }

        applyPhoneMask(input);
        input.addEventListener('input', () => applyPhoneMask(input));
        input.addEventListener('blur', () => applyPhoneMask(input));
    });

    const forms = document.querySelectorAll('.teqcidb-create-form');

    const isStrongPassword = (value) => {
        if (!window.wp || !window.wp.passwordStrength) {
            return value.length >= 12;
        }

        const strength = window.wp.passwordStrength.meter(value, [], value);
        return strength >= 4;
    };

    const showFeedback = (form, message, isLoading) => {
        const feedback = form.querySelector('.teqcidb-form-feedback');
        if (!feedback) {
            return;
        }

        const messageEl = feedback.querySelector('.teqcidb-form-message');
        if (messageEl) {
            messageEl.textContent = message || '';
        }

        feedback.classList.toggle('is-visible', Boolean(message) || isLoading);
        feedback.classList.toggle('is-loading', Boolean(isLoading));
    };

    const collectFormData = (form) => {
        const data = new FormData();
        const getValue = (selector) => {
            const field = form.querySelector(selector);
            return field ? field.value.trim() : '';
        };

        data.append('action', settings.ajaxAction || 'teqcidb_save_student');
        data.append('_ajax_nonce', settings.ajaxNonce || '');
        data.append('first_name', getValue('#teqcidb-create-first-name'));
        data.append('last_name', getValue('#teqcidb-create-last-name'));
        data.append('company', getValue('#teqcidb-create-company'));
        data.append('phone_cell', getValue('#teqcidb-create-cell-phone'));
        data.append('email', getValue('#teqcidb-create-email'));
        data.append('phone_office', getValue('#teqcidb-create-office-phone'));
        data.append('student_address_street_1', getValue('#teqcidb-create-street-address'));
        data.append('student_address_city', getValue('#teqcidb-create-city'));
        data.append('student_address_state', getValue('#teqcidb-create-state'));
        data.append('student_address_postal_code', getValue('#teqcidb-create-zip'));
        data.append('representative_first_name', getValue('#teqcidb-create-rep-first-name'));
        data.append('representative_last_name', getValue('#teqcidb-create-rep-last-name'));
        data.append('representative_email', getValue('#teqcidb-create-rep-email'));
        data.append('representative_phone', getValue('#teqcidb-create-rep-phone'));
        data.append('password', getValue('#teqcidb-create-password'));
        data.append('verify_password', getValue('#teqcidb-create-verify-password'));

        form.querySelectorAll('input[name="teqcidb_create_associations[]"]:checked')
            .forEach((input) => {
                data.append('associations[]', input.value);
            });

        return data;
    };

    const shouldReload = (form) => {
        return Boolean(form.closest('[data-teqcidb-dashboard="true"]'));
    };

    const submitForm = (form) => {
        if (!settings.ajaxUrl) {
            showFeedback(form, settings.messageUnknown, false);
            return;
        }

        fetch(settings.ajaxUrl, {
            method: 'POST',
            body: collectFormData(form),
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((payload) => {
                const message =
                    payload && payload.data
                        ? payload.data.message || payload.data.error
                        : '';

                if (payload && payload.success) {
                    if (shouldReload(form)) {
                        window.location.reload();
                        return;
                    }
                    showFeedback(form, message || '', false);
                } else {
                    showFeedback(
                        form,
                        message || settings.messageUnknown,
                        false
                    );
                }
            })
            .catch(() => {
                showFeedback(form, settings.messageUnknown, false);
            });
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            showFeedback(form, '', true);

            const requiredFields = Array.from(
                form.querySelectorAll('input, select, textarea')
            ).filter((field) => {
                if (field.closest('.teqcidb-form-checkbox')) {
                    return false;
                }

                if (field.type === 'checkbox' || field.type === 'radio') {
                    return false;
                }

                return ![
                    'teqcidb_create_office_phone',
                    'teqcidb_create_rep_first_name',
                    'teqcidb_create_rep_last_name',
                    'teqcidb_create_rep_email',
                    'teqcidb_create_rep_phone',
                ].includes(field.name);
            });

            const hasEmptyRequired = requiredFields.some((field) => {
                if (field.tagName === 'SELECT') {
                    return !field.value || !field.value.trim();
                }

                return !field.value || !field.value.trim();
            });

            if (hasEmptyRequired) {
                showFeedback(form, settings.messageRequired, false);
                return;
            }

            const email = form.querySelector('#teqcidb-create-email');
            const verifyEmail = form.querySelector('#teqcidb-create-verify-email');

            if (
                email &&
                verifyEmail &&
                email.value.trim().toLowerCase() !==
                    verifyEmail.value.trim().toLowerCase()
            ) {
                showFeedback(form, settings.messageEmail, false);
                return;
            }

            const password = form.querySelector('#teqcidb-create-password');
            const verifyPassword = form.querySelector(
                '#teqcidb-create-verify-password'
            );

            if (
                password &&
                verifyPassword &&
                password.value !== verifyPassword.value
            ) {
                showFeedback(form, settings.messagePassword, false);
                return;
            }

            if (
                password &&
                verifyPassword &&
                (!isStrongPassword(password.value) ||
                    !isStrongPassword(verifyPassword.value))
            ) {
                showFeedback(form, settings.messageStrength, false);
                return;
            }

            submitForm(form);
        });
    });

    const loginForms = document.querySelectorAll('.teqcidb-login-form');

    const submitLogin = (form) => {
        if (!settings.ajaxUrl) {
            showFeedback(form, settings.messageLoginFailed, false);
            return;
        }

        const username = form.querySelector('#teqcidb-login-username');
        const password = form.querySelector('#teqcidb-login-password');
        const remember = form.querySelector('#teqcidb-login-remember');

        if (
            !username ||
            !password ||
            !username.value.trim() ||
            !password.value.trim()
        ) {
            showFeedback(form, settings.messageLoginRequired, false);
            return;
        }

        const data = new FormData();
        data.append('action', settings.ajaxLoginAction || 'teqcidb_login_user');
        data.append('_ajax_nonce', settings.ajaxNonce || '');
        data.append('log', username.value.trim());
        data.append('pwd', password.value);
        data.append('rememberme', remember && remember.checked ? '1' : '0');

        fetch(settings.ajaxUrl, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((payload) => {
                if (payload && payload.success) {
                    if (shouldReload(form)) {
                        window.location.reload();
                        return;
                    }
                    showFeedback(form, '', false);
                } else {
                    showFeedback(
                        form,
                        (payload && payload.data && payload.data.message) ||
                            settings.messageLoginFailed,
                        false
                    );
                }
            })
            .catch(() => {
                showFeedback(form, settings.messageLoginFailed, false);
            });
    };

    loginForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            showFeedback(form, '', true);
            submitLogin(form);
        });
    });

    const activateDashboardTab = (dashboard, tab) => {
        if (!dashboard || !tab) {
            return;
        }

        const tabId = tab.getAttribute('id');
        const panelId = tab.getAttribute('aria-controls');
        const tabs = Array.from(
            dashboard.querySelectorAll('.teqcidb-dashboard-tab')
        );
        const panels = Array.from(
            dashboard.querySelectorAll('.teqcidb-dashboard-panel')
        );

        tabs.forEach((item) => {
            const isActive = item === tab;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            item.setAttribute('tabindex', isActive ? '0' : '-1');
        });

        panels.forEach((panel) => {
            const isActive = panel.getAttribute('id') === panelId;
            panel.classList.toggle('is-active', isActive);
            panel.toggleAttribute('hidden', !isActive);
            panel.setAttribute(
                'aria-labelledby',
                tabId || panel.getAttribute('aria-labelledby') || ''
            );
        });
    };

    const dashboards = document.querySelectorAll('.teqcidb-dashboard');
    const tabAliases = {
        profileinfo: 'profile-info',
        classhistory: 'class-history',
        certificatesdates: 'certificates-dates',
        paymenthistory: 'payment-history',
        yourstudents: 'your-students',
        registerstudents: 'register-students',
    };
    const normalizeTabParam = (value) => {
        if (!value) {
            return '';
        }

        const cleaned = value.toLowerCase().replace(/[^a-z]/g, '');
        if (tabAliases[cleaned]) {
            return tabAliases[cleaned];
        }

        return value.toLowerCase();
    };

    dashboards.forEach((dashboard) => {
        const tabs = Array.from(
            dashboard.querySelectorAll('.teqcidb-dashboard-tab')
        );

        if (!tabs.length) {
            return;
        }

        const scrollToPanel = (tab) => {
            if (!window.matchMedia('(max-width: 980px)').matches) {
                return;
            }

            const panelId = tab.getAttribute('aria-controls');
            if (!panelId) {
                return;
            }

            const panel = dashboard.querySelector(`#${panelId}`);
            if (!panel) {
                return;
            }

            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activateDashboardTab(dashboard, tab);
                scrollToPanel(tab);
            });
        });

        const queryTab = normalizeTabParam(
            new URLSearchParams(window.location.search).get('tab')
        );
        if (queryTab) {
            const matchedTab = tabs.find(
                (tab) => tab.dataset.teqcidbTab === queryTab
            );
            if (matchedTab) {
                activateDashboardTab(dashboard, matchedTab);
                scrollToPanel(matchedTab);
            }
        }
    });

    const initLockedFieldNotice = () => {
        const message =
            settings.lockedFieldMessage ||
            "This can't be edited! Please contact Ilka Porter (iporter@thompsonengineering.com) to change this info.";
        const alwaysLockedProfileFieldIds = [
            'teqcidb-profile-company',
            'teqcidb-profile-email',
            'teqcidb-profile-rep-first-name',
            'teqcidb-profile-rep-last-name',
            'teqcidb-profile-rep-email',
            'teqcidb-profile-rep-phone',
        ];
        const alwaysLockedStudentFields = [
            'company',
            'email',
            'old_companies',
            'initial_training_date',
            'last_refresher_date',
            'expiration_date',
            'qcinumber',
        ];

        const allDashboardFields = Array.from(
            document.querySelectorAll(
                '.teqcidb-dashboard .teqcidb-form-field input, .teqcidb-dashboard .teqcidb-form-field select, .teqcidb-dashboard .teqcidb-form-field textarea'
            )
        );

        allDashboardFields.forEach((field) => {
            const fieldWrapper = field.closest('.teqcidb-form-field');
            if (!fieldWrapper) {
                return;
            }

            const isProfileLocked = alwaysLockedProfileFieldIds.includes(field.id)
                || field.name === 'teqcidb_profile_old_companies[]';
            const studentFieldKey = field.dataset ? field.dataset.studentField : '';
            const isAssignedStudentLocked = alwaysLockedStudentFields.includes(studentFieldKey);

            if (isProfileLocked || isAssignedStudentLocked) {
                fieldWrapper.classList.add('teqcidb-field-is-locked');
                fieldWrapper.dataset.teqcidbLockedMessage = message;
                return;
            }

            fieldWrapper.classList.remove('teqcidb-field-is-locked');
            delete fieldWrapper.dataset.teqcidbLockedMessage;
        });

        let hideTimer = null;

        const hideVisibleNotice = () => {
            document
                .querySelectorAll('.teqcidb-field-is-locked.is-locked-notice-visible')
                .forEach((wrapper) => {
                    wrapper.classList.remove('is-locked-notice-visible');
                });
        };

        document.addEventListener('touchstart', (event) => {
            const wrapper = event.target.closest('.teqcidb-field-is-locked');
            if (wrapper) {
                const editingForm = wrapper.closest('form.is-editing');
                if (!editingForm) {
                    hideVisibleNotice();
                    return;
                }

                hideVisibleNotice();
                wrapper.classList.add('is-locked-notice-visible');

                if (hideTimer) {
                    window.clearTimeout(hideTimer);
                }

                hideTimer = window.setTimeout(() => {
                    wrapper.classList.remove('is-locked-notice-visible');
                    hideTimer = null;
                }, 2600);
                return;
            }

            hideVisibleNotice();
        });
    };

    initLockedFieldNotice();

    const profileForms = document.querySelectorAll('[data-teqcidb-profile-form]');

    const getProfileSnapshot = (fields) => {
        return fields.map((field) => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                return { field, checked: field.checked };
            }

            return { field, value: field.value };
        });
    };

    const restoreProfileSnapshot = (snapshot) => {
        snapshot.forEach((entry) => {
            if ('checked' in entry) {
                entry.field.checked = entry.checked;
            } else {
                entry.field.value = entry.value;
            }
        });
    };

    const setProfileFieldsDisabled = (fields, disabled) => {
        fields.forEach((field) => {
            if (
                !disabled &&
                (field.id === 'teqcidb-profile-company' || field.id === 'teqcidb-profile-email' || field.id === 'teqcidb-profile-rep-first-name' || field.id === 'teqcidb-profile-rep-last-name' || field.id === 'teqcidb-profile-rep-email' || field.id === 'teqcidb-profile-rep-phone' || field.name === 'teqcidb_profile_old_companies[]')
            ) {
                field.disabled = true;
                return;
            }
            field.disabled = disabled;
        });
    };

    const collectProfileFormData = (form) => {
        const data = new FormData();
        const getValue = (selector) => {
            const field = form.querySelector(selector);
            return field ? field.value.trim() : '';
        };
        data.append('action', settings.profileUpdateAction || 'teqcidb_update_profile');
        data.append('_ajax_nonce', settings.ajaxNonce || '');
        data.append('first_name', getValue('#teqcidb-profile-first-name'));
        data.append('last_name', getValue('#teqcidb-profile-last-name'));
        data.append('phone_cell', getValue('#teqcidb-profile-cell-phone'));
        data.append('phone_office', getValue('#teqcidb-profile-office-phone'));
        data.append('student_address_street_1', getValue('#teqcidb-profile-street-address'));
        data.append('student_address_street_2', getValue('#teqcidb-profile-street-address-2'));
        data.append('student_address_city', getValue('#teqcidb-profile-city'));
        data.append('student_address_state', getValue('#teqcidb-profile-state'));
        data.append('student_address_postal_code', getValue('#teqcidb-profile-zip'));

        form.querySelectorAll('input[name="teqcidb_profile_associations[]"]:checked')
            .forEach((input) => {
                data.append('associations[]', input.value);
            });

        return data;
    };

    const handleProfileSave = (form, fields, editButton, saveButton, snapshotRef) => {
        const requiredSelectors = [
            '#teqcidb-profile-first-name',
            '#teqcidb-profile-last-name',
        ];

        const hasEmptyRequired = requiredSelectors.some((selector) => {
            const field = form.querySelector(selector);
            return !field || !field.value || !field.value.trim();
        });

        if (hasEmptyRequired) {
            showFeedback(form, settings.profileMessageRequired, false);
            return;
        }

        if (!settings.ajaxUrl) {
            showFeedback(form, settings.profileMessageSaveError, false);
            return;
        }

        showFeedback(form, '', true);

        fetch(settings.ajaxUrl, {
            method: 'POST',
            body: collectProfileFormData(form),
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((payload) => {
                if (payload && payload.success) {
                    showFeedback(
                        form,
                        (payload.data && payload.data.message) ||
                            settings.profileMessageSaved,
                        false
                    );
                    snapshotRef.current = getProfileSnapshot(fields);
                    setProfileFieldsDisabled(fields, true);
                    form.classList.remove('is-editing');
                    editButton.dataset.editing = 'false';
                    editButton.textContent =
                        settings.profileEditLabel || 'Edit Profile Info';
                    saveButton.disabled = true;
                } else {
                    const message =
                        payload && payload.data && payload.data.message
                            ? payload.data.message
                            : settings.profileMessageSaveError;
                    showFeedback(form, message, false);
                }
            })
            .catch(() => {
                showFeedback(form, settings.profileMessageSaveError, false);
            });
    };

    profileForms.forEach((form) => {
        const editButton = form.querySelector('[data-teqcidb-profile-edit]');
        const saveButton = form.querySelector('[data-teqcidb-profile-save]');
        const fields = Array.from(
            form.querySelectorAll('input, select, textarea')
        );

        if (!editButton || !saveButton || !fields.length) {
            return;
        }

        const snapshotRef = { current: getProfileSnapshot(fields) };
        editButton.addEventListener('click', () => {
            const isEditing = editButton.dataset.editing === 'true';

            if (isEditing) {
                restoreProfileSnapshot(snapshotRef.current);
                setProfileFieldsDisabled(fields, true);
                form.classList.remove('is-editing');
                editButton.dataset.editing = 'false';
                editButton.textContent =
                    settings.profileEditLabel || 'Edit Profile Info';
                saveButton.disabled = true;
                showFeedback(form, '', false);
                return;
            }

            setProfileFieldsDisabled(fields, false);
            form.classList.add('is-editing');
            editButton.dataset.editing = 'true';
            editButton.textContent =
                settings.profileCancelLabel || 'Cancel Editing';
            saveButton.disabled = false;
        });

        saveButton.addEventListener('click', () => {
            handleProfileSave(
                form,
                fields,
                editButton,
                saveButton,
                snapshotRef
            );
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!saveButton.disabled) {
                handleProfileSave(
                    form,
                    fields,
                    editButton,
                    saveButton,
                    snapshotRef
                );
            }
        });
    });

    const studentSearchSettings = settings.studentSearch || {};

    const parseJsonList = (value) => {
        if (!value || typeof value !== 'string') {
            return null;
        }

        if (!value.trim().startsWith('[')) {
            return null;
        }

        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : null;
        } catch (error) {
            return null;
        }
    };

    const formatStudentValue = (value, key) => {
        const emptyValue = studentSearchSettings.emptyValue || '—';

        if (key === 'is_a_representative') {
            const boolLabels = studentSearchSettings.booleanLabels || {};
            if (typeof value === 'string' && value in boolLabels) {
                return boolLabels[value];
            }
        }

        const parsedList = parseJsonList(value);
        if (parsedList) {
            return parsedList.length ? parsedList.join(', ') : emptyValue;
        }

        if (Array.isArray(value)) {
            return value.length ? value.join(', ') : emptyValue;
        }

        if (value === null || typeof value === 'undefined') {
            return emptyValue;
        }

        if (typeof value === 'string') {
            const trimmed = value.trim();
            return trimmed ? trimmed : emptyValue;
        }

        return value;
    };

    const buildStudentDetails = (entity) => {
        const detailFields = studentSearchSettings.detailFields || [];
        const wrapper = document.createElement('div');
        wrapper.className = 'teqcidb-student-details';

        const heading = document.createElement('h4');
        heading.className = 'teqcidb-student-details-heading';
        heading.textContent =
            studentSearchSettings.detailsHeading || 'Student Information';
        wrapper.appendChild(heading);

        const detailsGrid = document.createElement('dl');
        detailsGrid.className = 'teqcidb-student-details-grid';

        detailFields.forEach((field) => {
            if (!field || !field.key) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'teqcidb-student-details-item';

            const label = document.createElement('dt');
            label.textContent = field.label || field.key;

            const value = document.createElement('dd');
            value.textContent = formatStudentValue(entity[field.key], field.key);

            row.appendChild(label);
            row.appendChild(value);
            detailsGrid.appendChild(row);
        });

        wrapper.appendChild(detailsGrid);

        return wrapper;
    };

    const buildStudentHistory = (entity) => {
        const historyWrapper = document.createElement('div');
        historyWrapper.className = 'teqcidb-student-history';

        const heading = document.createElement('h4');
        heading.className = 'teqcidb-student-history-heading';
        heading.textContent =
            studentSearchSettings.historyHeading || 'Student History';
        historyWrapper.appendChild(heading);

        const historyEntries = Array.isArray(entity.studenthistory)
            ? entity.studenthistory
            : [];

        if (!historyEntries.length) {
            const empty = document.createElement('p');
            empty.className = 'teqcidb-student-history-empty';
            empty.textContent =
                studentSearchSettings.historyEmpty ||
                'No student history entries were found.';
            historyWrapper.appendChild(empty);
            return historyWrapper;
        }

        const historyList = document.createElement('div');
        historyList.className = 'teqcidb-student-history-list';

        const historyFields = studentSearchSettings.historyFields || [];
        const formatHistoryDate = (value) => {
            if (typeof value !== 'string') {
                return value;
            }

            const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!match) {
                return value;
            }

            return `${match[2]}-${match[3]}-${match[1]}`;
        };

        const formatHistoryValue = (value, key) => {
            const formatted = formatStudentValue(value, key);
            if (typeof formatted !== 'string') {
                return formatted;
            }

            let updated = formatted;
            if (key && key.toLowerCase().includes('date')) {
                updated = formatHistoryDate(updated);
            }

            if (updated && updated === updated.toLowerCase()) {
                updated = updated.charAt(0).toUpperCase() + updated.slice(1);
            }

            return updated;
        };

        historyEntries.forEach((entry, index) => {
            const card = document.createElement('article');
            card.className = 'teqcidb-student-history-card';

            const headerButton = document.createElement('button');
            headerButton.type = 'button';
            headerButton.className = 'teqcidb-student-history-summary';
            headerButton.setAttribute('aria-expanded', 'false');

            const titleWrap = document.createElement('span');
            titleWrap.className = 'teqcidb-student-history-summary-main';

            const className = formatHistoryValue(entry.classname, 'classname');
            const enrollmentDate = formatHistoryValue(entry.enrollmentdate, 'enrollmentdate');
            const titleTemplate =
                studentSearchSettings.historyEntryTitle || 'History Entry %s';

            const classNameLabel = document.createElement('span');
            classNameLabel.className = 'teqcidb-student-history-summary-title';
            classNameLabel.textContent = className === (studentSearchSettings.emptyValue || '—')
                ? titleTemplate.replace('%s', (index + 1).toString())
                : className;

            const enrollmentLabel = document.createElement('span');
            enrollmentLabel.className = 'teqcidb-student-history-summary-meta';
            const enrollmentText = studentSearchSettings.historyEnrollmentDateLabel || 'Enrollment Date';
            enrollmentLabel.textContent = `${enrollmentText}: ${enrollmentDate}`;

            titleWrap.appendChild(classNameLabel);
            titleWrap.appendChild(enrollmentLabel);

            const indicator = document.createElement('span');
            indicator.className = 'teqcidb-student-history-summary-indicator';
            indicator.setAttribute('aria-hidden', 'true');
            indicator.textContent = '▾';

            headerButton.appendChild(titleWrap);
            headerButton.appendChild(indicator);

            const cardBody = document.createElement('div');
            cardBody.className = 'teqcidb-student-history-body';
            cardBody.hidden = true;

            const cardGrid = document.createElement('dl');
            cardGrid.className = 'teqcidb-student-history-grid';

            historyFields.forEach((field) => {
                if (!field || !field.key) {
                    return;
                }

                const row = document.createElement('div');
                row.className = 'teqcidb-student-history-item';

                const label = document.createElement('dt');
                label.textContent = field.label || field.key;

                const value = document.createElement('dd');
                value.textContent = formatHistoryValue(entry[field.key], field.key);

                row.appendChild(label);
                row.appendChild(value);
                cardGrid.appendChild(row);
            });

            cardBody.appendChild(cardGrid);

            headerButton.addEventListener('click', () => {
                const isOpen = headerButton.getAttribute('aria-expanded') === 'true';
                headerButton.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                card.classList.toggle('is-open', !isOpen);
                cardBody.hidden = isOpen;
            });

            card.appendChild(headerButton);
            card.appendChild(cardBody);
            historyList.appendChild(card);
        });

        historyWrapper.appendChild(historyList);
        return historyWrapper;
    };
    const getEntityListValues = (value) => {
        const parsed = parseJsonList(value);
        if (parsed) {
            return parsed;
        }

        if (Array.isArray(value)) {
            return value.filter((entry) => typeof entry === 'string' && entry.trim());
        }

        return [];
    };

    const resolveAssignedStudentStateValue = (stateValue, options) => {
        const normalized = typeof stateValue === 'string' ? stateValue.trim() : '';
        if (!normalized || !Array.isArray(options) || !options.length) {
            return normalized;
        }

        const optionValues = new Set(options.map((option) => option.value));
        if (optionValues.has(normalized)) {
            return normalized;
        }

        const stateCodes = {
            AL: 'Alabama', AK: 'Alaska', AZ: 'Arizona', AR: 'Arkansas', CA: 'California',
            CO: 'Colorado', CT: 'Connecticut', DE: 'Delaware', FL: 'Florida', GA: 'Georgia',
            HI: 'Hawaii', ID: 'Idaho', IL: 'Illinois', IN: 'Indiana', IA: 'Iowa', KS: 'Kansas',
            KY: 'Kentucky', LA: 'Louisiana', ME: 'Maine', MD: 'Maryland', MA: 'Massachusetts',
            MI: 'Michigan', MN: 'Minnesota', MS: 'Mississippi', MO: 'Missouri', MT: 'Montana',
            NE: 'Nebraska', NV: 'Nevada', NH: 'New Hampshire', NJ: 'New Jersey', NM: 'New Mexico',
            NY: 'New York', NC: 'North Carolina', ND: 'North Dakota', OH: 'Ohio', OK: 'Oklahoma',
            OR: 'Oregon', PA: 'Pennsylvania', RI: 'Rhode Island', SC: 'South Carolina',
            SD: 'South Dakota', TN: 'Tennessee', TX: 'Texas', UT: 'Utah', VT: 'Vermont',
            VA: 'Virginia', WA: 'Washington', WV: 'West Virginia', WI: 'Wisconsin', WY: 'Wyoming',
        };

        const upperCode = normalized.toUpperCase();
        if (Object.prototype.hasOwnProperty.call(stateCodes, upperCode)) {
            const mapped = stateCodes[upperCode];
            if (optionValues.has(mapped)) {
                return mapped;
            }
        }

        const matchingOption = options.find((option) =>
            typeof option.label === 'string' && option.label.toLowerCase() === normalized.toLowerCase()
        );

        return matchingOption ? matchingOption.value : normalized;
    };

    const buildAssignedStudentForm = (entity) => {
        const form = document.createElement('form');
        form.className = 'teqcidb-profile-form teqcidb-assigned-student-form';
        form.setAttribute('data-teqcidb-assigned-form', 'true');
        form.dataset.studentId = entity.id ? String(entity.id) : '';

        const createField = ({ label, key, type = 'text', options = null, autocomplete = '', value = '' }) => {
            const field = document.createElement('div');
            field.className = 'teqcidb-form-field';

            const inputId = `teqcidb-assigned-${form.dataset.studentId || 'student'}-${key}`;

            const labelEl = document.createElement('label');
            labelEl.setAttribute('for', inputId);
            labelEl.textContent = label;

            let input;
            if (Array.isArray(options)) {
                input = document.createElement('select');
                options.forEach((option) => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.label;
                    input.appendChild(optionEl);
                });
                input.value = value || '';
            } else {
                input = document.createElement('input');
                input.type = type;
                input.value = value || '';
            }

            input.id = inputId;
            input.disabled = true;
            input.dataset.studentField = key;
            input.dataset.initialValue = input.value;
            if (autocomplete) {
                input.autocomplete = autocomplete;
            }

            if (input.tagName === 'INPUT' && input.type === 'tel') {
                applyPhoneMask(input);
                input.addEventListener('input', () => applyPhoneMask(input));
                input.addEventListener('blur', () => applyPhoneMask(input));
            }

            field.appendChild(labelEl);
            field.appendChild(input);

            return field;
        };

        const formGrid = document.createElement('div');
        formGrid.className = 'teqcidb-form-grid';

        const stateOptions = [{ value: '', label: studentSearchSettings.emptySelectLabel || 'Make a selection' }]
            .concat((studentSearchSettings.stateOptions || []).map((state) => ({ value: state, label: state })));
        const fields = [
            { label: 'First Name', key: 'first_name', autocomplete: 'given-name' },
            { label: 'Last Name', key: 'last_name', autocomplete: 'family-name' },
            { label: 'Company', key: 'company', autocomplete: 'organization' },
            { label: 'Cell Phone', key: 'phone_cell', type: 'tel', autocomplete: 'tel' },
            { label: 'Office Phone', key: 'phone_office', type: 'tel', autocomplete: 'tel' },
            { label: 'Email', key: 'email', type: 'email', autocomplete: 'email' },
            { label: 'Street Address', key: 'student_address_street_1', autocomplete: 'street-address' },
            { label: 'Address Line 2', key: 'student_address_street_2', autocomplete: 'address-line2' },
            { label: 'City', key: 'student_address_city', autocomplete: 'address-level2' },
            { label: 'State', key: 'student_address_state', options: stateOptions, autocomplete: 'address-level1' },
            { label: 'Zip Code', key: 'student_address_postal_code', autocomplete: 'postal-code' },
            { label: 'Fax', key: 'fax', type: 'tel' },
            { label: 'Initial Training Date', key: 'initial_training_date', type: 'date' },
            { label: 'Last Refresher Date', key: 'last_refresher_date', type: 'date' },
            { label: 'Expiration Date', key: 'expiration_date', type: 'date' },
            { label: 'QCI Number', key: 'qcinumber' },
        ];

        fields.forEach((fieldConfig) => {
            let value = entity[fieldConfig.key] || '';
            if (fieldConfig.key === 'student_address_state') {
                value = resolveAssignedStudentStateValue(value, stateOptions);
            }
            formGrid.appendChild(createField({ ...fieldConfig, value }));
        });

        form.appendChild(formGrid);

        const oldCompaniesFieldset = document.createElement('fieldset');
        oldCompaniesFieldset.className = 'teqcidb-form-fieldset teqcidb-profile-old-companies';
        const oldCompaniesLegend = document.createElement('legend');
        oldCompaniesLegend.textContent = studentSearchSettings.oldCompaniesLegend || 'Previous Companies';
        oldCompaniesFieldset.appendChild(oldCompaniesLegend);

        const oldCompanies = getEntityListValues(entity.old_companies);

        if (!oldCompanies.length) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'teqcidb-dashboard-empty';
            emptyMessage.textContent =
                studentSearchSettings.oldCompaniesEmpty || 'No previous companies.';
            oldCompaniesFieldset.appendChild(emptyMessage);
        }

        const oldCompaniesGrid = document.createElement('div');
        oldCompaniesGrid.className = 'teqcidb-form-grid';
        oldCompaniesGrid.dataset.teqcidbAssignedOldCompanies = 'true';
        const oldCompanyValues = oldCompanies;
        oldCompanyValues.forEach((company, index) => {
            const companyField = createField({
                label: `${studentSearchSettings.oldCompanyLabel || 'Previous Company'} ${index + 1}`,
                key: `old_company_${index + 1}`,
                value: company,
                autocomplete: 'organization',
            });

            const input = companyField.querySelector('input,select,textarea');
            if (input) {
                input.dataset.studentField = 'old_companies';
                input.dataset.listField = 'true';
            }

            oldCompaniesGrid.appendChild(companyField);
        });

        oldCompaniesFieldset.appendChild(oldCompaniesGrid);

        form.appendChild(oldCompaniesFieldset);

        const associationsFieldset = document.createElement('fieldset');
        associationsFieldset.className = 'teqcidb-form-fieldset teqcidb-profile-associations';
        const associationsLegend = document.createElement('legend');
        associationsLegend.textContent = studentSearchSettings.associationsLegend || 'Affiliated Associations';
        associationsFieldset.appendChild(associationsLegend);

        const checkboxGrid = document.createElement('div');
        checkboxGrid.className = 'teqcidb-checkbox-grid';

        const selectedAssociations = getEntityListValues(entity.associations);
        const associationOptions = studentSearchSettings.associationOptions || ['AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA'];

        associationOptions.forEach((option) => {
            const key = typeof option === 'string' ? option : option.value;
            const label = typeof option === 'string' ? option : option.label;
            const checkboxId = `teqcidb-assigned-${form.dataset.studentId || 'student'}-association-${String(key).toLowerCase()}`;

            const wrapper = document.createElement('label');
            wrapper.className = 'teqcidb-checkbox';
            wrapper.setAttribute('for', checkboxId);

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.id = checkboxId;
            input.value = key;
            input.dataset.studentField = 'associations';
            input.dataset.listField = 'true';
            input.checked = selectedAssociations.includes(key);
            input.disabled = true;

            const span = document.createElement('span');
            span.textContent = label;

            wrapper.appendChild(input);
            wrapper.appendChild(span);
            checkboxGrid.appendChild(wrapper);
        });

        associationsFieldset.appendChild(checkboxGrid);
        form.appendChild(associationsFieldset);

        const actions = document.createElement('div');
        actions.className = 'teqcidb-profile-actions teqcidb-student-edit';

        const editButton = document.createElement('button');
        editButton.className = 'teqcidb-button teqcidb-button-primary';
        editButton.type = 'button';
        editButton.dataset.teqcidbEditStudent = 'true';
        editButton.textContent = studentSearchSettings.editLabel || 'Edit This Student';

        const saveButton = document.createElement('button');
        saveButton.className = 'teqcidb-button teqcidb-button-secondary';
        saveButton.type = 'button';
        saveButton.dataset.teqcidbSaveStudent = 'true';
        saveButton.textContent = studentSearchSettings.saveLabel || 'Save Changes';
        saveButton.disabled = true;

        const feedback = document.createElement('div');
        feedback.className = 'teqcidb-form-feedback';
        feedback.setAttribute('aria-live', 'polite');

        const spinner = document.createElement('span');
        spinner.className = 'teqcidb-spinner';
        spinner.setAttribute('aria-hidden', 'true');

        const message = document.createElement('span');
        message.className = 'teqcidb-form-message';

        feedback.appendChild(spinner);
        feedback.appendChild(message);

        actions.appendChild(editButton);
        actions.appendChild(saveButton);
        actions.appendChild(feedback);
        form.appendChild(actions);

        return form;
    };

    const renderStudentResults = (tbody, entities, columnCount, options = {}) => {
        tbody.innerHTML = '';

        if (!entities.length) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'no-items';
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = columnCount;
            emptyCell.textContent =
                studentSearchSettings.assignedEmpty ||
                'No students are currently assigned to you.';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
            return;
        }

        const summaryFields = studentSearchSettings.summaryFields || [];
        entities.forEach((entity) => {
            const entityId = entity.id || Math.random().toString(36).slice(2);
            const headerId = `teqcidb-student-${entityId}-header`;
            const panelId = `teqcidb-student-${entityId}-panel`;

            const summaryRow = document.createElement('tr');
            summaryRow.id = headerId;
            summaryRow.className = 'teqcidb-accordion__summary-row';
            summaryRow.setAttribute('tabindex', '0');
            summaryRow.setAttribute('role', 'button');
            summaryRow.setAttribute('aria-expanded', 'false');
            summaryRow.setAttribute('aria-controls', panelId);

            summaryFields.forEach((fieldKey, index) => {
                const cell = document.createElement('td');
                const cellClass =
                    index === 0
                        ? 'teqcidb-accordion__cell teqcidb-accordion__cell--title'
                        : 'teqcidb-accordion__cell teqcidb-accordion__cell--meta';
                cell.className = cellClass;

                if (index === 0) {
                    const titleText = document.createElement('span');
                    titleText.className = 'teqcidb-accordion__title-text';
                    const valueSpan = document.createElement('span');
                    valueSpan.className = 'teqcidb-accordion__meta-value';
                    valueSpan.textContent = formatStudentValue(
                        entity[fieldKey],
                        fieldKey
                    );
                    titleText.appendChild(valueSpan);
                    cell.appendChild(titleText);
                } else {
                    const metaText = document.createElement('span');
                    metaText.className = 'teqcidb-accordion__meta-text';
                    const valueSpan = document.createElement('span');
                    valueSpan.className = 'teqcidb-accordion__meta-value';
                    valueSpan.textContent = formatStudentValue(
                        entity[fieldKey],
                        fieldKey
                    );
                    metaText.appendChild(valueSpan);
                    cell.appendChild(metaText);
                }

                summaryRow.appendChild(cell);
            });
            tbody.appendChild(summaryRow);

            const panelRow = document.createElement('tr');
            panelRow.id = panelId;
            panelRow.className = 'teqcidb-accordion__panel-row';
            panelRow.setAttribute('role', 'region');
            panelRow.setAttribute('aria-labelledby', headerId);
            panelRow.setAttribute('aria-hidden', 'true');
            panelRow.hidden = true;

            const panelCell = document.createElement('td');
            panelCell.colSpan = columnCount;
            const panel = document.createElement('div');
            panel.className = 'teqcidb-accordion__panel';

            if (options.editable) {
                panel.appendChild(buildAssignedStudentForm(entity));
                panel.appendChild(buildStudentHistory(entity));
            } else {
                panel.appendChild(buildStudentDetails(entity));
                panel.appendChild(buildStudentHistory(entity));
            }

            panelCell.appendChild(panel);
            panelRow.appendChild(panelCell);
            tbody.appendChild(panelRow);
        });
    };
    const setEditFeedback = (wrapper, message, isLoading) => {
        if (!wrapper) {
            return;
        }

        const hideTimer = wrapper._teqcidbEditFeedbackHideTimer;
        if (hideTimer) {
            window.clearTimeout(hideTimer);
            wrapper._teqcidbEditFeedbackHideTimer = null;
        }

        const feedback = wrapper.querySelector('.teqcidb-form-feedback');
        if (!feedback) {
            return;
        }
        const feedbackMessage = feedback.querySelector('.teqcidb-form-message');
        if (feedbackMessage) {
            feedbackMessage.textContent = message || '';
        }
        feedback.classList.toggle('is-visible', Boolean(message) || isLoading);
        feedback.classList.toggle('is-loading', Boolean(isLoading));
    };

    const setTimedEditFeedback = (wrapper, message, durationMs = 5000) => {
        setEditFeedback(wrapper, message, false);

        if (!wrapper || !message) {
            return;
        }

        wrapper._teqcidbEditFeedbackHideTimer = window.setTimeout(() => {
            setEditFeedback(wrapper, '', false);
            wrapper._teqcidbEditFeedbackHideTimer = null;
        }, durationMs);
    };
    const resetStudentDetails = (panel) => {
        panel.querySelectorAll('[data-student-field]').forEach((input) => {
            if (!Object.prototype.hasOwnProperty.call(input.dataset, 'initialValue')) {
                return;
            }

            if (input.type === 'checkbox') {
                input.checked = input.dataset.initialValue === '1';
                return;
            }

            input.value = input.dataset.initialValue;
        });
    };

    const setStudentDetailsEditable = (panel, editable) => {
        panel.querySelectorAll('[data-student-field]').forEach((input) => {
            if (
                input.dataset.studentField === 'company' ||
                input.dataset.studentField === 'email' ||
                input.dataset.studentField === 'old_companies' ||
                input.dataset.studentField === 'initial_training_date' ||
                input.dataset.studentField === 'last_refresher_date' ||
                input.dataset.studentField === 'expiration_date' ||
                input.dataset.studentField === 'qcinumber'
            ) {
                input.disabled = true;
                return;
            }
            input.disabled = !editable;
        });
    };

    const resetAssignedStudentEditState = (panel) => {
        if (!panel) {
            return;
        }

        setStudentDetailsEditable(panel, false);

        const editButton = panel.querySelector('[data-teqcidb-edit-student]');
        const saveButton = panel.querySelector('[data-teqcidb-save-student]');
        const form = panel.querySelector('[data-teqcidb-assigned-form]');

        if (form) {
            form.classList.remove('is-editing');
        }

        if (editButton) {
            editButton.removeAttribute('data-editing');
            editButton.textContent = studentSearchSettings.editLabel || 'Edit This Student';
        }

        if (saveButton) {
            saveButton.disabled = true;
        }
    };

    const handleSaveStudent = async (button) => {
        const form = button.closest('[data-teqcidb-assigned-form]');
        const panel = button.closest('.teqcidb-accordion__panel');
        const wrapper = button.closest('.teqcidb-student-edit');
        const studentId = button.dataset.studentId || (form ? form.dataset.studentId : '');
        const action = studentSearchSettings.saveAction || 'teqcidb_save_student';

        if (!form || !panel || !studentId) {
            setEditFeedback(
                wrapper,
                studentSearchSettings.saveError ||
                    'Unable to save student details right now. Please try again.',
                false
            );
            return;
        }

        const formData = new FormData();
        formData.append('action', action);
        formData.append('_ajax_nonce', settings.ajaxNonce || '');
        formData.append('id', studentId);

        form.querySelectorAll('[data-student-field]').forEach((input) => {
            const key = input.dataset.studentField;
            if (!key) {
                return;
            }

            if (
                input.dataset.listField === 'true' ||
                key === 'company' ||
                key === 'email' ||
                key === 'initial_training_date' ||
                key === 'last_refresher_date' ||
                key === 'expiration_date' ||
                key === 'qcinumber'
            ) {
                return;
            }

            const value = input.value.trim();
            formData.append(key, value);
        });

        form.querySelectorAll('[data-student-field="associations"]').forEach((input) => {
            if (input.type === 'checkbox' && input.checked) {
                formData.append('associations[]', input.value);
            }
        });

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        setEditFeedback(wrapper, '', true);

        try {
            const response = await fetch(settings.ajaxUrl || '', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });

            const payload = await response.json();
            if (payload && payload.success) {
                form.querySelectorAll('[data-student-field]').forEach((input) => {
                    if (input.type === 'checkbox') {
                        input.dataset.initialValue = input.checked ? '1' : '0';
                    } else {
                        input.dataset.initialValue = input.value;
                    }
                });
                resetAssignedStudentEditState(panel);
                setTimedEditFeedback(
                    wrapper,
                    (payload.data && payload.data.message) ||
                        studentSearchSettings.saveSuccess ||
                        'Student details updated.',
                    5000
                );
            } else {
                setEditFeedback(
                    wrapper,
                    (payload && payload.data && payload.data.message) ||
                        studentSearchSettings.saveError ||
                        'Unable to save student details right now. Please try again.',
                    false
                );
            }
        } catch (error) {
            setEditFeedback(
                wrapper,
                studentSearchSettings.saveError ||
                    'Unable to save student details right now. Please try again.',
                false
            );
        } finally {
            const editButton = panel ? panel.querySelector('[data-teqcidb-edit-student]') : null;
            const isEditing = editButton && editButton.dataset.editing === 'true';
            button.disabled = !isEditing;
            button.removeAttribute('aria-busy');
        }
    };

    const toggleStudentAccordion = (summaryRow) => {
        if (!summaryRow) {
            return;
        }

        const panelId = summaryRow.getAttribute('aria-controls');
        if (!panelId) {
            return;
        }

        const panelRow = document.getElementById(panelId);
        if (!panelRow) {
            return;
        }

        const isOpen = summaryRow.classList.contains('is-open');
        summaryRow.classList.toggle('is-open', !isOpen);
        summaryRow.setAttribute('aria-expanded', (!isOpen).toString());
        panelRow.hidden = isOpen;
        panelRow.setAttribute('aria-hidden', isOpen.toString());
    };

    const initAssignedStudents = () => {
        const assignedSection = document.querySelector('[data-teqcidb-assigned-students]');
        if (!assignedSection) {
            return;
        }

        const assignedList = assignedSection.querySelector('[data-teqcidb-assigned-list]');
        const emptyMessage = assignedSection.querySelector('[data-teqcidb-assigned-empty]');
        if (!assignedList) {
            return;
        }

        const assignedStudents = Array.isArray(studentSearchSettings.assignedStudents)
            ? studentSearchSettings.assignedStudents
            : [];
        const columnCount = (studentSearchSettings.summaryFields || []).length;

        renderStudentResults(assignedList, assignedStudents, columnCount, {
            editable: true,
        });

        if (emptyMessage) {
            if (assignedStudents.length) {
                emptyMessage.hidden = true;
            } else {
                emptyMessage.textContent =
                    studentSearchSettings.assignedEmpty ||
                    'No students are currently assigned to you.';
                emptyMessage.hidden = false;
            }
        }

        assignedList.addEventListener('click', (event) => {
            const row = event.target.closest('.teqcidb-accordion__summary-row');
            if (!row) {
                return;
            }
            toggleStudentAccordion(row);
        });

        assignedList.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            const row = event.target.closest('.teqcidb-accordion__summary-row');
            if (!row) {
                return;
            }
            event.preventDefault();
            toggleStudentAccordion(row);
        });

        assignedSection.addEventListener('click', (event) => {
            const editButton = event.target.closest('[data-teqcidb-edit-student]');
            if (editButton) {
                event.preventDefault();
                const panel = editButton.closest('.teqcidb-accordion__panel');
                if (!panel) {
                    return;
                }
                const isEditing = editButton.dataset.editing === 'true';
                const saveButton = panel.querySelector('[data-teqcidb-save-student]');
                const form = panel.querySelector('[data-teqcidb-assigned-form]');
                if (isEditing) {
                    setStudentDetailsEditable(panel, false);
                    resetStudentDetails(panel);
                    if (form) {
                        form.classList.remove('is-editing');
                    }
                    editButton.dataset.editing = 'false';
                    editButton.textContent =
                        studentSearchSettings.editLabel || 'Edit This Student';
                    if (saveButton) {
                        saveButton.disabled = true;
                    }
                } else {
                    setStudentDetailsEditable(panel, true);
                    if (form) {
                        form.classList.add('is-editing');
                    }
                    editButton.dataset.editing = 'true';
                    editButton.textContent =
                        studentSearchSettings.editCancelLabel || 'Cancel Editing';
                    if (saveButton) {
                        saveButton.disabled = false;
                    }
                }
                return;
            }

            const saveButton = event.target.closest('[data-teqcidb-save-student]');
            if (saveButton) {
                event.preventDefault();
                handleSaveStudent(saveButton);
            }
        });
    };

    initAssignedStudents();

    const countdownLabels = settings.countdownLabels || {};

    const resolveCountdownLabel = (unit, value) => {
        const labels = countdownLabels[unit] || {};
        const fallback = value === 1 ? unit.slice(0, -1) : unit;
        if (value === 1) {
            return labels.singular || fallback;
        }
        return labels.plural || `${fallback}s`;
    };

    const calculateCountdown = (targetDate, nowDate) => {
        if (!(targetDate instanceof Date) || Number.isNaN(targetDate.getTime())) {
            return null;
        }

        if (!(nowDate instanceof Date) || Number.isNaN(nowDate.getTime())) {
            return null;
        }

        if (targetDate <= nowDate) {
            return {
                expired: true,
                months: 0,
                weeks: 0,
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                totalDays: 0,
            };
        }

        const totalMs = targetDate.getTime() - nowDate.getTime();
        const totalDays = Math.floor(totalMs / (1000 * 60 * 60 * 24));

        let months =
            (targetDate.getFullYear() - nowDate.getFullYear()) * 12 +
            (targetDate.getMonth() - nowDate.getMonth());

        if (months < 0) {
            months = 0;
        }

        const anchor = new Date(nowDate.getTime());
        anchor.setMonth(anchor.getMonth() + months);

        if (anchor > targetDate) {
            months -= 1;
            anchor.setMonth(anchor.getMonth() - 1);
        }

        if (months < 0) {
            months = 0;
        }

        let remainingMs = targetDate.getTime() - anchor.getTime();
        if (remainingMs < 0) {
            remainingMs = 0;
        }

        const dayMs = 1000 * 60 * 60 * 24;
        const hourMs = 1000 * 60 * 60;
        const minuteMs = 1000 * 60;
        const secondMs = 1000;

        const daysTotal = Math.floor(remainingMs / dayMs);
        const weeks = Math.floor(daysTotal / 7);
        const days = daysTotal % 7;
        remainingMs -= daysTotal * dayMs;

        const hours = Math.floor(remainingMs / hourMs);
        remainingMs -= hours * hourMs;

        const minutes = Math.floor(remainingMs / minuteMs);
        remainingMs -= minutes * minuteMs;

        const seconds = Math.floor(remainingMs / secondMs);

        return {
            expired: false,
            months,
            weeks,
            days,
            hours,
            minutes,
            seconds,
            totalDays,
        };
    };

    const updateCountdownElement = (element) => {
        const targetValue = element.dataset.teqcidbCountdownTarget;
        if (!targetValue) {
            return;
        }

        const targetDate = new Date(targetValue);
        const nowDate = new Date();
        const countdown = calculateCountdown(targetDate, nowDate);
        if (!countdown) {
            return;
        }

        const timer = element.querySelector('[data-teqcidb-countdown-timer]');
        const expiredMessage = element.querySelector('[data-teqcidb-countdown-expired]');

        if (countdown.expired) {
            if (timer) {
                timer.setAttribute('hidden', 'hidden');
            }
            if (expiredMessage) {
                expiredMessage.removeAttribute('hidden');
            }
            element.classList.remove('is-warning');
            return;
        }

        if (timer) {
            timer.removeAttribute('hidden');
            const units = [
                'months',
                'weeks',
                'days',
                'hours',
                'minutes',
                'seconds',
            ];
            units.forEach((unit) => {
                const unitEl = timer.querySelector(
                    `[data-teqcidb-countdown-unit="${unit}"]`
                );
                if (!unitEl) {
                    return;
                }
                const value = countdown[unit];
                const label = resolveCountdownLabel(unit, value);
                unitEl.textContent = `${value} ${label}`;
                const shouldHide = unit !== 'seconds' && value === 0;
                unitEl.toggleAttribute('hidden', shouldHide);
            });
        }

        if (expiredMessage) {
            expiredMessage.setAttribute('hidden', 'hidden');
        }

        const warningDays = parseInt(
            element.dataset.teqcidbCountdownWarningDays || '45',
            10
        );
        const warningThreshold = Number.isNaN(warningDays) ? 45 : warningDays;
        element.classList.toggle(
            'is-warning',
            countdown.totalDays <= warningThreshold
        );
    };

    const initCountdown = (element) => {
        if (!element || element.dataset.teqcidbCountdownInitialized === 'true') {
            return;
        }

        element.dataset.teqcidbCountdownInitialized = 'true';
        updateCountdownElement(element);

        const intervalId = window.setInterval(() => {
            updateCountdownElement(element);
        }, 1000);
        element.dataset.teqcidbCountdownInterval = intervalId.toString();
    };

    const initCountdowns = (root = document) => {
        const countdowns = root.querySelectorAll('[data-teqcidb-countdown]');
        countdowns.forEach((element) => initCountdown(element));
    };

    window.TEQCIDBCountdown = window.TEQCIDBCountdown || {
        init: initCountdowns,
    };

    initCountdowns();

    const walletCardSettings = settings.walletCard || {};
    const registrationReceiptSettings = settings.registrationReceipt || {};

    const loadWalletCardImage = (url) => {
        if (!url) {
            return Promise.resolve(null);
        }

        return fetch(url)
            .then((response) => response.blob())
            .then(
                (blob) =>
                    new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = () => resolve(null);
                        reader.readAsDataURL(blob);
                    })
            )
            .catch(() => null);
    };

    const getWalletCardValue = (value) => {
        const emptyValue = walletCardSettings.emptyValue || '—';
        if (!value || (typeof value === 'string' && !value.trim())) {
            return emptyValue;
        }
        return value;
    };

    const parseWalletCardData = (element) => {
        if (!element) {
            return null;
        }

        const rawData = element.dataset.teqcidbWalletCard;
        if (!rawData) {
            return null;
        }

        try {
            return JSON.parse(rawData);
        } catch (error) {
            return null;
        }
    };

    const renderWalletCardPdf = async (data) => {
        const jspdf = window.jspdf || {};
        const { jsPDF } = jspdf;
        if (!jsPDF) {
            throw new Error('missing-js-pdf');
        }

        const [ademLogo, thompsonLogo] = await Promise.all([
            loadWalletCardImage(walletCardSettings.ademLogoUrl),
            loadWalletCardImage(walletCardSettings.thompsonLogoUrl),
        ]);

        const doc = new jsPDF({ unit: 'in', format: 'letter' });
        const pageWidth = 8.5;
        const cardWidth = 3.375;
        const cardHeight = 2.125;
        const gap = 0.5;
        const startX = (pageWidth - cardWidth) / 2;
        const frontY = 1.0;
        const backY = frontY + cardHeight + gap;

        const drawCardBorder = (y) => {
            doc.setLineWidth(0.01);
            doc.rect(startX, y, cardWidth, cardHeight);
        };

        const drawCenteredText = (text, y, size, style = 'normal') => {
            doc.setFont('times', style);
            doc.setFontSize(size);
            doc.text(text, startX + cardWidth / 2, y, { align: 'center' });
        };

        drawCardBorder(frontY);

        if (ademLogo) {
            const logoWidth = 1.0;
            const logoHeight = 0.33;
            doc.addImage(
                ademLogo,
                'JPEG',
                startX + (cardWidth - logoWidth) / 2,
                frontY + 0.18,
                logoWidth,
                logoHeight
            );
        }

        drawCenteredText(walletCardSettings.qualifiedLabel || '', frontY + 0.8, 9);
        drawCenteredText(getWalletCardValue(data.name), frontY + 0.98, 11, 'bold');
        drawCenteredText(getWalletCardValue(data.company), frontY + 1.17, 9, 'bold');
        drawCenteredText(
            `${walletCardSettings.qciNumberLabel || 'QCI No.'} ${getWalletCardValue(data.qci_number)}`,
            frontY + 1.34,
            9
        );

        doc.setFont('times', 'normal');
        doc.setFontSize(8);

        const leftCenterX = startX + cardWidth * 0.25;
        const rightCenterX = startX + cardWidth * 0.75;
        const baseY = frontY + 1.53;
        const lineHeight = 0.14;

        const addressLines = [
            data.address_line_1,
            data.address_line_2,
            data.phone,
            data.email,
        ]
            .map((line) => (line ? line.trim() : ''))
            .filter((line) => line.length);

        addressLines.forEach((line, index) => {
            doc.text(line, leftCenterX, baseY + index * lineHeight, { align: 'center' });
        });

        const rightLines = [
            `${walletCardSettings.expirationLabel || 'Expiration Date'}: ${getWalletCardValue(data.expiration_date)}`,
            `${walletCardSettings.initialTrainingLabel || 'Initial Training'}: ${getWalletCardValue(data.initial_training_date)}`,
            `${walletCardSettings.mostRecentLabel || 'Most Recent Annual Update'}:`,
            getWalletCardValue(data.last_refresher_date),
        ];

        rightLines.forEach((line, index) => {
            doc.text(line, rightCenterX, baseY + index * lineHeight, { align: 'center' });
        });

        drawCardBorder(backY);
        drawCenteredText(walletCardSettings.backTitle || '', backY + 0.38, 9, 'bold');

        doc.setFont('times', 'normal');
        doc.setFontSize(7.5);

        const bulletX = startX + cardWidth / 2;
        let bulletY = backY + 0.6;
        const bulletWidth = cardWidth - 0.3;
        const bulletLineHeight = 0.14;
        const bullets = walletCardSettings.backBullets || [];
        bullets.forEach((bullet) => {
            const lines = doc.splitTextToSize(`• ${bullet}`, bulletWidth);
            lines.forEach((line) => {
                doc.text(line, bulletX, bulletY, { align: 'center' });
                bulletY += bulletLineHeight;
            });
            bulletY += 0.05;
        });

        if (thompsonLogo) {
            const logoWidth = 0.52;
            const { width: imageWidth, height: imageHeight } = doc.getImageProperties(thompsonLogo);
            const logoHeight = imageWidth ? (logoWidth * imageHeight) / imageWidth : logoWidth;
            doc.addImage(
                thompsonLogo,
                'JPEG',
                startX + (cardWidth - logoWidth) / 2,
                Math.max(backY + cardHeight - logoHeight - 0.16, bulletY + 0.05),
                logoWidth,
                logoHeight
            );
        }

        return doc;
    };


    const registrationSections = document.querySelectorAll('[data-teqcidb-registration="true"]');
    let activeRegistrationCheckout = null;

    const setPaymentFeedback = (paymentWrapper, message, isLoading, options = {}) => {
        if (!paymentWrapper) {
            return;
        }

        const feedback = paymentWrapper.querySelector('.teqcidb-registration-payment-feedback');
        if (!feedback) {
            return;
        }

        const messageEl = feedback.querySelector('.teqcidb-form-message');
        if (messageEl) {
            if (options.allowHtml) {
                messageEl.innerHTML = message || '';
            } else {
                messageEl.textContent = message || '';
            }
        }

        feedback.classList.toggle('is-visible', Boolean(message) || Boolean(isLoading));
        feedback.classList.toggle('is-loading', Boolean(isLoading));
    };

    const parseIframeCommunication = (payload) => {
        if (!payload || typeof payload !== 'string') {
            return new URLSearchParams();
        }

        const normalized = payload.charAt(0) === '?' ? payload.substring(1) : payload;
        return new URLSearchParams(normalized);
    };

    const escapeHtml = (value) => {
        const html = value === null || value === undefined ? '' : String(value);

        return html
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const formatRegistrationDate = (date = new Date()) => {
        const month = `${date.getMonth() + 1}`.padStart(2, '0');
        const day = `${date.getDate()}`.padStart(2, '0');
        const year = date.getFullYear();

        return `${month}/${day}/${year}`;
    };

    const buildRegistrationReceiptData = (checkout, response = {}) => ({
        className: checkout && checkout.className ? String(checkout.className) : 'N/A',
        registrationDate: formatRegistrationDate(),
        paymentAmount: response && response.totalAmount ? String(response.totalAmount) : 'N/A',
        transactionId: response && response.transId ? String(response.transId) : 'N/A',
        invoiceNumber: response && response.orderInvoiceNumber ? String(response.orderInvoiceNumber) : 'N/A',
    });

    const renderRegistrationReceiptPdf = async (receiptData) => {
        const jspdf = window.jspdf || {};
        const { jsPDF } = jspdf;

        if (!jsPDF) {
            throw new Error('missing-js-pdf');
        }

        const logoData = await loadWalletCardImage(registrationReceiptSettings.logoUrl || '');

        const doc = new jsPDF({ unit: 'pt', format: 'letter' });
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const margin = 54;
        const contentWidth = pageWidth - margin * 2;
        let y = margin;

        if (logoData) {
            const img = doc.getImageProperties(logoData);
            const logoWidth = 210;
            const logoHeight = img.width ? (logoWidth * img.height) / img.width : 54;
            doc.addImage(logoData, img.fileType || 'PNG', (pageWidth - logoWidth) / 2, y, logoWidth, logoHeight);
            y += logoHeight + 20;
        }

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(19);
        doc.text('Registration Payment Receipt', pageWidth / 2, y, { align: 'center' });
        y += 24;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        const intro = doc.splitTextToSize(
            'Your class registration & payment are successful! Please retain this receipt for your records.',
            contentWidth
        );
        doc.text(intro, margin, y);
        y += intro.length * 14 + 12;

        doc.setLineWidth(0.8);
        doc.setDrawColor(207, 207, 207);
        doc.rect(margin, y, contentWidth, 114);

        y += 20;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.text('Transaction Details', margin + 12, y);
        y += 18;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        const details = [
            `Class Name: ${receiptData.className}`,
            `Date of Registration & Payment: ${receiptData.registrationDate}`,
            `Payment Amount: ${receiptData.paymentAmount}`,
            `Transaction ID: ${receiptData.transactionId}`,
            `Invoice Number: ${receiptData.invoiceNumber}`,
        ];
        details.forEach((line) => {
            doc.text(line, margin + 12, y);
            y += 16;
        });

        y += 10;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.text("What's Next?", margin, y);
        y += 18;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        const nextSteps = doc.splitTextToSize(
            "Check your email - you should have received information about how to access your class. If you don't see an email, please check all junk and spam folders. If you still don't see an email, please contact Ilka Porter at (251) 666-2443, or QCI@thompsonengineering.com for more info.",
            contentWidth
        );
        doc.text(nextSteps, margin, y);
        y += nextSteps.length * 14 + 20;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10);
        doc.text('Cancellation & Payment Policy', margin, y);
        y += 14;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        const policyParagraphs = [
            'Registration fees for in-person classes and online courses are non-refundable. Payment is requested prior to or on the date of the training. In certain situations, we may issue credits that are good for one year from the original (initial) training date. These credits may be transferable to another employee of the same company/organization. We do not issue credits for online refresher training fees.',
            'Certificates of completion and QCI numbers issued upon completion of training and receipt of payment.',
            'For more information or clarification, please call (251) 666-2443.',
        ];

        policyParagraphs.forEach((paragraph) => {
            const lines = doc.splitTextToSize(paragraph, contentWidth);
            if (y + lines.length * 12 > pageHeight - margin) {
                doc.addPage();
                y = margin;
            }
            doc.text(lines, margin, y);
            y += lines.length * 12 + 8;
        });

        const fileName = registrationReceiptSettings.downloadFileName || 'qci-registration-receipt.pdf';
        doc.save(fileName);
    };

    const buildRegistrationSuccessMessage = (receiptData = {}) => {
        const className = escapeHtml(receiptData.className || 'N/A');
        const registrationDate = escapeHtml(receiptData.registrationDate || 'N/A');
        const paymentAmount = escapeHtml(receiptData.paymentAmount || 'N/A');
        const transactionId = escapeHtml(receiptData.transactionId || 'N/A');
        const invoiceNumber = escapeHtml(receiptData.invoiceNumber || 'N/A');

        return `
            <p>
                Your class registration &amp; payment are successful! Below are your details.
                <a href="#" data-teqcidb-receipt-download-link>Click here to download and save a copy of this transaction.</a>
            </p>
            <p class="teqcidb-registration-payment-success-details">
                Class Name: ${className}<br>
                Date of Registration &amp; Payment: ${registrationDate}<br>
                Payment Amount: ${paymentAmount}<br>
                Transaction ID: ${transactionId}<br>
                Invoice Number: ${invoiceNumber}
            </p>
            <p class="teqcidb-registration-payment-success-next-title">What's Next?</p>
            <p>
                Check your email - you should have received information about how to access your class. If you don't see an email, please check all junk and spam folders. If you <strong><em>still</em></strong> don't see an email, please contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a>, or <a href="mailto:QCI@thompsonengineering.com">QCI@thompsonengineering.com</a> for more info.
            </p>
        `;
    };

    const hideRegistrationPaymentIframe = (paymentWrapper, paymentIframe) => {
        if (!paymentWrapper || !paymentIframe || !paymentIframe.classList.contains('is-visible')) {
            return;
        }

        const classPanel = paymentWrapper.closest('.teqcidb-registration-class-panel');
        if (classPanel) {
            classPanel.style.maxHeight = `${classPanel.scrollHeight}px`;
        }

        paymentIframe.classList.add('is-fading-out');

        const completeHide = () => {
            paymentIframe.classList.remove('is-visible', 'is-fading-out');

            if (classPanel) {
                classPanel.style.maxHeight = `${classPanel.scrollHeight}px`;

                window.setTimeout(() => {
                    classPanel.style.maxHeight = '';
                }, 840);
            }
        };

        window.setTimeout(completeHide, 760);
    };


    if (registrationSections.length) {
        window.AuthorizeNetIFrame = window.AuthorizeNetIFrame || {};
        const previousOnReceiveCommunication =
            typeof window.AuthorizeNetIFrame.onReceiveCommunication === 'function'
                ? window.AuthorizeNetIFrame.onReceiveCommunication
                : null;

        window.AuthorizeNetIFrame.onReceiveCommunication = (payload) => {

            if (previousOnReceiveCommunication) {
                previousOnReceiveCommunication(payload);
            }
            

            if (!activeRegistrationCheckout) {
                return;
            }

            const params = parseIframeCommunication(payload);
            const action = params.get('action') || '';

            const responseRaw = params.get('response') || '';
            let response = {};

            if (responseRaw) {
                try {
                    response = JSON.parse(responseRaw) || {};
                } catch (error) {
                    response = {};
                }
            }

            if (!action) {
                return;
            }

            const { paymentWrapper, paymentIframe } = activeRegistrationCheckout;

            if (action === 'resizeWindow') {
                const iframeHeight = parseInt(params.get('height') || '', 10);

                if (Number.isFinite(iframeHeight) && iframeHeight > 0 && paymentIframe) {
                    paymentIframe.style.height = `${Math.max(iframeHeight, 480)}px`;
                }

                return;
            }

            if (action === 'cancel') {
                setPaymentFeedback(
                    paymentWrapper,
                    settings.messagePaymentCancelled ||
                        'Payment was canceled before completion.',
                    false
                );
                activeRegistrationCheckout = null;
                return;
            }

            if (action === 'transactResponse') {
                const responseCode = response && response.responseCode ? String(response.responseCode) : "";
                const responseReasonText = (params.get('responseReasonText') || '').trim();

                if (responseCode === '1') {
                    const receiptData = buildRegistrationReceiptData(activeRegistrationCheckout, response);
                    paymentWrapper.dataset.teqcidbReceiptData = JSON.stringify(receiptData);
                    const successMessage = buildRegistrationSuccessMessage(receiptData);
                    setPaymentFeedback(paymentWrapper, successMessage, false, { allowHtml: true });
                    hideRegistrationPaymentIframe(paymentWrapper, paymentIframe);
                } else {
                    const failedMessage = responseReasonText ||
                        settings.messagePaymentFailed ||
                        'Payment could not be completed. Please verify your payment details and try again.';
                    setPaymentFeedback(paymentWrapper, failedMessage, false);
                }

                activeRegistrationCheckout = null;
            }
        };
    }

    registrationSections.forEach((section) => {
        const toggles = Array.from(
            section.querySelectorAll('.teqcidb-registration-class-toggle')
        );

        const setExpanded = (toggle, expanded) => {
            const panelId = toggle.getAttribute('aria-controls');
            if (!panelId) {
                return;
            }

            const panel = section.querySelector(`#${panelId}`);
            if (!panel) {
                return;
            }

            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.classList.toggle('is-active', expanded);
            panel.hidden = !expanded;
        };

        const requestHostedToken = (classId) => {
            const formData = new FormData();
            formData.append('action', settings.ajaxTokenAction || 'teqcidb_get_accept_hosted_token');
            formData.append('_ajax_nonce', settings.ajaxNonce || '');
            formData.append('class_id', classId);

            return fetch(settings.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            }).then(async (response) => {
                const bodyText = await response.text();

                try {
                    return JSON.parse(bodyText);
                } catch (error) {
                    if (!response.ok) {
                        throw new Error(
                            settings.messagePaymentError ||
                                'Unable to load the payment form right now. Please try again.'
                        );
                    }

                    throw error;
                }
            });
        };

        toggles.forEach((toggle) => {
            setExpanded(toggle, false);
            toggle.addEventListener('click', () => {
                const currentlyExpanded =
                    toggle.getAttribute('aria-expanded') === 'true';

                toggles.forEach((item) => {
                    if (item !== toggle) {
                        setExpanded(item, false);
                    }
                });

                setExpanded(toggle, !currentlyExpanded);
            });
        });

        const paymentButtons = Array.from(
            section.querySelectorAll('[data-teqcidb-registration-pay]')
        );

        section.addEventListener('click', (event) => {
            const receiptLink = event.target.closest('[data-teqcidb-receipt-download-link]');
            if (!receiptLink) {
                return;
            }

            event.preventDefault();

            const paymentWrapper = receiptLink.closest('[data-teqcidb-registration-payment]');
            const rawReceiptData = paymentWrapper ? paymentWrapper.dataset.teqcidbReceiptData || '' : '';

            if (!rawReceiptData) {
                return;
            }

            let receiptData = null;

            try {
                receiptData = JSON.parse(rawReceiptData);
            } catch (error) {
                receiptData = null;
            }

            if (!receiptData) {
                return;
            }

            renderRegistrationReceiptPdf(receiptData).catch(() => {
                setPaymentFeedback(
                    paymentWrapper,
                    registrationReceiptSettings.missingPdfMessage ||
                        'Unable to generate the transaction receipt right now. Please try again.',
                    false
                );
            });
        });

        paymentButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const hasCredentials = section.dataset.authorizenetHasCredentials === 'yes';
                const classId = button.dataset.classId || '';
                const paymentWrapper = button.closest('[data-teqcidb-registration-payment]');
                const paymentForm = paymentWrapper
                    ? paymentWrapper.querySelector('[data-teqcidb-registration-payment-form]')
                    : null;
                const tokenInput = paymentWrapper
                    ? paymentWrapper.querySelector('[data-teqcidb-registration-token]')
                    : null;
                const paymentIframe = paymentWrapper
                    ? paymentWrapper.querySelector('[data-teqcidb-registration-iframe]')
                    : null;
                const classPanel = button.closest('.teqcidb-registration-class-panel');
                const classTitleElement = classPanel
                    ? classPanel.querySelector('.teqcidb-registration-class-section-title')
                    : null;
                const className = classTitleElement ? classTitleElement.textContent.trim() : '';

                if (!settings.ajaxUrl || !hasCredentials || !classId || !paymentForm || !tokenInput || !paymentIframe) {
                    setPaymentFeedback(
                        paymentWrapper,
                        settings.messagePaymentUnavailable ||
                            'Online checkout is unavailable right now. Please contact Thompson Engineering for payment assistance.',
                        false
                    );
                    return;
                }

                setPaymentFeedback(
                    paymentWrapper,
                    settings.messagePaymentLoading || 'Loading secure payment form...',
                    true
                );

                button.disabled = true;
                button.setAttribute('aria-busy', 'true');

                requestHostedToken(classId)
                    .then((payload) => {
                        if (!payload || !payload.success || !payload.data || !payload.data.token) {
                            const message =
                                payload && payload.data && (payload.data.message || payload.data.error)
                                    ? payload.data.message || payload.data.error
                                    : settings.messagePaymentError ||
                                      'Unable to load the payment form right now. Please try again.';
                            throw new Error(message);
                        }

                        if (payload.data.postUrl) {
                            paymentForm.setAttribute('action', payload.data.postUrl);
                        }

                        tokenInput.value = payload.data.token;
                        paymentIframe.classList.remove('is-fading-out');
                        paymentIframe.classList.add('is-visible');
                        activeRegistrationCheckout = {
                            classId,
                            className,
                            paymentWrapper,
                            paymentIframe,
                        };
                        paymentForm.submit();

                        setPaymentFeedback(paymentWrapper, '', false);
                    })
                    .catch((error) => {
                        setPaymentFeedback(
                            paymentWrapper,
                            error && error.message
                                ? error.message
                                : settings.messagePaymentError ||
                                  'Unable to load the payment form right now. Please try again.',
                            false
                        );
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                    });
            });
        });
    });

    const handleWalletCardAction = async (event) => {
        const button = event.target.closest('[data-teqcidb-wallet-card-action]');
        if (!button) {
            return;
        }

        const action = button.dataset.teqcidbWalletCardAction;
        const wrapper = button.closest('[data-teqcidb-wallet-card]');
        const data = parseWalletCardData(wrapper);

        if (!data) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        try {
            const doc = await renderWalletCardPdf(data);
            if (action === 'print') {
                doc.autoPrint();
                doc.output('dataurlnewwindow');
            } else {
                doc.save(walletCardSettings.downloadFileName || 'wallet-card.pdf');
            }
        } catch (error) {
            const message =
                walletCardSettings.missingPdfMessage ||
                'Unable to generate the wallet card right now. Please try again.';
            window.alert(message);
        } finally {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        }
    };

    document.addEventListener('click', handleWalletCardAction);
})();
