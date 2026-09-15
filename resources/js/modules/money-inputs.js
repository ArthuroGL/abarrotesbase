export function initializeMoneyInputs() {
    const inputs = document.querySelectorAll('[data-money-input]');

    inputs.forEach((input) => {
        input.addEventListener('blur', () => {
            formatMoneyInput(input);
        });

        input.addEventListener('focus', () => {
            if (input.value === '') {
                return;
            }

            const value = Number(input.value);

            if (!Number.isFinite(value)) {
                input.value = '';
            }
        });
    });
}

function formatMoneyInput(input) {
    const rawValue = input.value.trim();

    if (rawValue === '') {
        return;
    }

    const value = Number(rawValue);

    if (!Number.isFinite(value)) {
        input.value = '';
        return;
    }

    input.value = value.toFixed(2);
}
