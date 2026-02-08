document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('ramacf_api_data_type');
    if (!select) return;

    const row = document.getElementById('ramacf_meta_keys_row');
    select.addEventListener('change', function () {
        if (this.value === 'meta' || this.value === 'mixed') {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
