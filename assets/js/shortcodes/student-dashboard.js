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

})();
