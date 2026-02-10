// Save & Test frontend: collect provider form, post to authority_mailer_save_and_test,
// then play back returned steps sequentially into a log textarea.
//
// Requires localized authority_mailer_onboarding with { ajax_url, nonce, strings }.

(function(){
  // Support both localized object names
  var localized = window.authority_mailer_onboarding || window.AUTHORITY_MAILEROnboarding;
  if ( typeof localized === 'undefined' ) {
    console.warn('authority-mailer: missing localized authority_mailer_onboarding object.');
    return;
  }
  const ajaxUrl = localized.ajax_url;
  const nonce = localized.nonce;
  const strings = localized.strings || localized || {};

  function findConfigureForm() {
    return document.querySelector('.wpmsl-mailer-settings form.wpmsl-mailer-form');
  }

  function createLogAreaIfMissing(container) {
    let log = container.querySelector('.wpmsl-config-test-log');
    if ( ! log ) {
      log = document.createElement('textarea');
      log.className = 'wpmsl-config-test-log';
      log.setAttribute('readonly', 'readonly');
      log.style.width = '100%';
      log.style.height = '220px';
      log.style.padding = '10px';
      log.style.boxSizing = 'border-box';
      log.style.fontFamily = 'monospace';
      log.style.marginTop = '12px';
      container.appendChild(log);
    }
    return log;
  }

  function appendLog(logEl, line, type) {
    const time = new Date().toLocaleTimeString();
    const prefix = type ? '[' + type.toUpperCase() + '] ' : '';
    logEl.value += '[' + time + '] ' + prefix + line + "\n";
    logEl.scrollTop = logEl.scrollHeight;
  }

  function serializeFormToSettings(form) {
    const settings = {};
    if ( ! form ) return settings;
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(function(i){
      if ( ! i.name ) return;
      let val;
      if ( i.type === 'checkbox' ) {
        val = i.checked ? (i.value || '1') : '';
      } else {
        val = i.value;
      }
      settings[i.name] = val;
    });
    return settings;
  }

  function init() {
    document.addEventListener('click', function(e){
      const btn = e.target.closest('.wpmsl-onboarding-save-and-test');
      if ( ! btn ) return;
      e.preventDefault();

      const form = findConfigureForm();
      if ( ! form ) {
        alert( strings.select_mailer_prompt || 'Form not found.' );
        return;
      }
      const wrapper = form.closest('.wpmsl-mailer-settings') || document.body;
      const log = createLogAreaIfMissing(wrapper);
      appendLog(log, strings.log_saving_settings || 'Saving settings and starting test...', 'info');

      // detect provider key from wrapper class or data attribute
      let provider = '';
      if ( wrapper.dataset && wrapper.dataset.mailer ) provider = wrapper.dataset.mailer;
      if ( ! provider ) {
        const m = wrapper.className.match(/wpmsl-mailer-settings--([a-z0-9-_]+)/i);
        if ( m ) provider = m[1];
      }
      if ( ! provider ) provider = (document.querySelector('input[name="provider"]:checked, input[name="mailer"]:checked') || {}).value || '';

      const settings = serializeFormToSettings(form);

      btn.disabled = true;
      const body = new URLSearchParams();
      body.append('action', 'authority_mailer_smtp_save_and_test');
      // Use standardized 'nonce' field name to match server-side verification
      body.append('nonce', nonce);
      body.append('provider', provider);
      body.append('settings', JSON.stringify(settings));

      fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).then(r => r.json()).then(function(resp){
        if ( resp && resp.success && resp.data && Array.isArray(resp.data.steps) ) {
          const steps = resp.data.steps;
          let i = 0;
          function playNext() {
            if ( i >= steps.length ) {
              appendLog(log, strings.log_test_finished || 'Test finished.', 'result');
              btn.disabled = false;
              return;
            }
            const s = steps[i];
            appendLog(log, s.message || '(no message)', s.status || 'info');
            if ( s.details ) {
              if ( typeof s.details === 'string' ) {
                appendLog(log, s.details, 'detail');
              } else if ( Array.isArray( s.details ) ) {
                s.details.forEach(function(d){ appendLog(log, JSON.stringify(d), 'detail'); });
              } else {
                appendLog(log, JSON.stringify(s.details), 'detail');
              }
            }
            i++;
            setTimeout( playNext, 450 );
          }
          playNext();
        } else {
          const msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Unknown error';
          appendLog(log, 'Test failed: ' + msg, 'error');
          btn.disabled = false;
        }
      }).catch(function(err){
        appendLog(log, 'Request failed: ' + (err && err.message ? err.message : String(err)), 'error');
        btn.disabled = false;
      });
    }, false);
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
  } else {
    init();
  }
})();
