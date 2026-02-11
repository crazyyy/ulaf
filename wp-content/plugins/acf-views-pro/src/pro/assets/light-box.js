// https://codebeautify.org/minify-js
(function () {

    const LIGHT_BOX = {
        class: {
            _: 'acf-views-light-box',
            IMAGE: 'acf-views-light-box__image',
            ICON: 'acf-views-light-box__icon',
            ICON__INACTIVE: 'acf-views-light-box__icon--inactive',
            ICON_LEFT: 'acf-views-light-box__icon-left',
            ICON_RIGHT: 'acf-views-light-box__icon-right',
        },
        data: {
            FULL: 'full',
        }
    }

    class LightBox {
        constructor(gallery) {
            this.gallery = gallery
            this.lightBox = null
            this.currentItem = null

            this.setup()
        }

        removePopup(event) {
            if (event.target !== this.lightBox) {
                return
            }

            event.preventDefault()

            this.lightBox.remove()
            this.lightBox = null
            this.currentItem = null
        }

        updateImage() {
            let image = document.createElement('img')
            image.classList.add(LIGHT_BOX.class.IMAGE)
            let originImage = this.currentItem.querySelector('img')
            if (!originImage) {
                console.log('ACF Views: Lightbox can\'t be created, the image element is missing')
                return
            }
            image.src = originImage.dataset[LIGHT_BOX.data.FULL] || originImage.src || ''

            // can be missing for the first call
            this.lightBox.querySelector('img')?.remove()
            this.lightBox.append(image)
        }

        updateArrowsState() {
            let leftIcon = this.lightBox.querySelector('.' + LIGHT_BOX.class.ICON_LEFT)
            let rightIcon = this.lightBox.querySelector('.' + LIGHT_BOX.class.ICON_RIGHT)

            this.currentItem.previousSibling ?
                leftIcon.classList.remove(LIGHT_BOX.class.ICON__INACTIVE) :
                leftIcon.classList.add(LIGHT_BOX.class.ICON__INACTIVE);

            this.currentItem.nextSibling ?
                rightIcon.classList.remove(LIGHT_BOX.class.ICON__INACTIVE) :
                rightIcon.classList.add(LIGHT_BOX.class.ICON__INACTIVE);
        }

        showNextItem() {
            this.currentItem = this.currentItem.nextSibling
            this.updateImage()
            this.updateArrowsState()
        }

        showPrevItem() {
            this.currentItem = this.currentItem.previousSibling
            this.updateImage()
            this.updateArrowsState()
        }

        createPopup(item) {
            this.currentItem = item
            this.lightBox = document.createElement('div')
            this.lightBox.classList.add(LIGHT_BOX.class._)
            this.lightBox.addEventListener('click', this.removePopup.bind(this))

            //// image

            this.updateImage(item)

            //// leftIcon

            let leftIcon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            leftIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg')
            leftIcon.setAttribute('viewBox', '0 0 40 40')
            leftIcon.setAttribute('width', '40')
            leftIcon.setAttribute('height', '40')
            leftIcon.classList.add(LIGHT_BOX.class.ICON)
            leftIcon.classList.add(LIGHT_BOX.class.ICON_LEFT)
            leftIcon.addEventListener('click', this.showPrevItem.bind(this))
            let leftPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
            leftPath.setAttribute("d",
                "m13.5 7.01 13 13m-13 13 13-13");
            leftIcon.append(leftPath)

            //// rightIcon

            let rightIcon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            rightIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg')
            rightIcon.setAttribute('viewBox', '0 0 40 40')
            rightIcon.setAttribute('width', '40')
            rightIcon.setAttribute('height', '40')
            rightIcon.classList.add(LIGHT_BOX.class.ICON)
            rightIcon.classList.add(LIGHT_BOX.class.ICON_RIGHT)
            rightIcon.addEventListener('click', this.showNextItem.bind(this))
            let rightPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
            rightPath.setAttribute("d",
                "m13.5 7.01 13 13m-13 13 13-13");
            rightIcon.append(rightPath)

            //// setup & show

            this.lightBox.append(leftIcon)
            this.lightBox.append(rightIcon)
            this.updateArrowsState()

            document.body.append(this.lightBox)
        }

        setup() {
            [...this.gallery.children].forEach((item) => {
                item.addEventListener('click', (event) => {
                    event.preventDefault()

                    this.createPopup(item)
                })
            })
        }
    }

    //// lightbox implementation

    class LightBoxes {
        constructor() {
            this.lightboxes = window['acfViewsLightBox'] || []

            if (!this.lightboxes) {
                console.log("ACF Views: LightBox data is missing")
                return
            }

            "loading" === document.readyState ?
                document.addEventListener('DOMContentLoaded', this.setup.bind(this)) :
                this.setup();
        }

        setup() {
            this.lightboxes.forEach((lightboxSelector) => {
                let lightboxElement = document.querySelector(lightboxSelector)

                // Gallery can be missing. Skip without any errors or logs
                if (!lightboxElement) {
                    return
                }

                new LightBox(lightboxElement)
            })
        }
    }

    new LightBoxes()
}())
