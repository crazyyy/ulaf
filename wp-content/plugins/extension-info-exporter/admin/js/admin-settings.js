// JavaScript Validation for form submission
function validateForm() {
    var checkboxes = document.querySelectorAll('input[name="ext_info_exporter_export_fields[]"]');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(function (checkbox) {
        return checkbox.checked && !checkbox.disabled; // Check only enabled boxes
    });

    if (!checkedOne) {
        alert("Please select at least one field to export.");
        return false; // Prevent form submission
    }
    return true; // Allow form submission
}

function validateExportForm() {
    var checkboxes = document.querySelectorAll('input[name="ext_info_exporter_export_fields[]"]');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(function (checkbox) {
        return checkbox.checked; // Check if any box is checked
    });

    if (!checkedOne) {
        alert("No fields selected for export. The 'Plugin Name' will always be included.");
        return false;
    }

    // No 'recent' option anymore, so no days validation
    return true;
}

// Toggle the days input visibility based on export type selection
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('input[name="ext_info_exporter_export_type"]');
    var exportTypeSelect = document.getElementById('ext_info_exporter_export_type');
    var recentDaysWrap = document.getElementById('ext-info-exporter-recent-days');
    var formatSelect = document.querySelector('select[name="ext_info_exporter_format"]');
    var templateInput = document.getElementById('ext_info_exporter_filename_template');
    var preview = document.getElementById('ext_ie_filename_preview');
    var resetBtn = document.getElementById('ext_ie_reset_filename');

    function updateVisibility() {
        var selectedVal = exportTypeSelect ? exportTypeSelect.value : (document.querySelector('input[name="ext_info_exporter_export_type"]:checked') || {}).value;
        if (recentDaysWrap) { recentDaysWrap.style.display = 'none'; }
    }

    function sanitizeFilename(str) {
        return String(str || '').replace(/[^A-Za-z0-9\-_.]/g, '_');
    }

    function computePreview() {
        if (!preview || !templateInput) return;
        var tpl = templateInput.value || '{site_name}_{date}';
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');
        var hh = String(now.getHours()).padStart(2, '0');
        var min = String(now.getMinutes()).padStart(2, '0');
        var ss = String(now.getSeconds()).padStart(2, '0');
        var site = (window.ExtIE && ExtIE.siteName) ? ExtIE.siteName : location.hostname;
        var exportType = exportTypeSelect ? exportTypeSelect.value : (document.querySelector('input[name="ext_info_exporter_export_type"]:checked') ? document.querySelector('input[name="ext_info_exporter_export_type"]:checked').value : 'all');
        var fmt = formatSelect ? formatSelect.value : 'csv';

        var filename = tpl
            .replace(/\{date\}/g, yyyy + '-' + mm + '-' + dd)
            .replace(/\{time\}/g, hh + '-' + min + '-' + ss)
            .replace(/\{site_name\}/g, site)
            .replace(/\{export_type\}/g, exportType)
            .replace(/\{format\}/g, fmt);

        filename = sanitizeFilename(filename) + '.' + fmt;
        preview.textContent = 'Preview: ' + filename;
    }

    if (templateInput) {
        templateInput.addEventListener('input', computePreview);
    }
    if (formatSelect) {
        formatSelect.addEventListener('change', computePreview);
    }
    if (exportTypeSelect) {
        exportTypeSelect.addEventListener('change', function(){ updateVisibility(); computePreview(); });
    }
    Array.prototype.forEach.call(radios, function (r) {
        r.addEventListener('change', function(){ updateVisibility(); computePreview(); });
    });
    if (resetBtn && templateInput) {
        resetBtn.addEventListener('click', function(){
            templateInput.value = '{site_name}_{date}';
            computePreview();
        });
    }

    Array.prototype.forEach.call(radios, function (r) {
        r.addEventListener('change', updateVisibility);
    });

    updateVisibility();
    computePreview();

    // Enhance save settings with toast
    var settingsForm = document.querySelector('.ext-ie-settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(){
            // Settings form submitted
        });
    }

    // Enhance export submit with toast
    var exportForm = document.querySelector('#ext-ie-card-export form');
    if (exportForm) {
        exportForm.addEventListener('submit', function(){
            // Export form submitted
        });
    }
});
