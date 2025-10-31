/**
 * Settings Page Toggle Logic
 * Enables/disables the Max log entries field based on Enable logging checkbox state
 */
(function() {
    'use strict';

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const logEnabledCheckbox = document.getElementById('ew_log_enabled');
        const logLimitInput = document.getElementById('ew_log_limit_input');
        
        if (!logEnabledCheckbox || !logLimitInput) {
            return;
        }

        // Toggle function
        function toggleLogLimit() {
            logLimitInput.disabled = !logEnabledCheckbox.checked;
        }

        // Listen for changes
        logEnabledCheckbox.addEventListener('change', toggleLogLimit);

        // Initial state
        toggleLogLimit();
    }
})();
