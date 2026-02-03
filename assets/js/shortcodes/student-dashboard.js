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
})();
