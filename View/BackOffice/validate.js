/**
 * validate.js
 * BackOffice form validation — replaces HTML5 native input controls
 * with custom JavaScript validation + styled error messages.
 */

const Validator = {
    /**
     * Show an inline error under a field
     */
    showError(field, message) {
        this.clearError(field);
        field.classList.add('input-error');

        const errorEl = document.createElement('span');
        errorEl.className = 'field-error-msg';
        errorEl.textContent = message;

        // Insert after the field (or after its parent if inside a wrapper)
        const parent = field.closest('.form-group') || field.parentElement;
        parent.appendChild(errorEl);
    },

    /**
     * Clear error from a field
     */
    clearError(field) {
        field.classList.remove('input-error');
        const parent = field.closest('.form-group') || field.parentElement;
        const existing = parent.querySelector('.field-error-msg');
        if (existing) existing.remove();
    },

    /**
     * Clear all errors in a form
     */
    clearAllErrors(form) {
        form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
        form.querySelectorAll('.field-error-msg').forEach(el => el.remove());
    },

    /**
     * RULES — define per-form validation
     * Each rule: { field: '#id', check: fn(value, el) => string|null }
     * Return null = valid, string = error message
     */

    // ─── Required field ───
    required(value, label) {
        if (!value || value.trim() === '') {
            return `${label} is required.`;
        }
        return null;
    },

    // ─── Min length ───
    minLength(value, min, label) {
        if (value.trim().length < min) {
            return `${label} must be at least ${min} characters.`;
        }
        return null;
    },

    // ─── Max length ───
    maxLength(value, max, label) {
        if (value.trim().length > max) {
            return `${label} must not exceed ${max} characters.`;
        }
        return null;
    },

    // ─── Numeric range ───
    numberRange(value, min, max, label) {
        const num = parseFloat(value);
        if (isNaN(num)) {
            return `${label} must be a valid number.`;
        }
        if (min !== null && num < min) {
            return `${label} must be at least ${min}.`;
        }
        if (max !== null && num > max) {
            return `${label} must not exceed ${max}.`;
        }
        return null;
    },

    // ─── Positive number ───
    positiveNumber(value, label) {
        const num = parseFloat(value);
        if (isNaN(num) || num < 0) {
            return `${label} must be a positive number.`;
        }
        return null;
    },

    // ─── Select not empty ───
    selectRequired(value, label) {
        if (!value || value === '') {
            return `Please select a ${label}.`;
        }
        return null;
    },

    // ─── File type check ───
    fileType(fileInput, allowedTypes, label) {
        if (fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const ext = file.name.split('.').pop().toLowerCase();
            const allowed = allowedTypes.map(t => t.toLowerCase());
            if (!allowed.includes(ext)) {
                return `${label}: Only ${allowedTypes.join(', ')} files are allowed.`;
            }
        }
        return null;
    },

    // ─── Date must be in the future ───
    futureDate(value, label) {
        if (!value) return null; // required check is separate
        const date = new Date(value);
        if (date <= new Date()) {
            return `${label} must be a future date.`;
        }
        return null;
    },

    /**
     * validateForm — main entry point
     * @param {HTMLFormElement} form
     * @param {Array} rules — [{field: '#id', validate: fn(el) => string|null}, ...]
     * @returns {boolean} true if valid
     */
    validateForm(form, rules) {
        this.clearAllErrors(form);
        let isValid = true;
        let firstError = null;

        for (const rule of rules) {
            const el = form.querySelector(rule.field);
            if (!el) continue;

            const error = rule.validate(el);
            if (error) {
                this.showError(el, error);
                isValid = false;
                if (!firstError) firstError = el;
            }
        }

        // Scroll to first error
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }

        return isValid;
    },

    /**
     * Live validation — attach real-time feedback on blur
     */
    attachLiveValidation(form, rules) {
        for (const rule of rules) {
            const el = form.querySelector(rule.field);
            if (!el) continue;

            el.addEventListener('blur', () => {
                const error = rule.validate(el);
                if (error) {
                    this.showError(el, error);
                } else {
                    this.clearError(el);
                }
            });

            // Clear error on input
            el.addEventListener('input', () => {
                this.clearError(el);
            });
        }
    }
};


// ══════════════════════════════════════════════
//  FORM-SPECIFIC VALIDATION SETUP
// ══════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // Inject validation CSS
    const style = document.createElement('style');
    style.textContent = `
        .input-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }
        .field-error-msg {
            display: block;
            color: #f87171;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 6px;
            animation: errorSlide 0.25s ease;
        }
        @keyframes errorSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);


    // ─────────────────────────────────────────
    //  TRANSPORT MANAGEMENT FORMS
    // ─────────────────────────────────────────

    // Add Transport Type Form
    const addTypeForm = document.getElementById('add-type-form');
    if (addTypeForm) {
        const rules = [
            {
                field: 'input[name="name"]',
                validate: (el) => Validator.required(el.value, 'Type name') || Validator.minLength(el.value, 2, 'Type name') || Validator.maxLength(el.value, 50, 'Type name')
            },
            {
                field: 'input[name="type_image"]',
                validate: (el) => el.files.length > 0 ? Validator.fileType(el, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'Image') : null
            }
        ];

        Validator.attachLiveValidation(addTypeForm, rules);
        addTypeForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addTypeForm, rules)) {
                e.preventDefault();
            }
        });
    }

    // Add Vehicle Form
    const addVehicleForm = document.getElementById('add-vehicle-form');
    if (addVehicleForm) {
        const rules = [
            {
                field: 'input[name="name"]',
                validate: (el) => Validator.required(el.value, 'Vehicle name') || Validator.minLength(el.value, 2, 'Vehicle name')
            },
            {
                field: 'input[name="type"]',
                validate: (el) => Validator.required(el.value, 'Type label') || Validator.minLength(el.value, 2, 'Type label')
            },
            {
                field: 'input[name="capacity"]',
                validate: (el) => Validator.required(el.value, 'Capacity') || Validator.numberRange(el.value, 1, 500, 'Capacity')
            },
            {
                field: 'select[name="status"]',
                validate: (el) => Validator.selectRequired(el.value, 'status')
            }
        ];

        Validator.attachLiveValidation(addVehicleForm, rules);
        addVehicleForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addVehicleForm, rules)) {
                e.preventDefault();
            }
        });
    }

    // Add Route Form
    const addTrajetForm = document.getElementById('add-trajet-form');
    if (addTrajetForm) {
        const rules = [
            {
                field: 'input[name="departure"]',
                validate: (el) => Validator.required(el.value, 'Departure location') || Validator.minLength(el.value, 2, 'Departure location')
            },
            {
                field: 'input[name="destination"]',
                validate: (el) => Validator.required(el.value, 'Destination location') || Validator.minLength(el.value, 2, 'Destination location')
            },
            {
                field: 'input[name="departureTime"]',
                validate: (el) => Validator.required(el.value, 'Departure time')
            },
            {
                field: 'input[name="price"]',
                validate: (el) => Validator.required(el.value, 'Price') || Validator.numberRange(el.value, 0, 1000, 'Price')
            },
            {
                field: 'select[name="idTransport"]',
                validate: (el) => Validator.selectRequired(el.value, 'vehicle')
            }
        ];

        Validator.attachLiveValidation(addTrajetForm, rules);
        addTrajetForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addTrajetForm, rules)) {
                e.preventDefault();
            }
        });
    }

});

// Export function to setup validations for dynamically added forms
window.setupTransportValidations = function() {
    // Add Transport Type Form
    const addTypeForm = document.getElementById('add-type-form');
    if (addTypeForm) {
        const rules = [
            {
                field: 'input[name="name"]',
                validate: (el) => Validator.required(el.value, 'Type name') || Validator.minLength(el.value, 2, 'Type name') || Validator.maxLength(el.value, 50, 'Type name')
            },
            {
                field: 'input[name="type_image"]',
                validate: (el) => el.files.length > 0 ? Validator.fileType(el, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'Image') : null
            }
        ];

        Validator.attachLiveValidation(addTypeForm, rules);
        addTypeForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addTypeForm, rules)) {
                e.preventDefault();
            }
        });
    }

    // Add Vehicle Form
    const addVehicleForm = document.getElementById('add-vehicle-form');
    if (addVehicleForm) {
        const rules = [
            {
                field: 'input[name="name"]',
                validate: (el) => Validator.required(el.value, 'Vehicle name') || Validator.minLength(el.value, 2, 'Vehicle name')
            },
            {
                field: 'input[name="type"]',
                validate: (el) => Validator.required(el.value, 'Type label') || Validator.minLength(el.value, 2, 'Type label')
            },
            {
                field: 'input[name="capacity"]',
                validate: (el) => Validator.required(el.value, 'Capacity') || Validator.numberRange(el.value, 1, 500, 'Capacity')
            },
            {
                field: 'select[name="status"]',
                validate: (el) => Validator.selectRequired(el.value, 'status')
            }
        ];

        Validator.attachLiveValidation(addVehicleForm, rules);
        addVehicleForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addVehicleForm, rules)) {
                e.preventDefault();
            }
        });
    }

    // Add Route Form
    const addTrajetForm = document.getElementById('add-trajet-form');
    if (addTrajetForm) {
        const rules = [
            {
                field: 'input[name="departure"]',
                validate: (el) => Validator.required(el.value, 'Departure location') || Validator.minLength(el.value, 2, 'Departure location')
            },
            {
                field: 'input[name="destination"]',
                validate: (el) => Validator.required(el.value, 'Destination location') || Validator.minLength(el.value, 2, 'Destination location')
            },
            {
                field: 'input[name="departureDate"]',
                validate: (el) => Validator.required(el.value, 'Departure date') || Validator.futureDate(el.value, 'Departure date')
            },
            {
                field: 'input[name="departureTime"]',
                validate: (el) => Validator.required(el.value, 'Departure time')
            },
            {
                field: 'input[name="price"]',
                validate: (el) => Validator.required(el.value, 'Price') || Validator.numberRange(el.value, 0, 1000, 'Price')
            },
            {
                field: 'select[name="idTransport"]',
                validate: (el) => Validator.selectRequired(el.value, 'vehicle')
            }
        ];

        Validator.attachLiveValidation(addTrajetForm, rules);
        addTrajetForm.addEventListener('submit', (e) => {
            if (!Validator.validateForm(addTrajetForm, rules)) {
                e.preventDefault();
            }
        });
    }
};
