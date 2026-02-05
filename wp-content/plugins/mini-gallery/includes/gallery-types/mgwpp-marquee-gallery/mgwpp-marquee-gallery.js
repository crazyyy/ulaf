/**
 * Marquee Gallery - 3-Layer Scrolling Gallery JavaScript
 * 
 * Handles:
 * - Dynamic speed adjustments
 * - Intersection Observer for performance
 * - Touch/drag interactions
 * - Accessibility features
 */

(function () {
    'use strict';

    /**
     * Marquee Gallery Controller
     */
    class MarqueeGallery {
        constructor(element) {
            this.gallery = element;
            this.settings = JSON.parse(element.dataset.settings || '{}');
            this.layers = element.querySelectorAll('.mgwpp-marquee-layer');
            this.isInView = false;
            this.isPaused = false;

            this.init();
        }

        init() {
            this.setupIntersectionObserver();
            this.setupLayers();
            this.setupAccessibility();
            this.setupTouchInteraction();
        }

        /**
         * Setup Intersection Observer for performance
         * Only animate when in viewport
         */
        setupIntersectionObserver() {
            const options = {
                root: null,
                rootMargin: '100px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    this.isInView = entry.isIntersecting;
                    this.updateAnimationState();
                });
            }, options);

            observer.observe(this.gallery);
        }

        /**
         * Configure individual layers
         */
        setupLayers() {
            this.layers.forEach((layer, index) => {
                const layerNum = index + 1;
                const layerKey = 'layer_' + layerNum;
                const config = this.settings[layerKey] || {};

                const track = layer.querySelector('.mgwpp-marquee-track');
                if (!track) return;

                // Apply direction and speed from settings
                const direction = config.direction || (layerNum % 2 === 0 ? 'right' : 'left');
                const speed = config.speed || 30;
                const pauseOnHover = config.pause_on_hover !== false;

                // Update CSS classes and variables
                track.classList.remove('mgwpp-marquee-left', 'mgwpp-marquee-right');
                track.classList.add('mgwpp-marquee-' + direction);

                if (pauseOnHover) {
                    track.classList.add('mgwpp-pause-on-hover');
                } else {
                    track.classList.remove('mgwpp-pause-on-hover');
                }

                layer.style.setProperty('--marquee-speed', speed + 's');
            });
        }

        /**
         * Update animation state based on visibility
         */
        updateAnimationState() {
            this.layers.forEach(layer => {
                const track = layer.querySelector('.mgwpp-marquee-track');
                if (!track) return;

                if (this.isInView && !this.isPaused) {
                    track.style.animationPlayState = 'running';
                } else {
                    track.style.animationPlayState = 'paused';
                }
            });
        }

        /**
         * Setup accessibility features
         */
        setupAccessibility() {
            // Add keyboard controls
            this.gallery.setAttribute('role', 'region');
            this.gallery.setAttribute('aria-label', 'Scrolling image gallery');

            // Pause button for accessibility
            const pauseBtn = document.createElement('button');
            pauseBtn.className = 'mgwpp-marquee-pause-btn';
            pauseBtn.setAttribute('aria-label', 'Pause marquee animation');
            pauseBtn.innerHTML = `
                <svg class="pause-icon" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <rect x="6" y="4" width="4" height="16"/>
                    <rect x="14" y="4" width="4" height="16"/>
                </svg>
                <svg class="play-icon" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="display:none;">
                    <polygon points="5,3 19,12 5,21"/>
                </svg>
            `;

            pauseBtn.addEventListener('click', () => this.togglePause(pauseBtn));

            // Only add if user prefers reduced motion
            if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
                this.gallery.appendChild(pauseBtn);
            }
        }

        /**
         * Toggle pause state
         */
        togglePause(btn) {
            this.isPaused = !this.isPaused;
            this.updateAnimationState();

            const pauseIcon = btn.querySelector('.pause-icon');
            const playIcon = btn.querySelector('.play-icon');

            if (this.isPaused) {
                pauseIcon.style.display = 'none';
                playIcon.style.display = 'block';
                btn.setAttribute('aria-label', 'Play marquee animation');
            } else {
                pauseIcon.style.display = 'block';
                playIcon.style.display = 'none';
                btn.setAttribute('aria-label', 'Pause marquee animation');
            }
        }

        /**
         * Setup touch/drag interaction for mobile
         */
        setupTouchInteraction() {
            this.layers.forEach(layer => {
                let startX = 0;
                let scrollLeft = 0;
                let isDragging = false;

                const track = layer.querySelector('.mgwpp-marquee-track');
                if (!track) return;

                layer.addEventListener('touchstart', (e) => {
                    isDragging = true;
                    startX = e.touches[0].pageX;
                    track.style.animationPlayState = 'paused';
                }, { passive: true });

                layer.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    const x = e.touches[0].pageX;
                    const walk = (startX - x) * 2;
                    layer.scrollLeft = scrollLeft + walk;
                }, { passive: true });

                layer.addEventListener('touchend', () => {
                    isDragging = false;
                    if (this.isInView && !this.isPaused) {
                        track.style.animationPlayState = 'running';
                    }
                }, { passive: true });
            });
        }
    }

    /**
     * Initialize all marquee galleries
     */
    function initMarqueeGalleries() {
        const galleries = document.querySelectorAll('.mgwpp-marquee-gallery');
        galleries.forEach(gallery => {
            if (!gallery.dataset.initialized) {
                new MarqueeGallery(gallery);
                gallery.dataset.initialized = 'true';
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMarqueeGalleries);
    } else {
        initMarqueeGalleries();
    }

    // Re-initialize for dynamically loaded content
    if (typeof MutationObserver !== 'undefined') {
        const bodyObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('mgwpp-marquee-gallery')) {
                            new MarqueeGallery(node);
                            node.dataset.initialized = 'true';
                        }
                        const nested = node.querySelectorAll && node.querySelectorAll('.mgwpp-marquee-gallery:not([data-initialized])');
                        if (nested) {
                            nested.forEach(g => {
                                new MarqueeGallery(g);
                                g.dataset.initialized = 'true';
                            });
                        }
                    }
                });
            });
        });

        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    // Expose for external use
    window.MGWPPMarqueeGallery = MarqueeGallery;

})();
