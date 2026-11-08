// resources/js/currency-mask.js
export function initCurrencyMask() {
    const inputs = document.querySelectorAll('.currency-mask');

    inputs.forEach((input) => {
        // Formata para 10,55 enquanto digita e bloqueia letras
        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');

            if (value === '') {
                e.target.value = '';
                return;
            }

            e.target.value = (parseInt(value, 10) / 100)
                .toFixed(2)
                .replace('.', ',');
        });

        // Converte de volta para 10.55 ao enviar o formulário para o backend Laravel
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                if (input.value) {
                    input.value = input.value.replace(',', '.');
                }
            });
        }
    });
}
