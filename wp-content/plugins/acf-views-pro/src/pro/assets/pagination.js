// https://codebeautify.org/minify-js
(function () {
    function log(message, data = null) {
        console.log('ACF Card : ' + message)
        if (data) {
            console.log(data)
        }
    }

    if (!window.hasOwnProperty('acf_cards') ||
        !window.acf_cards.hasOwnProperty('ajaxData') ||
        !window.acf_cards.ajaxData.hasOwnProperty('url') ||
        !window.acf_cards.ajaxData.hasOwnProperty('name')) {
        log('ajax data is missing')

        throw new Error('ajax data is missing')
    }

    const ACF_CARD = {
        class: {
            ACF_CARD: 'acf-card',
            PAGINATION: 'acf-card__pagination',
            PAGINATION__TYPE__BUTTON: 'acf-card__pagination--type--load_more_button',
            PAGINATION__TYPE__PAGES: 'acf-card__pagination--type--page_numbers',
            PAGINATION__TYPE__INFINITY: 'acf-card__pagination--type--infinity_scroll',
            PAGINATION__LOCKED: 'acf-card__pagination--locked',
            LOAD_MORE: 'acf-card__load-more',
            ITEMS: 'acf-card__items',
            PAGE: 'acf-card__page',
            PAGE__ACTIVE: 'acf-card__page--active',
        },
        paginationType: {
            BUTTON: 'button',
            PAGES: 'pages',
            INFINITY: 'infinity',
        },
        data: {
            // in camelCase, as JS converts to camelCase for dataset
            PAGES_AMOUNT: 'pagesAmount',
            CARD_ID: 'cardId',
            PAGE_NUMBER: 'pageNumber',
        },
    }
    const AJAX_URL = window.acf_cards.ajaxData.url
    const AJAX_NAME = window.acf_cards.ajaxData.name

    class AcfCard {
        constructor(element, pagination) {
            this.element = element
            this.pagination = pagination
            this.pageNumber = 1
            this.pagesAmount = parseInt(pagination.dataset[ACF_CARD.data.PAGES_AMOUNT] || 0)
            this.cardId = parseInt(pagination.dataset[ACF_CARD.data.CARD_ID] || 0)
            this.isRunningAjax = false
            this.scrollListener = null

            this.paginationType = ''
            this.paginationType = pagination.classList.contains(ACF_CARD.class.PAGINATION__TYPE__BUTTON) ?
                ACF_CARD.paginationType.BUTTON :
                this.paginationType
            this.paginationType = pagination.classList.contains(ACF_CARD.class.PAGINATION__TYPE__PAGES) ?
                ACF_CARD.paginationType.PAGES :
                this.paginationType
            this.paginationType = pagination.classList.contains(ACF_CARD.class.PAGINATION__TYPE__INFINITY) ?
                ACF_CARD.paginationType.INFINITY :
                this.paginationType

            this.setupListeners()
        }

        setupListeners() {
            switch (this.paginationType) {
                case ACF_CARD.paginationType.BUTTON:
                    this.element.querySelector('.' + ACF_CARD.class.LOAD_MORE).addEventListener('click', this.prepareAjax.bind(this))
                    break
                case ACF_CARD.paginationType.PAGES:
                    this.element.querySelectorAll('.' + ACF_CARD.class.PAGE).forEach((pageLink) => {
                        pageLink.addEventListener('click', this.prepareAjax.bind(this))
                    })
                    break
                case ACF_CARD.paginationType.INFINITY:
                    this.scrollListener = this.prepareAjax.bind(this)
                    window.addEventListener('scroll', this.scrollListener)
                    break
            }
        }

        prepareAjax(event) {

            if (this.isRunningAjax) {
                return
            }

            if (ACF_CARD.paginationType.INFINITY === this.paginationType) {
                let elementBottom = this.element.getBoundingClientRect().bottom
                let screenHeight = window.innerHeight

                if (elementBottom - (screenHeight * 2) > 0) {
                    return
                }
            }

            this.isRunningAjax = true
            this.pagination.classList.add(ACF_CARD.class.PAGINATION__LOCKED)

            switch (this.paginationType) {
                case ACF_CARD.paginationType.BUTTON:
                    this.pageNumber++
                    break
                case ACF_CARD.paginationType.PAGES:
                    event.stopPropagation()
                    event.preventDefault()

                    let pageNumberElement = event.target.closest('.' + ACF_CARD.class.PAGE)
                    let pageNumber = parseInt(pageNumberElement.dataset[ACF_CARD.data.PAGE_NUMBER]) || 0

                    if (pageNumber < 1 ||
                        pageNumber > this.pagesAmount) {
                        log('requested page number is over limits')

                        this.removePaginationUI()

                        return
                    }

                    this.element.querySelector('.' + ACF_CARD.class.PAGE__ACTIVE).classList.remove(ACF_CARD.class.PAGE__ACTIVE)
                    this.pageNumber = pageNumber
                    break
                case ACF_CARD.paginationType.INFINITY:
                    this.pageNumber++
                    break
            }

            this.makeAjax()
        }

        removePaginationUI() {
            switch (this.paginationType) {
                case ACF_CARD.paginationType.BUTTON:
                    this.pagination.remove()
                    this.pagination = null
                    break
                case ACF_CARD.paginationType.INFINITY:
                    // use exactly the bound listener, because .bind gives a new every time
                    window.removeEventListener('scroll', this.scrollListener)
                    this.scrollListener = null
                    break
            }
        }

        updateUIAfterAjax() {

            if (this.pageNumber === this.pagesAmount &&
                ACF_CARD.paginationType.PAGES !== this.paginationType) {
                this.removePaginationUI()
                this.isRunningAjax = false

                return
            }

            if (ACF_CARD.paginationType.PAGES === this.paginationType) {
                this.element.querySelector('.' + ACF_CARD.class.PAGE + '[data-page-number="' + this.pageNumber + '"]').classList.add(ACF_CARD.class.PAGE__ACTIVE)
            }

            this.isRunningAjax = false
            this.pagination.classList.remove(ACF_CARD.class.PAGINATION__LOCKED)
        }

        makeAjax() {
            let formData = new FormData()

            formData.append('action', AJAX_NAME)
            formData.append('_noCache', new Date().getTime().toString())
            formData.append('_acfCardId', this.cardId)
            formData.append('_pageNumber', this.pageNumber)

            const request = new XMLHttpRequest()

            request.timeout = 30000
            request.open('POST', AJAX_URL, true)
            request.addEventListener('readystatechange', this.processAjaxResponse.bind(this, request))
            request.addEventListener('timeout', () => {
                log('ajax timeout')
                this.removePaginationUI()
            })

            request.send(formData)
        }

        processAjaxResponse(request) {

            if (request.readyState !== 4) {
                return
            }

            if (200 !== request.status) {
                log('ajax request is failed')
                return
            }

            let response = {}

            try {
                response = JSON.parse(request.responseText)
                if (!response.hasOwnProperty('error') ||
                    !response.hasOwnProperty('html')) {
                    throw new Error('Property is missing')
                }
            } catch (error) {
                log('ajax response is wrong')
                this.removePaginationUI()
                return
            }

            if (response.error) {
                log('ajax request was wrong, details : ' + response.error)
                this.removePaginationUI()
                return
            }

            if (ACF_CARD.paginationType.PAGES === this.paginationType) {
                this.element.querySelector('.' + ACF_CARD.class.ITEMS).innerHTML = response.html
            } else {
                this.element.querySelector('.' + ACF_CARD.class.ITEMS).innerHTML += response.html
            }

            this.updateUIAfterAjax()
        }
    }

    class Pagination {
        constructor() {
            'loading' !== document.readyState ?
                this.setup() :
                window.addEventListener('DOMContentLoaded', this.setup.bind(this))
        }

        setup() {
            const observer = new MutationObserver((records, observer) => {
                for (let record of records) {
                    record.addedNodes.forEach((addedNode) => {
                        this.addListeners(addedNode)
                    })
                }
            })
            observer.observe(document.body, {
                childList: true,
                subtree: true,
            })

            this.addListeners(document.body)
        }

        addListeners(element) {
            if (Node.ELEMENT_NODE !== element.nodeType) {
                return
            }

            element.querySelectorAll('.' + ACF_CARD.class.PAGINATION).forEach((pagination) => {
                let acfCardElement = pagination.closest('.' + ACF_CARD.class.ACF_CARD)
                if (!acfCardElement) {
                    return
                }

                new AcfCard(acfCardElement, pagination)
            })
        }
    }

    new Pagination()
}())