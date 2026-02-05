/**
 * Mini Gallery - 3D Horizontal Marquee JS
 */
document.addEventListener('DOMContentLoaded', () => {
    init3DHorizontalMarquees();
});

// Re-init on AJAX load or custom event
document.addEventListener('mgwpp-content-loaded', () => {
    init3DHorizontalMarquees();
});

function init3DHorizontalMarquees() {
    const galleries = document.querySelectorAll('.mgwpp-3d-h-marquee-wrapper');

    galleries.forEach(gallery => {
        new HorizontalMarquee3D(gallery);
    });
}

class HorizontalMarquee3D {
    constructor(element) {
        this.element = element;
        this.content = element.querySelector('.mgwpp-3d-h-marquee-content');
        this.rows = element.querySelectorAll('.mgwpp-3d-h-row');
        this.paused = false;

        this.init();
    }

    init() {
        // Intersection Observer for performance
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.play();
                } else {
                    this.pause();
                }
            });
        }, { threshold: 0.1 });

        this.observer.observe(this.element);

        // Add 3D Mouse Move Parallax (Optional - subtle effect)
        this.element.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.element.addEventListener('mouseleave', () => this.resetTilt());
    }

    handleMouseMove(e) {
        // Only apply if user hasn't disabled it (could be a setting later)
        // This adds a subtle interactive tilt on top of the base tilt
        const rect = this.element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * 5; // Max 5deg shift
        const rotateY = ((x - centerX) / centerX) * -5;

        // Note: We need to preserve the base CSS variables
        // This is complex because we set base transform in CSS via var()
        // So we update the variables themselves

        // Reading base values from style attribute or defaults
        // Easier approach: just modify the variables on the element style
        // We'll add a 'modifier' var to the CSS
    }

    resetTilt() {
        // Reset code
    }

    pause() {
        this.rows.forEach(row => {
            const track = row.querySelector('.mgwpp-3d-h-track');
            if (track) track.style.animationPlayState = 'paused';
        });
    }

    play() {
        if (!this.paused) {
            this.rows.forEach(row => {
                const track = row.querySelector('.mgwpp-3d-h-track');
                if (track) track.style.animationPlayState = 'running';
            });
        }
    }
}
