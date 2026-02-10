document.addEventListener('DOMContentLoaded', function() {
    const csmtpMailerData = window.csmtpMailerData || {};

    const openModalButtons = document.querySelectorAll('.js-open-modal');
    const closeModalButtons = document.querySelectorAll('.js-close-modal');
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) { document.body.classList.add('modal-open'); modal.classList.add('is-open'); }
    }
    function closeModal(modal) {
        if (modal && modal.classList.contains('is-open')) {
            modal.classList.add('is-closing');
            modal.addEventListener('animationend', () => {
                modal.classList.remove('is-open', 'is-closing');
                if (!document.querySelector('.smtp-modal.is-open')) { document.body.classList.remove('modal-open'); }
            }, { once: true });
        }
    }
    openModalButtons.forEach(button => button.addEventListener('click', (e) => {
        const modalId = e.currentTarget.dataset.modalId;
        if (modalId === 'email-log-detail-modal') return;
        openModal(modalId);
        if (modalId === 'email-log-list-modal') {
            loadEmailLogs();
            const logSettingsContainer = document.getElementById('email-log-settings-container');
            if (logSettingsContainer) { logSettingsContainer.classList.add('hidden'); }
            initializeSegmentedControl('modal-log-retention-control', 'modal_log_retention_period');
        }
        if (modalId === 'test-email-modal' || modalId === 'telegram-settings-modal') {
            const resultsContainer = document.getElementById(modalId === 'test-email-modal' ? 'test-email-results' : 'telegram-test-results');
            const formContainer = document.querySelector(`#${modalId} form`);
            if (resultsContainer) { resultsContainer.innerHTML = ''; resultsContainer.style.display = 'none'; }
            if (formContainer) { formContainer.style.display = 'flex'; }
        }
    }));
    closeModalButtons.forEach(button => button.addEventListener('click', () => closeModal(button.closest('.smtp-modal'))));
    document.querySelectorAll('.modal-overlay').forEach(overlay => overlay.addEventListener('click', () => closeModal(overlay.closest('.smtp-modal'))));
    document.addEventListener('keydown', (event) => { if (event.key === "Escape") { document.querySelectorAll('.smtp-modal.is-open').forEach(closeModal); } });

    let confirmCallback = null;
    const confirmModal = document.getElementById('confirmation-modal');
    const confirmBtn = document.getElementById('confirmation-modal-confirm-btn');
    function showConfirmation(title, message, onConfirm) {
        document.getElementById('confirmation-modal-title').textContent = title;
        document.getElementById('confirmation-modal-message').textContent = message;
        confirmCallback = onConfirm;
        openModal('confirmation-modal');
    }
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (typeof confirmCallback === 'function') { confirmCallback(); }
            closeModal(confirmModal);
            confirmCallback = null;
        });
    }

    document.getElementById('log-settings-toggle-button')?.addEventListener('click', () => {
        const container = document.getElementById('email-log-settings-container');
        if (container.classList.toggle('hidden')) return;
        initializeSegmentedControl('modal-log-retention-control', 'modal_log_retention_period');
    });

    async function handleFetch(url, formData, button) {
        let btnText, spinner;
        if (button) {
            btnText = button.querySelector('.button-text');
            spinner = button.querySelector('.spinner');
            if(btnText) btnText.classList.add('hidden');
            if(spinner) spinner.classList.remove('hidden');
            button.disabled = true;
        }

        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.data?.message || `Server Error: ${response.status}`);
            }
            
            if (!data.success) {
                throw new Error(data.data?.message || csmtpMailerData.l10n.errorOccurred);
            }
            
            return data;
        } catch (error) {
            showNotification(error.message, 'error');
            console.error('CSMTP Fetch Error:', error);
            return null;
        } finally {
            if (button) {
                if(btnText) btnText.classList.remove('hidden');
                if(spinner) spinner.classList.add('hidden');
                button.disabled = false;
            }
        }
    }

    document.getElementById('csmtp-save-settings-button')?.addEventListener('click', async function(e) {
        e.preventDefault();
        const form = document.getElementById('csmtp-settings-form');
        let formData = new FormData(form);
        formData.append('action', 'csmtp_save_settings');
        formData.append('_ajax_nonce', this.dataset.nonce);
        
        const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, this);
        if (result) {
            showNotification(result.data.message, 'success');
            const passwordInput = document.getElementById('csmtp_password');
            passwordInput.value = '';
            passwordInput.placeholder = '••••••••••••';
        }
    });
    
    document.getElementById('save-log-retention-button')?.addEventListener('click', async function(e){
        e.preventDefault();
        let formData = new FormData();
        formData.append('action', 'csmtp_save_log_retention');
        formData.append('_ajax_nonce', this.dataset.nonce);
        formData.append('log_retention_period', document.getElementById('modal_log_retention_period').value);
        const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, this);
        if (result) {
            showNotification(result.data.message, 'success');
        }
    });

    document.getElementById('save-telegram-settings-button')?.addEventListener('click', async function(e){
        e.preventDefault();
        const form = document.getElementById('csmtp-telegram-settings-form');
        let formData = new FormData(form);
        formData.append('action', 'csmtp_save_telegram_settings');
        formData.append('_ajax_nonce', this.dataset.nonce);
        if (!document.getElementById('telegram_enabled_toggle').checked) formData.set('telegram_enabled', '0');
        if (!document.getElementById('telegram_notify_failed_toggle').checked) formData.set('telegram_notify_failed', '0');
        if (!document.getElementById('telegram_notify_success_toggle').checked) formData.set('telegram_notify_success', '0');
        const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, this);
        if (result) {
            showNotification(result.data.message, 'success');
            const tokenInput = document.getElementById('telegram_bot_token');
            tokenInput.value = '';
            tokenInput.placeholder = '••••••••••••';
        }
    });

    document.getElementById('send-telegram-test-button')?.addEventListener('click', async function(e) {
        e.preventDefault();
        const resultsContainer = document.getElementById('telegram-test-results');
        let formData = new FormData();
        formData.append('action', 'csmtp_send_telegram_test');
        formData.append('_ajax_nonce', this.dataset.nonce);

        const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, this);
        let resultHTML = '';
        if(result && result.success) {
            resultHTML = `<div class="alert-success" role="alert"><p><strong>${result.data.message}</strong></p></div>`;
        } else {
            resultHTML = `<div class="alert-error" role="alert"><p><strong>${csmtpMailerData.l10n.errorOccurred}</strong>. Check console for details.</p></div>`;
        }
        resultsContainer.innerHTML = resultHTML;
        resultsContainer.style.display = 'block';
    });
    
    function loadEmailLogs(page = 1, searchTerm = '') {
        const contentArea = document.getElementById('email-log-list-content');
        if (!contentArea) return;
        const nonce = contentArea.dataset.nonce;
        contentArea.innerHTML = '<div class="loading-spinner-container"><div class="spinner"></div></div>';
        document.getElementById('email-log-list-pagination').innerHTML = '';
        const formData = new FormData();
        formData.append('action', 'csmtp_get_logs');
        formData.append('_ajax_nonce', nonce);
        formData.append('page', page);
        formData.append('search', searchTerm);
        fetch(csmtpMailerData.ajaxUrl, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    contentArea.innerHTML = data.data.html;
                    const paginationContainer = document.getElementById('email-log-list-pagination');
                    if(data.data.pagination_html && data.data.pagination_html.length > 0) {
                        paginationContainer.innerHTML = data.data.pagination_html.join('');
                    } else {
                        paginationContainer.innerHTML = '';
                    }
                } else {
                    contentArea.innerHTML = `<div class="empty-log-state"><p>${data.data.message || 'Error loading logs.'}</p></div>`;
                }
            })
            .catch(() => { contentArea.innerHTML = `<div class="empty-log-state"><p>${csmtpMailerData.l10n.networkError}</p></div>`; });
    }

    let searchTimeout;
    const searchInput = document.getElementById('log-search-input');
    if(searchInput) {
            searchInput.addEventListener('keyup', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadEmailLogs(1, e.target.value.trim());
            }, 400);
        });
    }

    document.getElementById('email-log-list-modal')?.addEventListener('click', async function(e) {
        const logDetailButton = e.target.closest('.btn-view-log');
        if (logDetailButton) { loadLogDetails(logDetailButton.dataset.logId); return; }

        const resendButton = e.target.closest('.btn-resend-log');
        if (resendButton) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'csmtp_resend_email');
            formData.append('_ajax_nonce', resendButton.dataset.nonce);
            formData.append('log_id', resendButton.dataset.logId);
            
            const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, resendButton);
            if (result) {
                showNotification(result.data.message, 'success');
                loadEmailLogs(1, searchInput?.value.trim());
            }
            return;
        }
        
        const deleteButton = e.target.closest('.btn-delete-log');
        if (deleteButton) {
            e.preventDefault();
            showConfirmation(csmtpMailerData.l10n.confirmDeletionTitle, csmtpMailerData.l10n.confirmDeletionMessage, async () => {
                const formData = new FormData();
                formData.append('action', 'csmtp_delete_log');
                formData.append('_ajax_nonce', deleteButton.dataset.nonce);
                formData.append('log_id', deleteButton.dataset.logId);
                
                const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, deleteButton);
                if(result) {
                    showNotification(result.data.message, 'success');
                    loadEmailLogs(1, searchInput?.value.trim());
                }
            });
            return;
        }

        const resendFailedButton = e.target.closest('#resend-failed-logs-button');
        if (resendFailedButton) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'csmtp_resend_all_failed');
            formData.append('_ajax_nonce', resendFailedButton.dataset.nonce);
            
            const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, resendFailedButton);
            if (result) {
                showNotification(result.data.message, 'success');
                loadEmailLogs(1, searchInput?.value.trim());
            }
            return;
        }

        const clearButton = e.target.closest('#clear-logs-button');
        if (clearButton) {
            e.preventDefault();
            showConfirmation(csmtpMailerData.l10n.confirmDeletionTitle, csmtpMailerData.l10n.confirmClearLogsMessage, async () => {
                const formData = new FormData();
                formData.append('action', 'csmtp_clear_logs');
                formData.append('_ajax_nonce', clearButton.dataset.nonce);
                
                const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, clearButton);
                if(result) {
                    showNotification(result.data.message, 'success');
                    loadEmailLogs(1, searchInput?.value.trim());
                }
            });
            return;
        }

        const pageLink = e.target.closest('.page-numbers');
        if (pageLink && !pageLink.classList.contains('current')) {
            e.preventDefault();
            let page = 1;
            const href = pageLink.getAttribute('href');
            if (href) {
                const url = new URL(href, window.location.origin);
                page = url.searchParams.get('paged') || 1;
            }
            loadEmailLogs(parseInt(page, 10), searchInput?.value.trim());
        }
    });

    function loadLogDetails(logId) {
        const detailModal = document.getElementById('email-log-detail-modal');
        const nonce = detailModal.querySelector('[data-nonce]').dataset.nonce;
        const formData = new FormData();
        formData.append('action', 'csmtp_get_log_details');
        formData.append('_ajax_nonce', nonce);
        formData.append('log_id', logId);
        openModal('email-log-detail-modal');
        fetch(csmtpMailerData.ajaxUrl, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const log = res.data;
                    document.getElementById('log-detail-subject').textContent = log.subject;
                    document.getElementById('log-detail-to').textContent = log.to_address;
                    document.getElementById('log-detail-date').textContent = log.formatted_date;
                    const mailerBadge = `<span class="log-badge ${log.mailer === 'SMTP' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-700 border-slate-200'}">${log.mailer}</span>`;
                    const statusIcon = log.status === 'Sent' ? 
                        '<i class="fa-solid fa-circle-check log-status-icon-sent"></i>' : 
                        '<i class="fa-solid fa-circle-xmark log-status-icon-failed"></i>';
                    const statusBadge = `<span class="log-badge ${log.status === 'Sent' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200'}">${log.status}</span>`;
                    document.getElementById('log-detail-mailer').innerHTML = mailerBadge;
                    document.getElementById('log-detail-status').innerHTML = statusIcon + statusBadge;
                    document.getElementById('log-detail-iframe').srcdoc = log.message;

                    const headersPre = document.getElementById('log-detail-headers');
                    const errorContainer = document.getElementById('log-detail-error-container');
                    const errorMessagePre = document.getElementById('log-detail-error');
                    if (headersPre) headersPre.textContent = log.headers;
                    if (log.error_message && errorMessagePre && errorContainer) {
                        errorMessagePre.textContent = log.error_message;
                        errorContainer.style.display = 'block';
                    } else if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                }
            });
    }

    const testEmailForm = document.getElementById('csmtp-test-email-form');
    if (testEmailForm) {
        testEmailForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('send-test-email-button');
            const resultsContainer = document.getElementById('test-email-results');
            const formData = new FormData(this);
            
            const result = await handleFetch(csmtpMailerData.ajaxUrl, formData, button);
            if (result) {
                let resultHTML = `<div class="alert-success" role="alert"><p><strong>${result.data.message}</strong></p></div>`;
                if (result.data && result.data.debug) {
                    const escapedDebug = result.data.debug.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    resultHTML += `<div class="mt-4"><h4 class="text-md font-semibold text-slate-800 mb-2">${csmtpMailerData.l10n.smtpDebugLog}</h4><div class="debug-log-container"><pre>${escapedDebug}</pre></div></div>`;
                }
                resultsContainer.innerHTML = resultHTML;
                resultsContainer.style.display = 'block';
                testEmailForm.style.display = 'none';
            }
        });
    }

    const testEmailModal = document.getElementById('test-email-modal');
    if (testEmailModal) {
        const tabsContainer = testEmailModal.querySelector('.test-email-tabs'), messageTextarea = testEmailModal.querySelector('#csmtp_email_body'), previewIframe = testEmailModal.querySelector('#test-email-preview-iframe');
        const updatePreview = () => { if (previewIframe && messageTextarea) { previewIframe.srcdoc = messageTextarea.value; } };
        if (tabsContainer) {
            tabsContainer.addEventListener('click', (e) => {
                if (e.target.matches('.tab-btn')) {
                    const tabId = e.target.dataset.tab;
                    tabsContainer.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    testEmailModal.querySelectorAll('.test-email-tab-content-wrapper .tab-content').forEach(content => content.classList.remove('active'));
                    testEmailModal.querySelector(`#tab-${tabId}`).classList.add('active');
                    if (tabId === 'preview') { updatePreview(); }
                }
            });
        }
        if (messageTextarea) { messageTextarea.addEventListener('input', updatePreview); updatePreview(); }
    }
    
    document.querySelectorAll('.password-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('.eye-icon').classList.toggle('hidden');
            this.querySelector('.eye-off-icon').classList.toggle('hidden');
        });
    });

    let notificationTimer;
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('csmtp-notification');
        clearTimeout(notificationTimer);
        notification.querySelector('#notification-message').textContent = message;
        const iconContainer = notification.querySelector('#notification-icon');
        if (type === 'success') {
            iconContainer.innerHTML = `<i class="fa-solid fa-circle-check text-green-400"></i>`;
        } else {
            iconContainer.innerHTML = `<i class="fa-solid fa-circle-exclamation text-red-400"></i>`;
        }
        notification.style.display = 'block';
        notification.style.animation = 'slideInRight 0.5s forwards';
        notificationTimer = setTimeout(() => {
            notification.style.animation = 'fadeOut 0.5s forwards';
            setTimeout(() => notification.style.display = 'none', 500);
        }, 5000);
    }
    window.showSmtpNotification = showNotification;

    function updateGlider(control) {
        if (!control) return;
        const glider = control.querySelector('.glider');
        const activeBtn = control.querySelector('.segmented-control-button.active');
        if (glider && activeBtn && activeBtn.offsetWidth > 0) {
            glider.style.width = `${activeBtn.offsetWidth}px`;
            glider.style.height = `${activeBtn.offsetHeight}px`;
            glider.style.left = `${activeBtn.offsetLeft}px`;
            glider.style.top = `${activeBtn.offsetTop}px`;
        }
    }

    function initializeSegmentedControl(controlId, hiddenInputId) {
        const control = document.getElementById(controlId);
        if (!control) return;

        setTimeout(() => updateGlider(control), 50);
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(() => updateGlider(control)).observe(control);
        }

        const buttons = control.querySelectorAll('.segmented-control-button');
        const hiddenInput = hiddenInputId ? document.getElementById(hiddenInputId) : null;
        
        buttons.forEach(button => button.addEventListener('click', (e) => {
            const target = e.currentTarget;
            if (hiddenInput) { hiddenInput.value = target.dataset.value; }
            
            buttons.forEach(btn => btn.classList.remove('active'));
            target.classList.add('active');
            
            updateGlider(control);
            control.dispatchEvent(new Event('change', { bubbles: true }));
        }));
    }
    
    initializeSegmentedControl('mailer-type-control', 'mailer_type');
    initializeSegmentedControl('encryption-control', 'type_of_encryption');
    initializeSegmentedControl('provider-control', null);
    initializeSegmentedControl('modal-log-retention-control', 'modal_log_retention_period');


    document.getElementById('csmtp_auth_toggle')?.addEventListener('change', (e) => {
        document.getElementById('csmtp_auth').value = e.currentTarget.checked ? 'true' : 'false';
    });

    const mailerTypeControl = document.getElementById('mailer-type-control');
    const smtpFieldsContainer = document.getElementById('smtp-settings-fields-container');
    const wpMailerInfo = document.getElementById('wp-mailer-info-state');

    function toggleSmtpFields() {
        if (mailerTypeControl && smtpFieldsContainer && wpMailerInfo) {
            const selectedValue = document.getElementById('mailer_type').value;
            if (selectedValue === 'default') {
                smtpFieldsContainer.style.display = 'none';
                wpMailerInfo.style.display = 'block';
            } else {
                smtpFieldsContainer.style.display = 'grid';
                wpMailerInfo.style.display = 'none';
                updateGlider(document.getElementById('encryption-control'));
                updateGlider(document.getElementById('provider-control'));
            }
        }
    }
    if (mailerTypeControl) {
        mailerTypeControl.addEventListener('change', toggleSmtpFields);
    }
    toggleSmtpFields(); // Initial check on page load
    
    const providers = {
        gmail: { host: 'smtp.gmail.com', port: '587', encryption: 'tls', auth: 'true' },
        outlook: { host: 'smtp.office365.com', port: '587', encryption: 'tls', auth: 'true' },
        sendgrid: { host: 'smtp.sendgrid.net', port: '587', encryption: 'tls', auth: 'true' }
    };
    document.getElementById('provider-control')?.addEventListener('change', function() {
        const selectedProvider = this.querySelector('.active').dataset.value;
        const providerData = providers[selectedProvider];
        if (providerData) {
            document.getElementById('csmtp_host').value = providerData.host;
            document.getElementById('csmtp_port').value = providerData.port;
            document.querySelector(`#encryption-control .segmented-control-button[data-value="${providerData.encryption}"]`)?.click();
            const authToggleInput = document.getElementById('csmtp_auth_toggle');
            authToggleInput.checked = providerData.auth === 'true';
            authToggleInput.dispatchEvent(new Event('change'));
        }
    });
    document.querySelector('.port-suggestions')?.addEventListener('click', function(e) {
        if (e.target.matches('.port-suggestion-btn')) {
            document.getElementById('csmtp_port').value = e.target.dataset.port;
        }
    });
});