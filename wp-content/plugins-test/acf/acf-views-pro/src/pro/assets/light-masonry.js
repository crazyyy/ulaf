// https://codebeautify.org/minify-js
(function () {
    // https://gitlab.com/lightsource/masonry

    const MASONRY_DATA = {
        data: {
            WIDTH: 'width',
            HEIGHT: 'height',
        },
    };

    class LightMasonry {
        constructor(element, settings) {
            this.element = element;
            this.settings = settings;
            this.maxBucketWidth = 0;
            this.setup();
        }

        getGutter() {
            if (!this.settings.MOBILE_SCREEN_WIDTH ||
                !this.settings.MOBILE_GUTTER) {
                return this.settings.GUTTER;
            }
            let isMobile = window.innerWidth < this.settings.MOBILE_SCREEN_WIDTH;
            return isMobile ?
                this.settings.MOBILE_GUTTER :
                this.settings.GUTTER;
        }

        getBucketWidth(items, extraWidth = 0) {
            let width = 0;
            if (items.length) {
                width = this.getGutter() * (items.length - 1);
                items.forEach((item) => {
                    width += item.width;
                });
            }
            width += extraWidth;
            return width;
        }

        getBuckets(items) {
            let buckets = [];
            // don't use this.element.clientWidth()
            // https://bugs.chromium.org/p/chromium/issues/detail?id=360889
            // https://bugzilla.mozilla.org/show_bug.cgi?id=825607
            // http://jsfiddle.net/7MMhB/
            let elementWidth = parseFloat(window.getComputedStyle(this.element).width);
            elementWidth = Math.floor(elementWidth);
            this.maxBucketWidth = this.settings.ROW_MAX_WIDTH || elementWidth;
            let lastBucket = {
                items: [],
                width: 0,
                height: 0,
                isLast: false,
                scale: 0,
            };
            items.forEach((item) => {
                Object.assign(item.style, {
                    width: 'auto',
                    float: 'left',
                    position: 'relative',
                    clear: 'none',
                    marginTop: 0,
                    marginLeft: 0,
                });
                let originalHeight = parseInt(item.dataset[MASONRY_DATA.data.HEIGHT]);
                let originalWidth = parseInt(item.dataset[MASONRY_DATA.data.WIDTH]);
                let scale = this.settings.ROW_MIN_HEIGHT / originalHeight;
                let itemData = {
                    element: item,
                    originalHeight: originalHeight,
                    originalWidth: originalWidth,
                    aspect: originalWidth / originalHeight,
                    scale: scale,
                    // floor to avoid have decimal numbers
                    width: Math.floor(originalWidth * scale),
                    height: Math.floor(originalHeight * scale),
                };
                let newBucketWidth = this.getBucketWidth(lastBucket.items, itemData.width);
                if (newBucketWidth > this.maxBucketWidth) {
                    buckets.push(lastBucket);
                    lastBucket = {
                        items: [],
                        width: 0,
                        height: 0,
                        isLast: false,
                        scale: 0,
                    };
                }
                lastBucket.items.push(itemData);
            });
            buckets.push(lastBucket);
            lastBucket.isLast = true;
            return buckets;
        }

        updateItemStyles(buckets) {
            buckets.forEach((bucket, index) => {
                if (!bucket.isLast) {
                    bucket.scale = (this.maxBucketWidth - (bucket.items.length - 1) * this.getGutter()) /
                        this.getBucketWidth(bucket.items);
                }
                let lastItem;
                bucket.items.forEach((item, index2) => {
                    if (bucket.scale) {
                        // floor to avoid have decimal numbers
                        item.width = Math.floor(item.width * bucket.scale);
                        item.height = Math.floor(item.height * bucket.scale);
                    }
                    lastItem = item;
                    Object.assign(item.element.style, {
                        height: item.height + 'px',
                        width: item.width + 'px',
                        marginTop: this.getGutter() + 'px',
                    });
                    if (index2 > 0) {
                        item.element.style.marginLeft = this.getGutter() + 'px';
                    } else {
                        item.element.style.clear = 'left';
                    }
                });
                // if !bucket.last &&
                if (lastItem) {
                    lastItem.width = lastItem.width + this.maxBucketWidth - this.getBucketWidth(bucket.items);
                    lastItem.element.style.width = lastItem.width + 'px';
                }
            });
        }

        setup() {
            this.updateItems();
            this.element.dataset['masonry'] = 'masonry';
            window.addEventListener('resize', () => {
                this.updateItems();
            });
        }

        updateItems(items = null) {
            items = items || Array.from(this.element.children);
            let buckets = this.getBuckets(items);
            this.updateItemStyles(buckets);
        }

        addItems(items) {
            this.element.append(...items);
            this.updateItems(items);
        }
    }

    //// galleries implementation

    class Galleries {
        constructor() {
            this.galleries = window['acfViewsMasonry'] || []

            if (!this.galleries) {
                console.log("ACF Views: Galleries data is missing")
                return
            }

            "loading" === document.readyState ?
                document.addEventListener('DOMContentLoaded', this.setup.bind(this)) :
                this.setup();
        }

        setup() {
            let isBroken = false
            this.galleries.forEach((galleryData) => {
                if (!galleryData.hasOwnProperty('selector') ||
                    !galleryData.hasOwnProperty('rowMinHeight') ||
                    !galleryData.hasOwnProperty('gutter') ||
                    !galleryData.hasOwnProperty('mobileGutter')) {
                    isBroken = true
                    return
                }

                let galleryElement = document.querySelector(galleryData.selector)

                // Gallery can be missing. Skip without any errors or logs
                if (!galleryElement) {
                    return
                }

                new LightMasonry(galleryElement,
                    {
                        ROW_MIN_HEIGHT: galleryData.rowMinHeight,
                        GUTTER: galleryData.gutter,
                        MOBILE_GUTTER: galleryData.mobileGutter,
                        MOBILE_WIDTH: 992,
                    });
            })

            if (isBroken) {
                console.log("ACF Views: Galleries data is wrong")
            }
        }
    }

    new Galleries()
}())

