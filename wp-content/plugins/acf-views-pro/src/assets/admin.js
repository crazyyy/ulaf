(function () {

  const DATA = {
    VIEW_MARKUP_TAB: '#acf-local_acf_views_view li .acf-tab-button[data-key=local_acf_views_view__markup-tab]',
    VIEW_ADVANCED_TAB: '#acf-local_acf_views_view li .acf-tab-button[data-key=local_acf_views_view__advanced-tab]',
    VIEW_PREVIEW_TAB: '#acf-local_acf_views_view li .acf-tab-button[data-key=local_acf_views_view__preview-tab]',
    VIEW_PREVIEW_FIELD: '#acf-local_acf_views_view__preview',
    VIEW_POST_BOX: '#acf-local_acf_views_view',
    CARD_POST_BOX: '#acf-local_acf_views_acf-card-data',
    CARD_ADVANCED_TAB: '#acf-local_acf_views_acf-card-data li .acf-tab-button[data-key=local_acf_views_acf-card-data__advanced]',
    CARD_PREVIEW_TAB: '#acf-local_acf_views_acf-card-data li .acf-tab-button[data-key=local_acf_views_acf-card-data__preview-tab]',
    CARD_PREVIEW_FIELD: '#acf-local_acf_views_acf-card-data__preview',
  }

  const ACF_VIEWS = window.hasOwnProperty('acf_views') ? window.acf_views : null

  // jquery is necessary for select2 events
  let jQuery = window.hasOwnProperty('jQuery') ? window.jQuery : null

  if (!jQuery) {
    new Error('JQuery is missing')
  }

  if (!ACF_VIEWS) {
    new Error('Arguments are missing')
  }

  class WebComponent extends HTMLElement {
    connectedCallback () {
      setTimeout(() => {
        this.initialize()
      }, 0)
    }

    initialize () {

    }
  }

  class Element {
    constructor (isOnReady = true) {
      // allow to initialize child class fields
      setTimeout(() => {
        let callback = this.initialize.bind(this)

        isOnReady ? Element.addDocumentReadyListener(callback) : Element.addWindowLoadListener(callback)
      }, 0)
    }

    static addDocumentReadyListener (callback) {
      'loading' !== document.readyState ? callback() : window.addEventListener('DOMContentLoaded', callback)
    }

    static addWindowLoadListener (callback) {
      'complete' !== document.readyState ? window.addEventListener('load', callback) : callback()
    }

    initialize () {

    }
  }

  class Shortcodes extends WebComponent {

    tryToClipboardOnHTTP (element, text) {
      let textarea = document.createElement('textarea')
      textarea.value = text

      let parent = element.parentElement

      // exactly as sibling of the target element, because browser will scroll to this element on focus
      parent.insertBefore(textarea, element)

      textarea.focus()
      textarea.select()

      try {
        document.execCommand('copy')
      }
      catch (err) {
        // nothing
      }

      textarea.remove()
    }

    initialize () {
      this.querySelectorAll('.av-shortcodes__copy-button').forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault()

          let target = button.getAttribute('data-target')
          if (!target) {
            new Error('Attribute is missing')
          }
          target = this.querySelector(target)
          if (!target) {
            new Error('Target element is missing')
          }

          // navigator.clipboard works only with HTTPS
          if (window.isSecureContext &&
            navigator.clipboard) {
            navigator.clipboard.writeText(target.textContent)
          }
          else {
            this.tryToClipboardOnHTTP(button, target.textContent)
          }

          button.textContent = 'Copied!'

          setTimeout(() => {
            button.textContent = 'Copy to clipboard'
          }, 5000)
        })
      })
    }
  }

  class CodeField extends Element {

    constructor (textarea, mode, isReadOnly) {
      super()

      this.textarea = textarea
      this.mode = mode
      this.isReadOnly = isReadOnly
    }

    initialize () {
      if (!window.hasOwnProperty('wp') || !window.hasOwnProperty('jQuery') || !window.hasOwnProperty('_cm_settings') ||
        !window._cm_settings.hasOwnProperty('_html') || !window._cm_settings.hasOwnProperty('_js') ||
        !window._cm_settings.hasOwnProperty('_css') || !window._cm_settings.hasOwnProperty('_php')) {
        return
      }

      let allSettings = JSON.parse(JSON.stringify(window['_cm_settings']))
      let settings = {}

      switch (this.mode) {
        case 'htmlmixed':
          settings = allSettings._html || {}
          break
        case 'javascript':
          settings = allSettings._js || {}
          break
        case 'css':
          settings = allSettings._css || {}
          break
        case 'php':
          settings = allSettings._php || {}
          break
      }

      settings.codemirror.readOnly = this.isReadOnly

      let editor = window['wp'].codeEditor.initialize(window['jQuery'](this.textarea), settings)
      editor.codemirror.on('change', (codeMirror) => {
        this.textarea.value = codeMirror.getValue()
      })
    }
  }

  class FieldSelect extends Element {

    constructor (mainSelectId, subSelectId, identifierInputId) {
      super()

      this.mainSelectId = mainSelectId
      this.subSelectId = subSelectId
      this.identifierInputId = identifierInputId
    }

    initialize () {

      if (!window.hasOwnProperty('MutationObserver')) {
        console.log('AcfViews : MutationObserver doesn\'t supported')

        return ''
      }

      this.addListeners(document.body)

      const observer = new MutationObserver((records, observer) => {
        for (let record of records) {
          record.addedNodes.forEach((addedNode) => {
            this.addListeners(addedNode)
          })
        }
      })
      observer.observe(document.body, {
        childList: true, subtree: true,
      })
    }

    addListeners (element) {
      if (Node.ELEMENT_NODE !== element.nodeType) {
        return
      }

      element.querySelectorAll('select[id*="' + this.mainSelectId + '"]').forEach((mainSelect, index) => {
        this.updateAvailableFields(mainSelect)

        // it's necessary to use jQuery.on('change'), because select2 doesn't trigger ordinary JS onChange
        jQuery(mainSelect).on('change', (event) => {
          this.updateAvailableFields(event.target, true)
          this.clearFieldIdentifier(event.target)
        })
      })
      element.querySelectorAll('select[id*="' + this.subSelectId + '"]').forEach((subSelect, index) => {
        subSelect.addEventListener('change', (event) => {
          this.clearFieldIdentifier(event.target)
        })
      })
    }

    updateAvailableFields (mainSelect, isChanged = false) {

      let box = mainSelect.closest('.acf-row')

      box = !box ? mainSelect.closest('.acf-postbox') : box

      box.querySelectorAll('select[id*="' + this.subSelectId + '"]').forEach((subSelect) => {
        if (isChanged) {
          // reset the current option
          subSelect.value = ''
        }

        // filter by groupId, [fieldId]
        let mainSelectIds = mainSelect.value.split('|')

        subSelect.querySelectorAll('option').forEach((fieldOption) => {
          // always keep default option
          if (!fieldOption.value) {
            return
          }

          let optionIds = fieldOption.value.split('|')
          // ignore last part (so compare only groupId, [fieldId]
          let optionIdsLast = optionIds.length - 1
          let isMatch = true

          for (let i = 0; i < optionIdsLast; i++) {
            if (i < mainSelectIds.length && optionIds[i] === mainSelectIds[i]) {
              continue
            }
            isMatch = false
            break
          }

          if (!isMatch) {
            fieldOption.style.display = 'none'
            return
          }

          fieldOption.style.display = ''
        })

        if (isChanged) {
          // important for repeater fields select
          subSelect.dispatchEvent(new Event('change'))
        }
      })
    }

    clearFieldIdentifier (rowSelect) {
      // optional
      if (!this.identifierInputId) {
        return
      }

      let identifierInput = rowSelect.closest('.acf-row').querySelector('input[id*="' + this.identifierInputId + '"]')
      identifierInput.value = ''
    }
  }

  class PostBox extends Element {
    createFieldSelects () {
      if (!ACF_VIEWS.hasOwnProperty('fieldSelect')) {
        new Error('Required property is missing')
      }

      ACF_VIEWS.fieldSelect.forEach((fieldSelect) => {
        if (!fieldSelect.hasOwnProperty('mainSelectId') || !fieldSelect.hasOwnProperty('subSelectId') ||
          !fieldSelect.hasOwnProperty('identifierInputId')) {
          new Error('Required property is missing')
        }

        new FieldSelect(fieldSelect.mainSelectId, fieldSelect.subSelectId, fieldSelect.identifierInputId)
      })
    }

    initializeCodeFields (tabSelectors) {
      document.body.querySelectorAll(tabSelectors).forEach((tabLi) => {
        let tab = tabLi.parentElement

        if (tab.classList.contains('active')) {
          this.createCodeFields()
          return
        }

        let isInit = false
        tab.addEventListener('click', (event) => {
          if (isInit) {
            return
          }
          isInit = true
          this.createCodeFields()
        })
      })
    }

    createCodeFields () {
      if (!ACF_VIEWS.hasOwnProperty('markupTextarea')) {
        new Error('Required argument is missing')
      }

      ACF_VIEWS['markupTextarea'].forEach((markupTextarea) => {
        if (!markupTextarea.hasOwnProperty('idSelector') || !markupTextarea.hasOwnProperty('mode') ||
          !markupTextarea.hasOwnProperty('isReadOnly')) {
          new Error('Required argument is missing')
        }

        let textarea = document.body.querySelector('textarea[id*="' + markupTextarea.idSelector + '"]')

        // optionally, as the field list contains fields from 2 pages
        if (!textarea) {
          return
        }

        let field = textarea.closest('.acf-field')

        // the field list contains fields from several tabs, process only from the tab (visible)
        if (field.classList.contains('acf-hidden')) {
          return
        }

        new CodeField(textarea, markupTextarea.mode, markupTextarea.isReadOnly)
      })

    }
  }

  class Preview {
    constructor (tabSelector, previewFieldSelector, html, css, homeUrl, extraCss = '') {
      this.tab = document.body.querySelector(tabSelector).parentElement
      this.previewField = document.querySelector(previewFieldSelector)
      this.html = html
      this.css = css
      this.homeUrl = homeUrl
      this.extraCss = extraCss
    }

    initialize () {
      if (this.tab.classList.contains('active')) {
        this.makeAjax()
        return
      }

      let isInit = false
      this.tab.addEventListener('click', (event) => {
        if (isInit) {
          return
        }
        isInit = true

        this.makeAjax()
      })
    }

    makeAjax () {
      const request = new XMLHttpRequest()

      request.timeout = 30000
      request.open('GET', this.homeUrl, true)
      request.addEventListener('readystatechange', () => {
        if (request.readyState !== 4) {
          return
        }

        if (200 !== request.status) {
          console.log('preview : ajax request is failed')
          return
        }

        this.grepStyles(request.responseText)
      })
      request.addEventListener('timeout', () => {
        console.log('preview : ajax timeout')
      })

      request.send()
    }

    grepStyles (pageHtml) {

      let html = document.createElement('div')
      html.innerHTML = pageHtml

      let stylesheets = []
      let styles = ''

      html.querySelectorAll('link[rel=stylesheet]').forEach((stylesheet) => {
        stylesheets.push(stylesheet.href)
      })
      html.querySelectorAll('style').forEach((style) => {
        styles += style.outerHTML
      })

      this.createFiddle(stylesheets, styles)
    }

    // https://blog.codepen.io/documentation/prefill-embeds/
    createFiddle (stylesheets, styles) {

      let fiddle = document.createElement('div')
      let fiddleScript = document.createElement('script')
      let html = document.createElement('pre')
      let css = document.createElement('pre')

      fiddle.classList.add('codepen')
      // encodeURI is necessary in case using of 'head'
      fiddle.dataset['prefill'] = encodeURI(JSON.stringify({
        stylesheets: stylesheets,
        // some padding to body, to give better look + extraCss (e.g. viewCss for card)
        head: styles + '<style>body{padding:20px;}' + this.extraCss + '</style>',
      }))

      fiddle.dataset['editable'] = 'true'
      fiddle.dataset['height'] = '800'
      fiddle.dataset['defaultTab'] = 'result'

      fiddleScript.setAttribute('async', 'on')
      fiddleScript.setAttribute('src', 'https://static.codepen.io/assets/embed/ei.js')

      html.dataset['lang'] = 'html'
      html.innerHTML = this.html

      css.dataset['lang'] = 'css'
      css.innerHTML = this.css ?
        this.css :
        '/*todo : test your styles here, then copy to the "CSS Code" field and press the "Update" button */'

      fiddle.appendChild(html)
      fiddle.appendChild(css)
      fiddle.append(fiddleScript)

      this.previewField.replaceWith(fiddle)
    }
  }

  class View extends PostBox {
    initialize () {
      super.initialize()

      if (!document.body.querySelector(DATA.VIEW_POST_BOX)) {
        return
      }

      this.createFieldSelects()

      let tabSelectors = DATA.VIEW_ADVANCED_TAB + ',' + DATA.VIEW_MARKUP_TAB
      Element.addWindowLoadListener(this.initializeCodeFields.bind(this, tabSelectors))

      this.initializePreview()
    }

    initializePreview () {
      if (!ACF_VIEWS.hasOwnProperty('viewPreview') ||
        !ACF_VIEWS.viewPreview.hasOwnProperty('HTML') ||
        !ACF_VIEWS.viewPreview.hasOwnProperty('CSS') ||
        !ACF_VIEWS.viewPreview.hasOwnProperty('HOME')) {
        console.log('Preview properties are missing')
        return
      }

      let preview = new Preview(DATA.VIEW_PREVIEW_TAB, DATA.VIEW_PREVIEW_FIELD,
        ACF_VIEWS.viewPreview['HTML'],
        ACF_VIEWS.viewPreview['CSS'],
        ACF_VIEWS.viewPreview['HOME'],
      )
      preview.initialize()
    }
  }

  class Card extends PostBox {
    initialize () {
      super.initialize()

      if (!document.body.querySelector(DATA.CARD_POST_BOX)) {
        return
      }

      this.createFieldSelects()
      Element.addWindowLoadListener(this.initializeCodeFields.bind(this, DATA.CARD_ADVANCED_TAB))

      this.initializePreview()
    }

    initializePreview () {
      if (!ACF_VIEWS.hasOwnProperty('cardPreview') ||
        !ACF_VIEWS.cardPreview.hasOwnProperty('HTML') ||
        !ACF_VIEWS.cardPreview.hasOwnProperty('CSS') ||
        !ACF_VIEWS.cardPreview.hasOwnProperty('HOME')) {
        console.log('Preview properties are missing')
        return
      }

      let preview = new Preview(DATA.CARD_PREVIEW_TAB, DATA.CARD_PREVIEW_FIELD,
        ACF_VIEWS.cardPreview['HTML'],
        ACF_VIEWS.cardPreview['CSS'],
        ACF_VIEWS.cardPreview['HOME'],
        ACF_VIEWS.cardPreview['VIEW_CSS'],
      )
      preview.initialize()
    }
  }

  ////

  customElements.define('av-shortcodes', Shortcodes)

  new View()
  new Card()
}())
