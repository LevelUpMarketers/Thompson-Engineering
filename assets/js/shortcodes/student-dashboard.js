(function () {
    const settings = window.teqcidbStudentDashboard || {};

    const strengthMessages = {
        empty: settings.strengthEmpty || 'Strength: ',
        weak: settings.strengthWeak || 'Strength: weak',
        good: settings.strengthGood || 'Strength: good',
        strong: settings.strengthStrong || 'Strength: strong',
    };

    const updateStrength = (input) => {
        const indicator = document.querySelector(
            `[data-teqcidb-strength-for="${input.id}"]`
        );

        if (!indicator) {
            return;
        }

        if (!input.value) {
            indicator.textContent = strengthMessages.empty;
            return;
        }

        if (!window.wp || !window.wp.passwordStrength) {
            indicator.textContent = strengthMessages.good;
            return;
        }

        const strength = window.wp.passwordStrength.meter(
            input.value,
            [],
            input.value
        );

        if (strength >= 4) {
            indicator.textContent = strengthMessages.strong;
        } else if (strength >= 3) {
            indicator.textContent = strengthMessages.good;
        } else {
            indicator.textContent = strengthMessages.weak;
        }
    };

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

    const passwordInputs = document.querySelectorAll(
        '#teqcidb-create-password, #teqcidb-create-verify-password'
    );

    passwordInputs.forEach((input) => {
        updateStrength(input);
        input.addEventListener('input', () => updateStrength(input));
    });
})();
