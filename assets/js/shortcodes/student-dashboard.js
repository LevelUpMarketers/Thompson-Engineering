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
})();
