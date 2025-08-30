// Avoid `console` errors in browsers that lack a console.
(function() {
  const noop = function() {};
  const methods = [
    'assert',
    'clear',
    'count',
    'debug',
    'dir',
    'dirxml',
    'error',
    'exception',
    'group',
    'groupCollapsed',
    'groupEnd',
    'info',
    'log',
    'markTimeline',
    'profile',
    'profileEnd',
    'table',
    'time',
    'timeEnd',
    'timeline',
    'timelineEnd',
    'timeStamp',
    'trace',
    'warn'
  ];
  const console = window.console || {};

  for (const method of methods) {
    // Only stub undefined methods.
    if (!console[method]) {
      console[method] = noop;
    }
  }
})();

if (typeof jQuery === 'undefined') {
  console.warn("jQuery hasn't loaded");
} else {
  console.log(`jQuery ${jQuery.fn.jquery} has loaded`);
}

// Place any jQuery/helper plugins in here.

document.addEventListener('DOMContentLoaded', function() {
  console.log('Document is ready');
  // Place your code that needs to run after the document has finished loading

});

jQuery(document).ready(function() {
  // Place your code that needs to run after the document has finished loading
  const $ = jQuery

});

// Mobile menu toggle
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const navMenu = document.querySelector('#menu-header-navigation');

mobileMenuToggle.addEventListener('click', () => {
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
    });
});

// Image slider functionality
const slides = document.querySelectorAll('.slide');
let currentSlide = 0;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

// Auto-play slider
setInterval(nextSlide, 5000);

// Teams slider functionality
const teamsTrack = document.getElementById('teamsTrack');
const teamsPrev = document.getElementById('teamsPrev');
const teamsNext = document.getElementById('teamsNext');
const teamsIndicators = document.getElementById('teamsIndicators');
const teamCards = document.querySelectorAll('.team-card');

let currentTeamIndex = 0;
const cardsPerView = window.innerWidth > 768 ? 3 : 1;
const maxTeamIndex = Math.max(0, teamCards.length - cardsPerView);

// Create team indicators
for (let i = 0; i <= maxTeamIndex; i++) {
    const indicator = document.createElement('div');
    indicator.className = `slider-indicator ${i === 0 ? 'active' : ''}`;
    indicator.addEventListener('click', () => moveToTeam(i));
    teamsIndicators.appendChild(indicator);
}

function moveToTeam(index) {
    currentTeamIndex = Math.max(0, Math.min(index, maxTeamIndex));
    const translateX = -currentTeamIndex * (300 + 32); // card width + gap
    teamsTrack.style.transform = `translateX(${translateX}px)`;
    
    // Update indicators
    document.querySelectorAll('#teamsIndicators .slider-indicator').forEach((indicator, i) => {
        indicator.classList.toggle('active', i === currentTeamIndex);
    });
}

function nextTeam() {
    if (currentTeamIndex < maxTeamIndex) {
        moveToTeam(currentTeamIndex + 1);
    } else {
        moveToTeam(0);
    }
}

function prevTeam() {
    if (currentTeamIndex > 0) {
        moveToTeam(currentTeamIndex - 1);
    } else {
        moveToTeam(maxTeamIndex);
    }
}

teamsNext.addEventListener('click', nextTeam);
teamsPrev.addEventListener('click', prevTeam);

// Auto-play teams slider
setInterval(nextTeam, 4000);

// Gallery slider functionality
const galleryTrack = document.getElementById('galleryTrack');
const galleryPrev = document.getElementById('galleryPrev');
const galleryNext = document.getElementById('galleryNext');
const galleryIndicators = document.getElementById('galleryIndicators');
const gallerySlides = document.querySelectorAll('.gallery-slide');

let currentGalleryIndex = 0;
const maxGalleryIndex = gallerySlides.length - 1;

// Create gallery indicators
for (let i = 0; i <= maxGalleryIndex; i++) {
    const indicator = document.createElement('div');
    indicator.className = `slider-indicator ${i === 0 ? 'active' : ''}`;
    indicator.addEventListener('click', () => moveToGallery(i));
    galleryIndicators.appendChild(indicator);
}

function moveToGallery(index) {
    currentGalleryIndex = Math.max(0, Math.min(index, maxGalleryIndex));
    const translateX = -currentGalleryIndex * 100; // 100% per slide
    galleryTrack.style.transform = `translateX(${translateX}%)`;
    
    // Update indicators
    document.querySelectorAll('#galleryIndicators .slider-indicator').forEach((indicator, i) => {
        indicator.classList.toggle('active', i === currentGalleryIndex);
    });
}

function nextGallery() {
    if (currentGalleryIndex < maxGalleryIndex) {
        moveToGallery(currentGalleryIndex + 1);
    } else {
        moveToGallery(0);
    }
}

function prevGallery() {
    if (currentGalleryIndex > 0) {
        moveToGallery(currentGalleryIndex - 1);
    } else {
        moveToGallery(maxGalleryIndex);
    }
}

galleryNext.addEventListener('click', nextGallery);
galleryPrev.addEventListener('click', prevGallery);

// Auto-play gallery slider
setInterval(nextGallery, 6000);

// Lightbox functionality
const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxClose = document.getElementById('lightboxClose');

gallerySlides.forEach(slide => {
    slide.addEventListener('click', () => {
        const bgImage = slide.style.backgroundImage;
        const imageUrl = bgImage.slice(5, -2); // Remove 'url("' and '")'
        lightboxImage.src = imageUrl;
        lightbox.classList.add('active');
    });
});

lightboxClose.addEventListener('click', () => {
    lightbox.classList.remove('active');
});

lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
        lightbox.classList.remove('active');
    }
});

// Statistics filtering
const filterButtons = document.querySelectorAll('.filter-btn');
const tableRows = document.querySelectorAll('.stats-table tbody tr');

filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');

        const filter = button.dataset.filter;

        tableRows.forEach(row => {
            if (filter === 'all') {
                row.style.display = '';
            } else {
                const position = row.dataset.position;
                if (position === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
});

// Newsletter form
const newsletterForm = document.querySelector('.newsletter-form');
newsletterForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = e.target.querySelector('.newsletter-input').value;
    alert(`Thank you for subscribing with email: ${email}`);
    e.target.reset();
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
        }
    });
}, observerOptions);

// Observe all sections
document.querySelectorAll('.section').forEach(section => {
    observer.observe(section);
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.15)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    }
});

console.log('All Scripts Loaded');
