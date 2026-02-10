(function () {
  'use strict';

  const config = window.advanSnippetEditor || {};
  const workspace = document.querySelector('.advan-snippet-workspace');

  if (workspace) {
    initLayout(workspace, config);
  }

  initValidation(config);

  function initLayout(root, cfg) {
    const sidebar = root.querySelector('.advan-snippet-sidebar');
    const divider = root.querySelector('.advan-snippet-divider');
    const toggle = root.querySelector('.advan-snippet-sidebar-toggle');
    const storageKey = root.getAttribute('data-storage-key') || cfg.storageKey || 'advanSnippetSidebarState';
    const state = Object.assign({ collapsed: false }, loadState(storageKey));
    let resizing = false;
    let startX = 0;
    let startWidth = 0;
    let pendingWidth = state.width || null;
    const minWidth = 240;
    const maxWidth = 520;

    applyState();

    if (toggle) {
      toggle.addEventListener('click', function () {
        setCollapsed(!isCollapsed());
      });
    }

    if (divider && sidebar) {
      divider.addEventListener('pointerdown', startResize);
      divider.addEventListener('pointermove', handleResize);
      divider.addEventListener('pointerup', finishResize);
      divider.addEventListener('pointercancel', finishResize);
    }

    function applyState() {
      if (sidebar && state.width && !state.collapsed) {
        sidebar.style.width = state.width + 'px';
      }
      setCollapsed(Boolean(state.collapsed));
    }

    function startResize(event) {
      if (!sidebar || isCollapsed()) {
        return;
      }
      resizing = true;
      startX = event.clientX;
      startWidth = sidebar.offsetWidth;
      pendingWidth = startWidth;
      divider.classList.add('is-resizing');
      if (divider.setPointerCapture) {
        divider.setPointerCapture(event.pointerId);
      }
    }

    function handleResize(event) {
      if (!resizing || !sidebar) {
        return;
      }
      const delta = event.clientX - startX;
      let nextWidth = startWidth - delta;
      nextWidth = Math.max(minWidth, Math.min(maxWidth, nextWidth));
      pendingWidth = nextWidth;
      sidebar.style.width = nextWidth + 'px';
    }

    function finishResize(event) {
      if (!resizing) {
        return;
      }
      resizing = false;
      if (divider.hasPointerCapture && divider.hasPointerCapture(event.pointerId)) {
        divider.releasePointerCapture(event.pointerId);
      }
      divider.classList.remove('is-resizing');
      if (pendingWidth) {
        state.width = pendingWidth;
        persistState(storageKey, state);
      }
    }

    function isCollapsed() {
      return root.getAttribute('data-sidebar-collapsed') === 'true';
    }

    function setCollapsed(collapsed) {
      const value = collapsed ? 'true' : 'false';
      root.setAttribute('data-sidebar-collapsed', value);
      if (sidebar) {
        sidebar.setAttribute('data-collapsed', value);
        if (collapsed) {
          sidebar.style.width = '';
        } else if (state.width) {
          sidebar.style.width = state.width + 'px';
        }
      }
      if (divider) {
        divider.setAttribute('aria-hidden', collapsed ? 'true' : 'false');
      }
      if (toggle) {
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        const label = collapsed ? (cfg.sidebarCollapsed || toggle.textContent) : (cfg.sidebarExpanded || toggle.textContent);
        toggle.textContent = label;
      }
      state.collapsed = collapsed;
      persistState(storageKey, state);
    }
  }

  function loadState(key) {
    try {
      const raw = window.localStorage.getItem(key);
      return raw ? JSON.parse(raw) : {};
    } catch (error) {
      return {};
    }
  }

  function persistState(key, state) {
    try {
      window.localStorage.setItem(key, JSON.stringify(state));
    } catch (error) {
      // Ignore storage failures.
    }
  }

  function initValidation(cfg) {
    const form = document.getElementById('advan-snippet-form');
    const codeField = document.getElementById('snippet-code');
    const errorEl = form ? form.querySelector('[data-snippet-error]') : null;
    if (!form || !codeField) {
      return;
    }

    form.addEventListener('submit', function (event) {
      const value = (syncAndGetValue() || '').trim();
      if (value === '') {
        event.preventDefault();
        if (errorEl) {
          errorEl.textContent = cfg.emptyCodeMessage || 'Please add code before saving.';
          errorEl.hidden = false;
        }
        focusEditor();
        return;
      }

      hideError();
    });

    codeField.addEventListener('input', hideError);
    whenEditorReady(function (editor) {
      editor.codemirror.on('change', hideError);
    });

    function focusEditor() {
      const editor = getEditor();
      if (editor && editor.codemirror) {
        editor.codemirror.focus();
      } else {
        codeField.focus();
      }
    }

    function syncAndGetValue() {
      const editor = getEditor();
      if (editor && editor.codemirror) {
        editor.codemirror.save();
        return editor.codemirror.getValue();
      }
      return codeField.value;
    }

    function hideError() {
      if (errorEl) {
        errorEl.hidden = true;
        errorEl.textContent = '';
      }
    }
  }

  function whenEditorReady(callback) {
    if (typeof callback !== 'function') {
      return;
    }
    const attempt = function () {
      const editor = getEditor();
      if (editor && editor.codemirror) {
        callback(editor);
        return;
      }
      const raf = window.requestAnimationFrame || function (cb) {
        return window.setTimeout(cb, 16);
      };
      raf(attempt);
    };
    attempt();
  }

  function getEditor() {
    const field = document.getElementById('snippet-code');
    if (field) {
      // Try common CodeMirror attachment points used by wp.codeEditor.
      if (field.CodeMirror) {
        return { codemirror: field.CodeMirror };
      }

      // The wrapper created by CodeMirror typically stores the instance on a sibling element.
      if (field.nextElementSibling && field.nextElementSibling.CodeMirror) {
        return { codemirror: field.nextElementSibling.CodeMirror };
      }

      const wrapper = field.parentElement ? field.parentElement.querySelector('.CodeMirror') : null;
      if (wrapper && wrapper.CodeMirror) {
        return { codemirror: wrapper.CodeMirror };
      }
    }
    if (!window.wp || !wp.codeEditor || !wp.codeEditor.editors) {
      return null;
    }
    return wp.codeEditor.editors['snippet-code'] || null;
  }
})();
