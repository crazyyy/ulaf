/**
 * 3D Masonry Gallery Controller
 * 
 * Handles:
 * - 4 view modes: WALL, TABLE, TUNNEL, FLAT
 * - Infinite vertical scrolling
 * - Dynamic speed calculations
 * - Performance optimization via Intersection Observer
 * - Keyboard accessibility
 */
(function () {
    'use strict';

    class MasonryGallery3D {
        constructor(container) {
            this.container = container;
            this.columns = container.querySelectorAll('.mgwpp-masonry-column');
            this.pauseBtn = container.querySelector('.mgwpp-3d-pause-btn');
            this.modeBtns = container.querySelectorAll('.mgwpp-mode-btn');

            this.isPaused = false;
            this.currentMode = container.dataset.mode || 'wall';
            this.baseSpeed = parseInt(container.dataset.speed) || 40;

            this.init();
        }

        init() {
            // Set up animation durations for each column
            this.setupAnimations();

            // Mode switcher
            this.modeBtns.forEach(btn => {
                btn.addEventListener('click', (e) => this.switchMode(e.target.dataset.mode));
            });

            // Pause button
            if (this.pauseBtn) {
                this.pauseBtn.addEventListener('click', () => this.togglePause());
            }

            // Intersection Observer for performance
            this.setupIntersectionObserver();

            // Keyboard navigation
            this.container.setAttribute('tabindex', '0');
            this.container.addEventListener('keydown', (e) => this.handleKeyboard(e));

            // Touch support for mobile
            this.setupTouchSupport();
        }

        setupAnimations() {
            this.columns.forEach((column, index) => {
                const track = column.querySelector('.mgwpp-masonry-track');
                if (!track) return;

                // Column-specific speed (staggered for visual interest)
                const columnSpeed = parseInt(column.dataset.speed) || this.baseSpeed;

                // Calculate duration: higher speed = shorter duration
                // Base formula: 100 - speed, with minimum of 15 seconds
                const duration = Math.max(15, 120 - columnSpeed + (index * 5));

                track.style.animationDuration = `${duration}s`;
            });
        }

        switchMode(mode) {
            // Update data attribute
            this.container.dataset.mode = mode;
            this.currentMode = mode;

            // Update button states
            this.modeBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });

            // Trigger smooth transition animation
            this.container.classList.add('mode-transitioning');
            setTimeout(() => {
                this.container.classList.remove('mode-transitioning');
            }, 800);
        }

        togglePause() {
            this.isPaused = !this.isPaused;
            this.container.classList.toggle('is-paused', this.isPaused);

            // Update button icons
            if (this.pauseBtn) {
                const pauseIcon = this.pauseBtn.querySelector('.mgwpp-icon-pause');
                const playIcon = this.pauseBtn.querySelector('.mgwpp-icon-play');

                if (pauseIcon && playIcon) {
                    pauseIcon.style.display = this.isPaused ? 'none' : 'block';
                    playIcon.style.display = this.isPaused ? 'block' : 'none';
                }

                this.pauseBtn.setAttribute('aria-label',
                    this.isPaused ? 'Play animation' : 'Pause animation'
                );
            }
        }

        handleKeyboard(e) {
            switch (e.key) {
                case ' ':
                case 'Enter':
                    e.preventDefault();
                    this.togglePause();
                    break;
                case '1':
                    this.switchMode('wall');
                    break;
                case '2':
                    this.switchMode('table');
                    break;
                case '3':
                    this.switchMode('tunnel');
                    break;
                case '4':
                    this.switchMode('flat');
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    this.cycleModes(-1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.cycleModes(1);
                    break;
            }
        }

        cycleModes(direction) {
            const modes = ['wall', 'table', 'tunnel', 'flat'];
            const currentIndex = modes.indexOf(this.currentMode);
            let newIndex = currentIndex + direction;

            if (newIndex < 0) newIndex = modes.length - 1;
            if (newIndex >= modes.length) newIndex = 0;

            this.switchMode(modes[newIndex]);
        }

        setupIntersectionObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const tracks = this.container.querySelectorAll('.mgwpp-masonry-track');

                    tracks.forEach(track => {
                        if (entry.isIntersecting && !this.isPaused) {
                            track.style.animationPlayState = 'running';
                        } else if (!entry.isIntersecting) {
                            // Pause when not visible for performance
                            track.style.animationPlayState = 'paused';
                        }
                    });
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            observer.observe(this.container);
        }

        setupTouchSupport() {
            let touchStartX = 0;

            this.container.addEventListener('touchstart', (e) => {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });

            this.container.addEventListener('touchend', (e) => {
                const touchEndX = e.changedTouches[0].clientX;
                const deltaX = touchEndX - touchStartX;

                // Swipe threshold
                if (Math.abs(deltaX) > 50) {
                    this.cycleModes(deltaX > 0 ? -1 : 1);
                }
            }, { passive: true });
        }
    }

    // Initialize all galleries
    function initAllGalleries() {
        const galleries = document.querySelectorAll('.mgwpp-3d-masonry-gallery');

        galleries.forEach(gallery => {
            if (!gallery.dataset.initialized) {
                new MasonryGallery3D(gallery);
                gallery.dataset.initialized = 'true';
            }
        });
    }

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllGalleries);
    } else {
        initAllGalleries();
    }

    // Support for dynamic content (AJAX loading)
    document.addEventListener('mgwpp-content-loaded', initAllGalleries);

    // Export for manual initialization
    window.MGWPP_3DMasonry = {
        init: initAllGalleries,
        Gallery: MasonryGallery3D
    };

})();
