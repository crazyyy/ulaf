// Onboarding: load Step 3 (Configure & Test) when user clicks "Save & Continue" on Step 2.
//
// Depends on authority_mailer_onboarding localized object with { ajax_url, nonce, strings }.

(function () {
  // Support both localized object names (authorityMailerOnboarding, authority_mailer_onboarding or AUTHORITY_MAILEROnboarding)
  var localized = window.authorityMailerOnboarding || window.authority_mailer_onboarding || window.AUTHORITY_MAILEROnboarding;
  if ( typeof localized === 'undefined' ) {
    console.warn( 'authority-mailer onboarding config loader: missing localized authority_mailer_onboarding object.' );
    return;
  }

  const ajaxUrl = localized.ajax_url;
  const nonce = localized.nonce;

  /**
   * Get a localized string with fallback.
   *
   * @param {string} key The string key.
   * @param {string} fallback The fallback value.
   * @return {string} The localized string or fallback.
   */
  function getString(key, fallback) {
    if (localized && localized[key]) {
      return localized[key];
    }
    if (localized && localized.strings && localized.strings[key]) {
      return localized.strings[key];
    }
    return fallback || '';
  }

  function getSelectedProvider() {
    let el = document.querySelector('input[name="mailer"]:checked, input[name="provider"]:checked');
    if ( el && el.value ) return el.value;
    el = document.querySelector('select[name*="mailer"], select[name*="provider"], #authority-mailer-mailer-select, select#mailer_select');
    if ( el && el.value ) return el.value;
    el = document.querySelector('[data-selected-provider]');
    if ( el ) return el.getAttribute('data-selected-provider');
    el = document.querySelector('.wpmsl-mailer-tile.selected, .mailer-tile.selected');
    if ( el && el.dataset && el.dataset.provider ) return el.dataset.provider;
    return '';
  }

  function getOnboardingContentContainer() {
    return document.querySelector('.wpmsl-onboarding-content') || document.querySelector('#authority-mailer-smtp-onboarding') || document.querySelector('.wrap .wpmsl-onboarding-step')?.parentNode || document.querySelector('.wrap');
  }

  function init() {
    const chooseSaveButtons = document.querySelectorAll('.wpmsl-step[data-step="2"] .wpmsl-onboarding-save, .wpmsl-step[data-step="2"] button.wpmsl-onboarding-save, .wpmsl-step[data-step="2"] .authority-mailer-save');
    const fallbackButtons = document.querySelectorAll('button[data-step="3"], button[data-step-target="3"], button.wpmsl-onboarding-save-and-continue');
    const buttons = chooseSaveButtons.length ? chooseSaveButtons : fallbackButtons;
    buttons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const provider = getSelectedProvider();
        if ( ! provider ) {
          alert( getString('i18n_select_mailer', 'Please select a mailer to continue.') );
          return;
        }
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = getString('i18n_loading', 'Loading...');
        const body = new URLSearchParams();
        body.append('action', 'authority_mailer_smtp_load_configure_step');
        body.append('provider', provider);
        body.append('nonce', nonce);

        fetch(ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString()
        }).then(r => r.json()).then(function (resp) {
          if ( resp && resp.success && resp.data && resp.data.html ) {
            const container = getOnboardingContentContainer();
            if ( container ) {
              container.innerHTML = resp.data.html;
              if ( window.authorityMailerInitConfigureStep ) {
                try { window.authorityMailerInitConfigureStep(); } catch (err) { /* ignore */ }
              }
            } else {
              console.warn('authority-mailer onboarding: content container not found.');
            }
          } else {
            alert( (resp && resp.data && resp.data.message) ? resp.data.message : getString('i18n_load_config_failed', 'Failed to load configuration step.') );
          }
        }).catch(function (err) {
          console.error(err);
          alert(getString('i18n_request_failed_loading', 'Request failed while loading the configuration step.'));
        }).finally(function () {
          btn.disabled = false;
          btn.innerHTML = originalText;
        });
      });
    });
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
  } else {
    init();
  }
})();
