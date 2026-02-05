// mgwpp-spotlight-carousel.js
document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('.mgwpp-spotlight-carousel');

    carousels.forEach(carousel => {
        initSpotlightCarousel(carousel);
    });
});

function initSpotlightCarousel(carousel) {
    // Spotlight Effect
    document.addEventListener('mousemove', (e) => {
        document.documentElement.style.setProperty('--mgwpp-x', `${e.clientX}px`);
        document.documentElement.style.setProperty('--mgwpp-y', `${e.clientY}px`);
    });

    // Get settings from data attributes
    const autoPlay = carousel.dataset.autoplay !== 'false';
    const slideDuration = parseInt(carousel.dataset.duration, 10) || 8000;

    // Carousel Elements
    const carouselSlides = carousel.querySelectorAll('.mgwpp-carousel-slide');
    const navButtons = carousel.querySelectorAll('.mgwpp-nav-btn');
    const prevArrow = carousel.querySelector('.mgwpp-arrow-prev');
    const nextArrow = carousel.querySelector('.mgwpp-arrow-next');
    const viewport = carousel.querySelector('.mgwpp-carousel-viewport');

    if (carouselSlides.length === 0) return;

    let currentSlideIndex = 0;
    let autoAdvanceInterval = null;
    let isTransitioning = false;

    function activateSlide(index) {
        if (isTransitioning || index === currentSlideIndex) return;

        isTransitioning = true;

        // Remove active classes
        carouselSlides[currentSlideIndex].classList.remove('mgwpp-active');
        navButtons[currentSlideIndex]?.classList.remove('mgwpp-active');

        // Add active classes to new slide
        carouselSlides[index].classList.add('mgwpp-active');
        navButtons[index]?.classList.add('mgwpp-active');

        // Update current index
        currentSlideIndex = index;

        // Allow next transition after animation completes
        setTimeout(() => {
            isTransitioning = false;
        }, 800);
    }

    function nextSlide() {
        const nextIndex = (currentSlideIndex + 1) % carouselSlides.length;
        activateSlide(nextIndex);
    }

    function prevSlide() {
        const prevIndex = (currentSlideIndex - 1 + carouselSlides.length) % carouselSlides.length;
        activateSlide(prevIndex);
    }

    // Navigation button clicks
    navButtons.forEach((button, index) => {
        button.addEventListener('click', () => {
            activateSlide(index);
            resetAutoAdvance();
        });
    });

    // Arrow button clicks
    if (prevArrow) {
        prevArrow.addEventListener('click', () => {
            prevSlide();
            resetAutoAdvance();
        });
    }

    if (nextArrow) {
        nextArrow.addEventListener('click', () => {
            nextSlide();
            resetAutoAdvance();
        });
    }

    // Keyboard navigation
    carousel.setAttribute('tabindex', '0');
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            resetAutoAdvance();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            resetAutoAdvance();
        }
    });

    // Auto-advance functionality
    function startAutoAdvance() {
        if (!autoPlay) return;
        autoAdvanceInterval = setInterval(() => {
            nextSlide();
        }, slideDuration);
    }

    function resetAutoAdvance() {
        clearInterval(autoAdvanceInterval);
        startAutoAdvance();
    }

    // Pause on hover
    carousel.addEventListener('mouseenter', () => {
        clearInterval(autoAdvanceInterval);
    });

    carousel.addEventListener('mouseleave', () => {
        if (autoPlay) {
            startAutoAdvance();
        }
    });

    // Start auto-advance
    startAutoAdvance();

    // Touch/Swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;

    function handleGesture() {
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;

        // Only trigger if horizontal swipe is greater than vertical
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX < 0) {
                nextSlide();
            } else {
                prevSlide();
            }
            resetAutoAdvance();
        }
    }

    if (viewport) {
        viewport.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        viewport.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleGesture();
        }, { passive: true });

        // Desktop: Mouse drag
        let startX = 0;
        let isDragging = false;

        viewport.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            viewport.classList.add('dragging');
        });

        viewport.addEventListener('mouseup', (e) => {
            if (!isDragging) return;

            isDragging = false;
            viewport.classList.remove('dragging');

            const deltaX = e.clientX - startX;
            if (Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    prevSlide();
                } else {
                    nextSlide();
                }
                resetAutoAdvance();
            }
        });

        viewport.addEventListener('mouseleave', () => {
            isDragging = false;
            viewport.classList.remove('dragging');
        });
    }
}