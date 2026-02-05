/**
 * Vertical Marquee Gallery Controller
 * Handles infinite vertical scrolling with dynamic speed calculation
 */
(function () {
    'use strict';

    class VerticalMarqueeGallery {
        constructor(container) {
            this.container = container;
            this.columns = container.querySelectorAll('.mgwpp-vmarquee-column');
            this.pauseBtn = container.querySelector('.mgwpp-vmarquee-pause-btn');
            this.isPaused = false;
            this.baseSpeed = parseInt(container.dataset.speed) || 30;
            this.pauseOnHover = container.dataset.pauseHover === 'true';

            this.init();
        }

        init() {
            // Set up each column with appropriate animation duration
            this.columns.forEach((column, index) => {
                const track = column.querySelector('.mgwpp-vmarquee-track');
                if (!track) return;

                // Calculate speed (staggered per column)
                const columnSpeed = parseInt(column.dataset.speed) || this.baseSpeed;
                const duration = 100 - columnSpeed; // Invert: higher speed = shorter duration

                track.style.setProperty('--vmarquee-duration', `${Math.max(10, duration)}s`);
            });

            // Pause button functionality
            if (this.pauseBtn) {
                this.pauseBtn.addEventListener('click', () => this.togglePause());
            }

            // Intersection Observer for performance
            this.setupIntersectionObserver();

            // Keyboard accessibility
            this.container.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    this.togglePause();
                }
            });
        }

        togglePause() {
            this.isPaused = !this.isPaused;
            this.container.classList.toggle('is-paused', this.isPaused);

            if (this.pauseBtn) {
                const pauseIcon = this.pauseBtn.querySelector('.mgwpp-pause-icon');
                const playIcon = this.pauseBtn.querySelector('.mgwpp-play-icon');

                if (pauseIcon && playIcon) {
                    pauseIcon.style.display = this.isPaused ? 'none' : 'block';
                    playIcon.style.display = this.isPaused ? 'block' : 'none';
                }

                this.pauseBtn.setAttribute('aria-label',
                    this.isPaused ? 'Play animation' : 'Pause animation'
                );
            }
        }

        setupIntersectionObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const tracks = this.container.querySelectorAll('.mgwpp-vmarquee-track');
                    tracks.forEach(track => {
                        if (entry.isIntersecting && !this.isPaused) {
                            track.style.animationPlayState = 'running';
                        } else if (!entry.isIntersecting) {
                            // Pause when not visible for performance
                            track.style.animationPlayState = 'paused';
                        }
                    });
                });
            }, { threshold: 0.1 });

            observer.observe(this.container);
        }
    }

    // Initialize all vertical marquee galleries
    function initAllGalleries() {
        const galleries = document.querySelectorAll('.mgwpp-vertical-marquee-gallery');
        galleries.forEach(gallery => {
            // Prevent double initialization
            if (!gallery.dataset.initialized) {
                new VerticalMarqueeGallery(gallery);
                gallery.dataset.initialized = 'true';
            }
        });
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllGalleries);
    } else {
        initAllGalleries();
    }

    // Also run after any AJAX content load
    document.addEventListener('mgwpp-content-loaded', initAllGalleries);

    // Export for manual initialization
    window.MGWPP_VerticalMarquee = {
        init: initAllGalleries,
        Gallery: VerticalMarqueeGallery
    };

})();
