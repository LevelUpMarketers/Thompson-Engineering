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
            field.disabled = disabled;
        });
    };

    const collectProfileFormData = (form) => {
        const data = new FormData();
        const getValue = (selector) => {
            const field = form.querySelector(selector);
            return field ? field.value.trim() : '';
        };

        const originalCompany = form.dataset.originalCompany || '';
        const updatedCompany = getValue('#teqcidb-profile-company');

        data.append('action', settings.profileUpdateAction || 'teqcidb_update_profile');
        data.append('_ajax_nonce', settings.ajaxNonce || '');
        data.append('first_name', getValue('#teqcidb-profile-first-name'));
        data.append('last_name', getValue('#teqcidb-profile-last-name'));
        data.append('company', getValue('#teqcidb-profile-company'));
        data.append('phone_cell', getValue('#teqcidb-profile-cell-phone'));
        data.append('phone_office', getValue('#teqcidb-profile-office-phone'));
        data.append('email', getValue('#teqcidb-profile-email'));
        data.append('student_address_street_1', getValue('#teqcidb-profile-street-address'));
        data.append('student_address_street_2', getValue('#teqcidb-profile-street-address-2'));
        data.append('student_address_city', getValue('#teqcidb-profile-city'));
        data.append('student_address_state', getValue('#teqcidb-profile-state'));
        data.append('student_address_postal_code', getValue('#teqcidb-profile-zip'));
        data.append('representative_first_name', getValue('#teqcidb-profile-rep-first-name'));
        data.append('representative_last_name', getValue('#teqcidb-profile-rep-last-name'));
        data.append('representative_email', getValue('#teqcidb-profile-rep-email'));
        data.append('representative_phone', getValue('#teqcidb-profile-rep-phone'));

        form.querySelectorAll('input[name="teqcidb_profile_associations[]"]:checked')
            .forEach((input) => {
                data.append('associations[]', input.value);
            });

        const oldCompanies = [];
        form.querySelectorAll('input[name="teqcidb_profile_old_companies[]"]')
            .forEach((input) => {
                const value = input.value.trim();
                if (value) {
                    oldCompanies.push(value);
                }
            });

        if (
            originalCompany &&
            updatedCompany &&
            originalCompany.toLowerCase() !== updatedCompany.toLowerCase()
        ) {
            const exists = oldCompanies.some(
                (value) => value.toLowerCase() === originalCompany.toLowerCase()
            );
            if (!exists) {
                oldCompanies.push(originalCompany);
            }
        }

        oldCompanies.forEach((value) => {
            data.append('old_companies[]', value);
        });

        return data;
    };

    const handleProfileSave = (form, fields, editButton, saveButton, snapshotRef) => {
        const requiredSelectors = [
            '#teqcidb-profile-first-name',
            '#teqcidb-profile-last-name',
            '#teqcidb-profile-company',
            '#teqcidb-profile-email',
        ];
        const companyField = form.querySelector('#teqcidb-profile-company');
        const addOldCompanyButton = form.querySelector('[data-teqcidb-add-old-company]');

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
                    editButton.dataset.editing = 'false';
                    editButton.textContent =
                        settings.profileEditLabel || 'Edit Profile Info';
                    saveButton.disabled = true;
                    if (addOldCompanyButton) {
                        addOldCompanyButton.disabled = true;
                    }
                    if (companyField) {
                        form.dataset.originalCompany = companyField.value.trim();
                    }
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
        const addOldCompanyButton = form.querySelector('[data-teqcidb-add-old-company]');
        const oldCompaniesGrid = form.querySelector('[data-teqcidb-old-companies]');
        const fields = Array.from(
            form.querySelectorAll('input, select, textarea')
        );

        if (!editButton || !saveButton || !fields.length) {
            return;
        }

        const snapshotRef = { current: getProfileSnapshot(fields) };
        const companyField = form.querySelector('#teqcidb-profile-company');
        if (companyField) {
            form.dataset.originalCompany = companyField.value.trim();
        }
        if (addOldCompanyButton) {
            addOldCompanyButton.disabled = true;
        }

        editButton.addEventListener('click', () => {
            const isEditing = editButton.dataset.editing === 'true';

            if (isEditing) {
                restoreProfileSnapshot(snapshotRef.current);
                setProfileFieldsDisabled(fields, true);
                editButton.dataset.editing = 'false';
                editButton.textContent =
                    settings.profileEditLabel || 'Edit Profile Info';
                saveButton.disabled = true;
                if (addOldCompanyButton) {
                    addOldCompanyButton.disabled = true;
                }
                showFeedback(form, '', false);
                return;
            }

            setProfileFieldsDisabled(fields, false);
            editButton.dataset.editing = 'true';
            editButton.textContent =
                settings.profileCancelLabel || 'Cancel Editing';
            saveButton.disabled = false;
            if (addOldCompanyButton) {
                addOldCompanyButton.disabled = false;
            }
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

        if (addOldCompanyButton && oldCompaniesGrid) {
            addOldCompanyButton.addEventListener('click', () => {
                const countValue = parseInt(
                    oldCompaniesGrid.dataset.oldCompanyCount || '0',
                    10
                );
                const nextIndex = Number.isNaN(countValue) ? 1 : countValue + 1;
                oldCompaniesGrid.dataset.oldCompanyCount = nextIndex.toString();

                const wrapper = document.createElement('div');
                wrapper.className = 'teqcidb-form-field';

                const label = document.createElement('label');
                label.className = 'screen-reader-text';
                label.setAttribute('for', `teqcidb-profile-old-company-${nextIndex}`);
                const labelBase =
                    settings.oldCompanyLabel || 'Previous Company';
                label.textContent = `${labelBase} ${nextIndex}`;

                const input = document.createElement('input');
                input.type = 'text';
                input.id = `teqcidb-profile-old-company-${nextIndex}`;
                input.name = 'teqcidb_profile_old_companies[]';
                input.autocomplete = 'organization';

                wrapper.appendChild(label);
                wrapper.appendChild(input);
                oldCompaniesGrid.appendChild(wrapper);

                const emptyMessage = form.querySelector(
                    '.teqcidb-profile-old-companies .teqcidb-dashboard-empty'
                );
                if (emptyMessage) {
                    emptyMessage.remove();
                }
            });
        }
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
        historyEntries.forEach((entry, index) => {
            const card = document.createElement('article');
            card.className = 'teqcidb-student-history-card';

            const cardHeading = document.createElement('h5');
            cardHeading.className = 'teqcidb-student-history-title';
            const titleTemplate =
                studentSearchSettings.historyEntryTitle || 'History Entry %s';
            cardHeading.textContent = titleTemplate.replace(
                '%s',
                (index + 1).toString()
            );
            card.appendChild(cardHeading);

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
                value.textContent = formatStudentValue(entry[field.key], field.key);

                row.appendChild(label);
                row.appendChild(value);
                cardGrid.appendChild(row);
            });

            card.appendChild(cardGrid);
            historyList.appendChild(card);
        });

        historyWrapper.appendChild(historyList);
        return historyWrapper;
    };

    const renderStudentResults = (tbody, entities, columnCount) => {
        tbody.innerHTML = '';

        if (!entities.length) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'no-items';
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = columnCount;
            emptyCell.textContent =
                studentSearchSettings.searchNoResults ||
                'No matching students were found.';
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
                    const nameLabel =
                        studentSearchSettings.summaryNameLabel || 'Name';
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

            const actionsCell = document.createElement('td');
            actionsCell.className =
                'teqcidb-accordion__cell teqcidb-accordion__cell--actions';
            const actionLabel = document.createElement('span');
            actionLabel.className = 'teqcidb-accordion__action-link';
            actionLabel.setAttribute('aria-hidden', 'true');
            actionLabel.textContent =
                studentSearchSettings.toggleDetails || 'Details';
            const icon = document.createElement('span');
            icon.className =
                'dashicons dashicons-arrow-down-alt2 teqcidb-accordion__icon';
            icon.setAttribute('aria-hidden', 'true');
            const srText = document.createElement('span');
            srText.className = 'screen-reader-text';
            srText.textContent =
                studentSearchSettings.toggleDetails || 'Toggle student details';
            actionsCell.appendChild(actionLabel);
            actionsCell.appendChild(icon);
            actionsCell.appendChild(srText);
            summaryRow.appendChild(actionsCell);
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

            panel.appendChild(buildStudentDetails(entity));
            panel.appendChild(buildStudentHistory(entity));

            panelCell.appendChild(panel);
            panelRow.appendChild(panelCell);
            tbody.appendChild(panelRow);
        });
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

    const fetchStudentPage = async (filters, page, perPage) => {
        const data = new FormData();
        data.append('action', studentSearchSettings.action || 'teqcidb_read_student');
        data.append('_ajax_nonce', settings.ajaxNonce || '');
        data.append('page', page.toString());
        data.append('per_page', perPage.toString());
        data.append('search[placeholder_1]', filters.name || '');
        data.append('search[placeholder_2]', filters.email || '');
        data.append('search[placeholder_3]', filters.company || '');

        const response = await fetch(settings.ajaxUrl || '', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('student-search-failed');
        }

        const payload = await response.json();
        if (!payload || !payload.success || !payload.data) {
            throw new Error('student-search-failed');
        }

        return payload.data;
    };

    const fetchAllStudents = async (filters) => {
        const perPage = studentSearchSettings.perPage || 50;
        let page = 1;
        let totalPages = 1;
        const allEntities = [];

        while (page <= totalPages) {
            const data = await fetchStudentPage(filters, page, perPage);
            if (data && Array.isArray(data.entities)) {
                allEntities.push(...data.entities);
            }
            totalPages = data && data.total_pages ? data.total_pages : 1;
            page += 1;
        }

        return allEntities;
    };

    const initStudentSearch = (form) => {
        const results = form
            ? form.closest('.teqcidb-dashboard-section--students')
                ?.querySelector('[data-teqcidb-student-results]')
            : null;
        if (!results) {
            return;
        }

        const tbody = results.querySelector('[data-teqcidb-student-list]');
        const emptyMessage = results.querySelector('[data-teqcidb-student-empty]');
        if (!tbody) {
            return;
        }

        const columnCount = (studentSearchSettings.summaryFields || []).length + 1;

        const feedback = form.querySelector('.teqcidb-form-feedback');
        const feedbackMessage = feedback
            ? feedback.querySelector('.teqcidb-form-message')
            : null;

        const setFeedback = (message, isLoading) => {
            if (!feedback) {
                return;
            }
            if (feedbackMessage) {
                feedbackMessage.textContent = message || '';
            }
            feedback.classList.toggle('is-visible', Boolean(message) || isLoading);
            feedback.classList.toggle('is-loading', Boolean(isLoading));
        };

        const showResults = () => {
            results.classList.add('is-visible');
            results.setAttribute('aria-hidden', 'false');
        };

        const hideResults = () => {
            results.classList.remove('is-visible');
            results.setAttribute('aria-hidden', 'true');
        };

        const runSearch = async () => {
            showResults();
            setFeedback('', true);
            if (emptyMessage) {
                emptyMessage.hidden = true;
            }

            const filters = {
                name:
                    form.querySelector('#teqcidb-student-search-name')?.value.trim() ||
                    '',
                email:
                    form.querySelector('#teqcidb-student-search-email')?.value.trim() ||
                    '',
                company:
                    form.querySelector('#teqcidb-student-search-company')?.value.trim() ||
                    '',
            };

            try {
                const entities = await fetchAllStudents(filters);
                renderStudentResults(tbody, entities, columnCount);
                setFeedback('', false);
                if (!entities.length && emptyMessage) {
                    emptyMessage.textContent =
                        studentSearchSettings.searchNoResults ||
                        'No matching students were found.';
                    emptyMessage.hidden = false;
                }
            } catch (error) {
                setFeedback(
                    studentSearchSettings.searchError ||
                        'Unable to load students right now. Please try again.',
                    false
                );
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            runSearch();
        });

        const clearButton = form.querySelector(
            '[data-teqcidb-student-search-clear]'
        );
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                form.reset();
                tbody.innerHTML = '';
                if (emptyMessage) {
                    emptyMessage.textContent =
                        studentSearchSettings.searchEmpty ||
                        'Search for students to view their details.';
                    emptyMessage.hidden = false;
                }
                setFeedback('', false);
                hideResults();
            });
        }

        tbody.addEventListener('click', (event) => {
            const row = event.target.closest('.teqcidb-accordion__summary-row');
            if (!row) {
                return;
            }
            toggleStudentAccordion(row);
        });

        tbody.addEventListener('keydown', (event) => {
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

        if (emptyMessage) {
            emptyMessage.textContent =
                studentSearchSettings.searchEmpty ||
                'Search for students to view their details.';
            emptyMessage.hidden = false;
        }

        hideResults();
    };

    document
        .querySelectorAll('[data-teqcidb-student-search]')
        .forEach((form) => initStudentSearch(form));

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
