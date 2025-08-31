/* ULAF Interactive JavaScript */

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNavigation();
    initializeScrollAnimations();
    initializeStatisticsTable();
    initializeNewsletterForm();
    initializeBackToTop();
    initializeGallery();
    initializeSmoothScrolling();
    initializePerformanceOptimizations();
});

// Navigation functionality
function initializeNavigation() {
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-link');

    // Handle active navigation state
    function updateActiveNav() {
        const scrollPosition = window.scrollY + 100;
        const sections = document.querySelectorAll('section[id]');
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');
            const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => link.classList.remove('active'));
                if (navLink) navLink.classList.add('active');
            }
        });
    }

    // Navbar background on scroll
    function handleNavbarScroll() {
        if (window.scrollY > 50) {
            navbar.style.backgroundColor = 'rgba(0, 102, 204, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.backgroundColor = 'var(--primary-blue)';
            navbar.style.backdropFilter = 'none';
        }
    }

    // Event listeners
    window.addEventListener('scroll', debounce(() => {
        updateActiveNav();
        handleNavbarScroll();
    }, 10));

    // Mobile menu close on link click
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse.classList.contains('show')) {
                bootstrap.Collapse.getInstance(navbarCollapse).hide();
            }
        });
    });
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Scroll animations
function initializeScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll, .animate-slide-left, .animate-slide-right');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    animatedElements.forEach(el => {
        observer.observe(el);
    });

    // Add animation classes to existing elements
    document.querySelectorAll('#about .row > div').forEach((el, index) => {
        el.classList.add(index % 2 === 0 ? 'animate-slide-left' : 'animate-slide-right');
    });

    document.querySelectorAll('.news-card, .card').forEach(el => {
        el.classList.add('animate-on-scroll');
    });
}

// Statistics table functionality
function initializeStatisticsTable() {
    const table = document.getElementById('statisticsTable');
    const positionFilter = document.getElementById('positionFilter');
    const teamFilter = document.getElementById('teamFilter');
    const sortableHeaders = document.querySelectorAll('.sortable');
    
    let currentSort = { column: -1, direction: 'asc' };
    let originalData = [];

    // Store original table data
    if (table) {
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        originalData = rows.map(row => {
            return Array.from(row.cells).map(cell => cell.textContent.trim());
        });

        // Sortable column headers
        sortableHeaders.forEach((header, index) => {
            header.addEventListener('click', () => sortTable(index));
        });

        // Filter functionality
        if (positionFilter) {
            positionFilter.addEventListener('change', filterTable);
        }
        if (teamFilter) {
            teamFilter.addEventListener('change', filterTable);
        }
    }

    function sortTable(columnIndex) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determine sort direction
        if (currentSort.column === columnIndex) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.direction = 'asc';
            currentSort.column = columnIndex;
        }

        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            // Check if values are numeric
            const aNum = parseFloat(aValue.replace(/,/g, ''));
            const bNum = parseFloat(bValue.replace(/,/g, ''));
            
            let comparison = 0;
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return currentSort.direction === 'asc' ? comparison : -comparison;
        });

        // Update table
        rows.forEach(row => tbody.appendChild(row));
        
        // Update header icons
        updateSortIcons(columnIndex);
    }

    function updateSortIcons(activeColumn) {
        sortableHeaders.forEach((header, index) => {
            const icon = header.querySelector('i');
            if (index === activeColumn) {
                icon.className = currentSort.direction === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
            } else {
                icon.className = 'fas fa-sort ms-1';
            }
        });
    }

    function filterTable() {
        const positionValue = positionFilter.value.toLowerCase();
        const teamValue = teamFilter.value.toLowerCase();
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const position = row.cells[2].textContent.toLowerCase();
            const team = row.cells[1].textContent.toLowerCase();
            
            const positionMatch = !positionValue || position.includes(positionValue);
            const teamMatch = !teamValue || team.includes(teamValue);
            
            if (positionMatch && teamMatch) {
                row.style.display = '';
                row.classList.add('animate-on-scroll');
            } else {
                row.style.display = 'none';
                row.classList.remove('animate-on-scroll');
            }
        });
    }
}

// Newsletter form functionality
function initializeNewsletterForm() {
    const form = document.getElementById('newsletterForm');
    const emailInput = document.getElementById('newsletterEmail');
    const successMessage = document.getElementById('newsletterSuccess');

    if (form && emailInput) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate email
            if (validateEmail(emailInput.value)) {
                // Simulate API call
                submitNewsletter(emailInput.value);
            } else {
                emailInput.classList.add('is-invalid');
                emailInput.focus();
            }
        });

        emailInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            successMessage.classList.add('d-none');
        });
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function submitNewsletter(email) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Loading state
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subscribing...';
        submitButton.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Success state
            emailInput.value = '';
            emailInput.classList.add('is-valid');
            successMessage.classList.remove('d-none');
            
            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            
            // Clear validation after 3 seconds
            setTimeout(() => {
                emailInput.classList.remove('is-valid');
            }, 3000);
        }, 1500);
    }
}

// Back to top button
function initializeBackToTop() {
    const backToTopButton = document.getElementById('backToTop');
    
    if (backToTopButton) {
        window.addEventListener('scroll', debounce(() => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        }, 100));

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Gallery initialization
function initializeGallery() {
    // Initialize GLightbox
    if (typeof GLightbox !== 'undefined') {
        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
            plyr: {
                css: 'https://cdn.plyr.io/3.6.8/plyr.css',
                js: 'https://cdn.plyr.io/3.6.8/plyr.js',
                config: {
                    ratio: '16:9',
                    youtube: {
                        noCookie: true,
                        rel: 0,
                        showinfo: 0,
                        iv_load_policy: 3
                    }
                }
            }
        });
    }

    // Gallery tab animations
    const galleryTabs = document.querySelectorAll('#galleryTabs button');
    galleryTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
            const images = targetPane.querySelectorAll('img');
            
            images.forEach((img, index) => {
                img.style.opacity = '0';
                img.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    img.style.transition = 'all 0.5s ease';
                    img.style.opacity = '1';
                    img.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    });
}

// Performance optimizations
function initializePerformanceOptimizations() {
    // Lazy loading for images not handled by native loading="lazy"
    const images = document.querySelectorAll('img:not([loading])');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('loading');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Preload critical images
    const criticalImages = [
        '/static/images/hero-1.jpg',
        '/static/images/ulaf-logo-white.png'
    ];

    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

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
    };
}

// Error handling for missing elements
window.addEventListener('error', function(e) {
    console.warn('ULAF Website Error:', e.error);
});

// Console welcome message
console.log(`
🏈 Welcome to ULAF Website
Ukrainian League of American Football
Built with Bootstrap 5, optimized for performance
`);

// Service Worker Registration (for future PWA features)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}