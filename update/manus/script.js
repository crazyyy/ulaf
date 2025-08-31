// ULAF Website JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initNavigation();
    initScrollAnimations();
    initLoadingAnimations();
    initFormValidation();
    initImageLazyLoading();
});

// Navigation functionality
function initNavigation() {
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Smooth scrolling for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only handle internal links
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const offsetTop = targetElement.offsetTop - 76; // Account for fixed navbar
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                    
                    // Update active navigation link
                    updateActiveNavLink(href);
                    
                    // Close mobile menu if open
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                        bsCollapse.hide();
                    }
                }
            }
        });
    });
    
    // Update active navigation link based on scroll position
    window.addEventListener('scroll', throttle(updateActiveNavOnScroll, 100));
    
    // Navbar background opacity on scroll
    window.addEventListener('scroll', throttle(updateNavbarOnScroll, 50));
}

// Update active navigation link
function updateActiveNavLink(activeHref) {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === activeHref) {
            link.classList.add('active');
        }
    });
}

// Update active navigation based on scroll position
function updateActiveNavOnScroll() {
    const sections = document.querySelectorAll('section[id], footer[id]');
    const scrollPos = window.scrollY + 100; // Offset for navbar
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
            updateActiveNavLink(`#${sectionId}`);
        }
    });
}

// Update navbar appearance on scroll
function updateNavbarOnScroll() {
    const navbar = document.querySelector('.navbar');
    const scrollY = window.scrollY;
    
    if (scrollY > 50) {
        navbar.style.backgroundColor = 'rgba(0, 86, 179, 0.95)';
        navbar.style.backdropFilter = 'blur(10px)';
    } else {
        navbar.style.backgroundColor = '';
        navbar.style.backdropFilter = '';
    }
}

// Scroll animations
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(element => {
        observer.observe(element);
    });
}

// Loading animations
function initLoadingAnimations() {
    // Add loading states for dynamic content
    const loadingElements = document.querySelectorAll('[data-loading]');
    
    loadingElements.forEach(element => {
        const loadingSpinner = document.createElement('div');
        loadingSpinner.className = 'loading-spinner';
        loadingSpinner.setAttribute('aria-label', 'Loading content');
        element.appendChild(loadingSpinner);
    });
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isValid = validateForm(this);
            
            if (isValid) {
                handleFormSubmission(this);
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

// Validate entire form
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const isRequired = field.hasAttribute('required');
    
    // Clear previous errors
    clearFieldError(field);
    
    // Check if required field is empty
    if (isRequired && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (fieldType === 'tel' && value) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    return true;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        field.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

// Clear field error
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (errorElement) {
        errorElement.remove();
    }
}

// Handle form submission
function handleFormSubmission(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="loading-spinner me-2"></span>Sending...';
    
    // Simulate form submission (replace with actual API call)
    setTimeout(() => {
        // Reset button
        submitButton.disabled = false;
        submitButton.textContent = originalText;
        
        // Show success message
        showNotification('Thank you! Your message has been sent successfully.', 'success');
        
        // Reset form
        form.reset();
    }, 2000);
}

// Image lazy loading
function initImageLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}

// Utility functions
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

function debounce(func, delay) {
    let timeoutId;
    return function() {
        const args = arguments;
        const context = this;
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(context, args), delay);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Carousel auto-height adjustment
function adjustCarouselHeight() {
    const carousels = document.querySelectorAll('.carousel');
    
    carousels.forEach(carousel => {
        const items = carousel.querySelectorAll('.carousel-item');
        let maxHeight = 0;
        
        items.forEach(item => {
            const itemHeight = item.offsetHeight;
            if (itemHeight > maxHeight) {
                maxHeight = itemHeight;
            }
        });
        
        carousel.style.height = maxHeight + 'px';
    });
}

// Initialize tooltips and popovers
function initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Call initialization functions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initBootstrapComponents();
    adjustCarouselHeight();
});

// Recalculate carousel height on window resize
window.addEventListener('resize', debounce(adjustCarouselHeight, 250));

// Accessibility improvements
document.addEventListener('keydown', function(e) {
    // Skip to main content with Tab key
    if (e.key === 'Tab' && !e.shiftKey && document.activeElement === document.body) {
        const mainContent = document.querySelector('main');
        if (mainContent) {
            mainContent.focus();
            e.preventDefault();
        }
    }
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
        }, 0);
    });
}


// Enhanced Statistics Table Functionality
document.addEventListener('DOMContentLoaded', function() {
    initStatisticsTable();
    initGalleryFunctionality();
    initCountdownTimer();
    initNewsletterForms();
    initPartnerTabs();
    initBackToTop();
});

function initStatisticsTable() {
    const positionFilter = document.getElementById('positionFilter');
    const teamFilter = document.getElementById('teamFilter');
    const statCategory = document.getElementById('statCategory');
    const statsTable = document.getElementById('statsTable');
    const exportBtn = document.getElementById('exportStats');
    const refreshBtn = document.getElementById('refreshStats');
    
    if (!statsTable) return;
    
    // Filter functionality
    [positionFilter, teamFilter, statCategory].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', filterStatistics);
        }
    });
    
    function filterStatistics() {
        const tbody = statsTable.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        // Add loading animation
        tbody.style.opacity = '0.6';
        
        setTimeout(() => {
            // Filter logic would go here
            rows.forEach(row => {
                row.style.display = 'table-row';
            });
            
            tbody.style.opacity = '1';
            showNotification('Statistics filtered successfully', 'info');
        }, 500);
    }
    
    // Sortable headers
    const sortableHeaders = statsTable.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            sortTable(this.dataset.sort);
        });
    });
    
    function sortTable(sortType) {
        const tbody = statsTable.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Reset all sort indicators
        sortableHeaders.forEach(header => {
            const icon = header.querySelector('i');
            icon.className = 'fas fa-sort me-1';
        });
        
        // Set active sort indicator
        const activeHeader = document.querySelector(`[data-sort="${sortType}"]`);
        const activeIcon = activeHeader.querySelector('i');
        activeIcon.className = 'fas fa-sort-up me-1';
        
        // Simulate sorting animation
        tbody.style.opacity = '0.6';
        setTimeout(() => {
            tbody.style.opacity = '1';
            showNotification(`Sorted by ${sortType}`, 'info');
        }, 300);
    }
    
    // Export functionality
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
                showNotification('Statistics exported successfully!', 'success');
            }, 2000);
        });
    }
    
    // Refresh functionality
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                showNotification('Statistics refreshed!', 'info');
            }, 1500);
        });
    }
}

function initGalleryFunctionality() {
    const galleryTabs = document.querySelectorAll('#galleryTabs .nav-link');
    const loadMoreBtn = document.getElementById('loadMoreGallery');
    
    // Gallery tab animations
    galleryTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
            const galleryItems = targetPane.querySelectorAll('.gallery-item');
            
            // Stagger animation for gallery items
            galleryItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    });
    
    // Load more functionality
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading More...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
                showNotification('More photos loaded successfully!', 'success');
            }, 2000);
        });
    }
}

function initCountdownTimer() {
    const countdownElement = document.getElementById('days');
    if (!countdownElement) return;
    
    // Set target date for championship final
    const targetDate = new Date('2025-09-15T15:00:00').getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        if (distance > 0) {
            countdownElement.textContent = days;
            
            // Add urgency styling when less than 7 days
            if (days < 7) {
                countdownElement.parentElement.classList.add('urgent');
                countdownElement.style.color = '#dc3545';
                countdownElement.style.animation = 'pulse 1s infinite';
            }
        } else {
            countdownElement.textContent = '0';
            countdownElement.parentElement.querySelector('.countdown-label').textContent = 'GAME TIME!';
        }
    }
    
    // Update every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

function initNewsletterForms() {
    const newsletterForm = document.getElementById('newsletterForm');
    const quickForms = document.querySelectorAll('.newsletter-quick-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleNewsletterSubmission(this, false);
        });
    }
    
    quickForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleNewsletterSubmission(this, true);
        });
    });
    
    function handleNewsletterSubmission(form, isQuick) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        if (!isQuick) {
            // Validate full form
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
        } else {
            // Validate email only for quick form
            const emailInput = form.querySelector('input[type="email"]');
            if (!emailInput.value || !emailInput.checkValidity()) {
                emailInput.classList.add('is-invalid');
                return;
            }
            emailInput.classList.remove('is-invalid');
            emailInput.classList.add('is-valid');
        }
        
        // Show loading state
        submitBtn.innerHTML = isQuick ? 
            '<i class="fas fa-spinner fa-spin"></i>' : 
            '<i class="fas fa-spinner fa-spin me-2"></i>Subscribing...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            submitBtn.innerHTML = isQuick ? 
                '<i class="fas fa-check"></i>' : 
                '<i class="fas fa-check me-2"></i>Subscribed!';
            
            if (!isQuick) {
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
            }
            
            showNotification('Successfully subscribed to newsletter!', 'success');
            
            // Reset form
            setTimeout(() => {
                form.reset();
                if (!isQuick) {
                    form.classList.remove('was-validated');
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-primary');
                } else {
                    form.querySelector('input[type="email"]').classList.remove('is-valid');
                }
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        }, 2000);
    }
}

function initPartnerTabs() {
    const partnerTabs = document.querySelectorAll('#partnerTabs .nav-link');
    
    partnerTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
            const partnerCards = targetPane.querySelectorAll('.partner-card');
            
            // Animate partner cards
            partnerCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 75);
            });
        });
    });
}

function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', throttle(function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }, 100));
    
    // Smooth scroll to top
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Enhanced smooth scrolling for all anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const targetElement = document.querySelector(href);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    const navbarToggler = document.querySelector('.navbar-toggler');
                    if (navbarToggler) navbarToggler.click();
                }
            }
        });
    });
});

// Enhanced form validation for all forms
document.addEventListener('DOMContentLoaded', function() {
    const allForms = document.querySelectorAll('form');
    
    allForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateFieldEnhanced(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateFieldEnhanced(this);
                }
            });
        });
    });
    
    function validateFieldEnhanced(field) {
        const value = field.value.trim();
        const isValid = field.checkValidity();
        
        field.classList.remove('is-valid', 'is-invalid');
        
        if (value !== '') {
            if (isValid) {
                field.classList.add('is-valid');
            } else {
                field.classList.add('is-invalid');
            }
        }
    }
});

// Keyboard accessibility enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Make interactive elements keyboard accessible
    const interactiveElements = document.querySelectorAll('.gallery-item, .partner-card, .team-card, .news-card');
    
    interactiveElements.forEach(element => {
        element.setAttribute('tabindex', '0');
        element.setAttribute('role', 'button');
        
        element.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Skip to main content link
    const skipLink = document.createElement('a');
    skipLink.href = '#main';
    skipLink.className = 'skip-link position-absolute';
    skipLink.textContent = 'Skip to main content';
    skipLink.style.cssText = `
        top: -100px;
        left: 10px;
        z-index: 10000;
        background: #000;
        color: #fff;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 0 0 5px 5px;
        transition: top 0.3s;
    `;
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '0';
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-100px';
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
});

// Performance monitoring and optimization
document.addEventListener('DOMContentLoaded', function() {
    // Preload critical images
    const criticalImages = [
        'assets/images/hero-1.jpg',
        'assets/images/hero-2.jpg',
        'assets/images/hero-3.jpg',
        'assets/images/ulaf-logo.png',
        'assets/images/ulaf-logo-white.png'
    ];
    
    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });
    
    // Lazy load non-critical images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});

// Error handling and logging
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // In production, you might want to send this to a logging service
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    // In production, you might want to send this to a logging service
});

// Service Worker registration for PWA capabilities (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}

console.log('ULAF Website Enhanced JavaScript loaded successfully!');

