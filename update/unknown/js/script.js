// ULAF Website JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Initialize all functionality
  initNavbar()
  initScrollAnimations()
  initCarousel()
  initTeamsCarousel()
  initGallery()
  initLazyLoading()

  // Add input event listeners for real-time validation
  const newsletterForm = document.getElementById("newsletterForm")
  if (newsletterForm) {
    const inputs = newsletterForm.querySelectorAll("input")
    inputs.forEach((input) => {
      input.addEventListener("input", () => {
        if (input.classList.contains("is-invalid")) {
          // Re-validate on input change
          if (input.type === "email") {
            if (validateEmail(input.value.trim())) {
              input.classList.remove("is-invalid")
              input.classList.add("is-valid")
            }
          } else if (input.type === "checkbox") {
            if (input.checked) {
              input.classList.remove("is-invalid")
              input.classList.add("is-valid")
            }
          } else {
            if (input.value.trim()) {
              input.classList.remove("is-invalid")
              input.classList.add("is-valid")
            }
          }
        }
      })
    })
  }

  console.log("ULAF Website initialized successfully")
})

// Navbar scroll effect
function initNavbar() {
  const navbar = document.getElementById("navbar")
  const navLinks = document.querySelectorAll(".navbar-nav .nav-link")

  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled")
    } else {
      navbar.classList.remove("scrolled")
    }

    // Update active navigation link based on scroll position
    updateActiveNavLink()
  })

  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        // Update active state immediately
        updateActiveNavLink(this.getAttribute("href"))

        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })

        // Close mobile menu if open
        const navbarCollapse = document.querySelector(".navbar-collapse")
        if (navbarCollapse.classList.contains("show")) {
          const bsCollapse = new window.bootstrap.Collapse(navbarCollapse)
          bsCollapse.hide()
        }
      }
    })
  })
}

function updateActiveNavLink(targetHref = null) {
  const navLinks = document.querySelectorAll(".navbar-nav .nav-link")
  const sections = document.querySelectorAll("section[id]")

  if (targetHref) {
    // Manually set active link
    navLinks.forEach((link) => {
      link.classList.remove("active")
      if (link.getAttribute("href") === targetHref) {
        link.classList.add("active")
      }
    })
    return
  }

  // Auto-detect active section based on scroll position
  let current = ""
  sections.forEach((section) => {
    const sectionTop = section.offsetTop - 100
    const sectionHeight = section.offsetHeight
    if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
      current = section.getAttribute("id")
    }
  })

  // Default to home if at top
  if (window.scrollY < 100) {
    current = "hero"
  }

  navLinks.forEach((link) => {
    link.classList.remove("active")
    if (link.getAttribute("href") === `#${current}` || (current === "hero" && link.getAttribute("href") === "#home")) {
      link.classList.add("active")
    }
  })
}

// Scroll animations
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible")
      }
    })
  }, observerOptions)

  // Observe all elements with fade-in class
  document.querySelectorAll(".fade-in").forEach((el) => {
    observer.observe(el)
  })
}

// Carousel initialization
function initCarousel() {
  const carousel = document.querySelector("#heroCarousel")
  if (carousel) {
    const bsCarousel = new window.bootstrap.Carousel(carousel, {
      interval: 5000,
      wrap: true,
      pause: "hover",
      keyboard: true,
      touch: true,
    })

    // Pause carousel when user interacts with navigation
    document.querySelectorAll(".navbar-nav .nav-link").forEach((link) => {
      link.addEventListener("click", () => {
        bsCarousel.pause()
        setTimeout(() => bsCarousel.cycle(), 3000)
      })
    })
  }
}

// Load more news articles
function loadMoreNews() {
  // Simulate loading more articles
  showNotification("Loading more articles...", "info")

  // In a real application, this would fetch more articles from an API
  setTimeout(() => {
    showNotification("More articles loaded successfully!", "success")
  }, 1500)
}

// Newsletter form handling
function handleNewsletterSubmit(event) {
  event.preventDefault()

  const form = event.target
  const firstName = form.querySelector("#firstName").value.trim()
  const lastName = form.querySelector("#lastName").value.trim()
  const email = form.querySelector("#emailAddress").value.trim()
  const agreeTerms = form.querySelector("#agreeTerms").checked

  // Reset previous validation states
  form.classList.remove("was-validated")

  // Validate form
  let isValid = true

  if (!firstName) {
    form.querySelector("#firstName").classList.add("is-invalid")
    isValid = false
  } else {
    form.querySelector("#firstName").classList.remove("is-invalid")
    form.querySelector("#firstName").classList.add("is-valid")
  }

  if (!lastName) {
    form.querySelector("#lastName").classList.add("is-invalid")
    isValid = false
  } else {
    form.querySelector("#lastName").classList.remove("is-invalid")
    form.querySelector("#lastName").classList.add("is-valid")
  }

  if (!validateEmail(email)) {
    form.querySelector("#emailAddress").classList.add("is-invalid")
    isValid = false
  } else {
    form.querySelector("#emailAddress").classList.remove("is-invalid")
    form.querySelector("#emailAddress").classList.add("is-valid")
  }

  if (!agreeTerms) {
    form.querySelector("#agreeTerms").classList.add("is-invalid")
    isValid = false
  } else {
    form.querySelector("#agreeTerms").classList.remove("is-invalid")
    form.querySelector("#agreeTerms").classList.add("is-valid")
  }

  if (isValid) {
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]')
    const originalText = submitBtn.innerHTML
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Subscribing...'
    submitBtn.disabled = true

    // Simulate API call
    setTimeout(() => {
      // Show success message
      showNotification(`Thank you ${firstName}! You've been successfully subscribed to our newsletter.`, "success")

      // Reset form
      form.reset()
      form.querySelectorAll(".is-valid").forEach((el) => el.classList.remove("is-valid"))

      // Reset button
      submitBtn.innerHTML = originalText
      submitBtn.disabled = false

      // Track subscription (in real app, this would be sent to analytics)
      console.log("Newsletter subscription:", { firstName, lastName, email })
    }, 2000)
  } else {
    form.classList.add("was-validated")
    showNotification("Please fill in all required fields correctly.", "error")
  }
}

// Enhanced email validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email) && email.length >= 5 && email.length <= 254
}

// Notification system
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `alert alert-${type === "error" ? "danger" : type === "info" ? "info" : "success"} alert-dismissible fade show position-fixed`
  notification.style.cssText = "top: 100px; right: 20px; z-index: 9999; min-width: 300px;"
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  document.body.appendChild(notification)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove()
    }
  }, 5000)
}

// Lazy loading for images
function initLazyLoading() {
  const images = document.querySelectorAll("img[data-src]")
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target
        img.src = img.dataset.src
        img.classList.remove("lazy")
        imageObserver.unobserve(img)
      }
    })
  })

  images.forEach((img) => imageObserver.observe(img))
}

// Gallery functionality
let currentImageIndex = 0
let galleryImages = []

function initGallery() {
  // Collect all gallery images
  galleryImages = Array.from(document.querySelectorAll(".gallery-image"))

  // Add click event listeners to gallery images
  galleryImages.forEach((img, index) => {
    img.addEventListener("click", () => {
      currentImageIndex = index
      openGalleryModal(img)
    })
  })

  // Initialize gallery tabs
  const galleryTabs = document.querySelectorAll("#galleryTabs button")
  galleryTabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
      filterGalleryByCategory(e.target.getAttribute("data-bs-target"))
    })
  })
}

function openGalleryModal(img) {
  const modal = document.getElementById("galleryModal")
  const modalImage = document.getElementById("galleryModalImage")
  const modalTitle = document.getElementById("galleryModalLabel")

  modalImage.src = img.getAttribute("data-bs-image")
  modalTitle.textContent = img.getAttribute("data-bs-title")
}

function previousImage() {
  currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : galleryImages.length - 1
  const img = galleryImages[currentImageIndex]
  openGalleryModal(img)
}

function nextImage() {
  currentImageIndex = currentImageIndex < galleryImages.length - 1 ? currentImageIndex + 1 : 0
  const img = galleryImages[currentImageIndex]
  openGalleryModal(img)
}

function filterGalleryByCategory(category) {
  const allItems = document.querySelectorAll(".gallery-item")

  if (category === "#all-gallery") {
    // Show all items
    allItems.forEach((item) => {
      item.closest(".col-lg-4").style.display = "block"
      item.closest(".col-lg-4").classList.add("fade-in")
    })
  } else {
    // Filter by category
    const targetCategory = category.replace("#", "").replace("-gallery", "")

    allItems.forEach((item) => {
      const itemCategory = item.getAttribute("data-category")
      const container = item.closest(".col-lg-4")

      if (itemCategory === targetCategory) {
        container.style.display = "block"
        container.classList.add("fade-in")
      } else {
        container.style.display = "none"
      }
    })
  }

  // Re-trigger scroll animations for visible items
  setTimeout(() => {
    const visibleItems = document.querySelectorAll('.gallery-item:not([style*="display: none"]) .fade-in')
    visibleItems.forEach((item) => {
      item.classList.add("visible")
    })
  }, 100)
}

function loadMoreGallery() {
  showNotification("Loading more photos...", "info")

  // Simulate loading more images
  setTimeout(() => {
    // In a real application, this would fetch more images from an API
    showNotification("More photos loaded successfully!", "success")
  }, 1500)
}

document.addEventListener("keydown", (e) => {
  const modal = document.getElementById("galleryModal")
  const isModalOpen = modal.classList.contains("show")

  if (isModalOpen) {
    if (e.key === "ArrowLeft") {
      e.preventDefault()
      previousImage()
    } else if (e.key === "ArrowRight") {
      e.preventDefault()
      nextImage()
    } else if (e.key === "Escape") {
      const bsModal = window.bootstrap.Modal.getInstance(modal)
      if (bsModal) {
        bsModal.hide()
      }
    }
  }
})

// Statistics functionality
let currentView = "cards"
let currentSortColumn = -1
let sortDirection = "asc"

// Toggle between cards and table view
function toggleView(view) {
  const cardsView = document.getElementById("statsCards")
  const tableView = document.getElementById("statsTable")
  const buttons = document.querySelectorAll(".btn-group .btn")

  buttons.forEach((btn) => btn.classList.remove("active"))

  if (view === "cards") {
    cardsView.classList.remove("d-none")
    tableView.classList.add("d-none")
    buttons[0].classList.add("active")
    currentView = "cards"
  } else {
    cardsView.classList.add("d-none")
    tableView.classList.remove("d-none")
    buttons[1].classList.add("active")
    currentView = "table"
  }
}

// Filter statistics by team and position
function filterStats() {
  const teamFilter = document.getElementById("teamFilter").value
  const positionFilter = document.getElementById("positionFilter").value

  if (currentView === "cards") {
    const cards = document.querySelectorAll(".stats-card").forEach((card) => {
      const cardContainer = card.closest("[data-team]")
      const team = cardContainer.getAttribute("data-team")
      const position = cardContainer.getAttribute("data-position")

      const teamMatch = !teamFilter || team === teamFilter
      const positionMatch = !positionFilter || position === positionFilter

      if (teamMatch && positionMatch) {
        cardContainer.style.display = "block"
        cardContainer.classList.add("fade-in")
      } else {
        cardContainer.style.display = "none"
      }
    })
  } else {
    const rows = document.querySelectorAll("#playerStatsTable tbody tr")
    rows.forEach((row) => {
      const team = row.getAttribute("data-team")
      const position = row.getAttribute("data-position")

      const teamMatch = !teamFilter || team === teamFilter
      const positionMatch = !positionFilter || position === positionFilter

      row.style.display = teamMatch && positionMatch ? "" : "none"
    })
  }
}

// Reset all filters
function resetFilters() {
  document.getElementById("teamFilter").value = ""
  document.getElementById("positionFilter").value = ""
  document.getElementById("statCategory").value = "offensive"

  // Show all items
  if (currentView === "cards") {
    document.querySelectorAll(".stats-card").forEach((card) => {
      card.closest("[data-team]").style.display = "block"
    })
  } else {
    document.querySelectorAll("#playerStatsTable tbody tr").forEach((row) => {
      row.style.display = ""
    })
  }

  showNotification("Filters reset successfully", "info")
}

// Change statistics category (offensive/defensive)
function changeStatCategory() {
  const category = document.getElementById("statCategory").value
  const tableHeaders = document.querySelectorAll("#playerStatsTable th")

  if (category === "offensive") {
    tableHeaders[3].textContent = "Yards"
    tableHeaders[4].textContent = "TDs"
    tableHeaders[5].textContent = "Avg"
  } else {
    tableHeaders[3].textContent = "Tackles"
    tableHeaders[4].textContent = "Sacks"
    tableHeaders[5].textContent = "INTs"
  }

  showNotification(`Switched to ${category} statistics`, "info")
}

// Sort table by column
function sortTable(columnIndex) {
  const table = document.getElementById("playerStatsTable")
  const tbody = table.querySelector("tbody")
  const rows = Array.from(tbody.querySelectorAll("tr"))

  // Toggle sort direction if same column
  if (currentSortColumn === columnIndex) {
    sortDirection = sortDirection === "asc" ? "desc" : "asc"
  } else {
    sortDirection = "asc"
    currentSortColumn = columnIndex
  }

  // Sort rows
  rows.sort((a, b) => {
    const aValue = a.cells[columnIndex].textContent.trim()
    const bValue = b.cells[columnIndex].textContent.trim()

    // Check if values are numeric
    const aNum = Number.parseFloat(aValue)
    const bNum = Number.parseFloat(bValue)

    if (!isNaN(aNum) && !isNaN(bNum)) {
      return sortDirection === "asc" ? aNum - bNum : bNum - aNum
    } else {
      return sortDirection === "asc" ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue)
    }
  })

  // Clear and re-append sorted rows
  tbody.innerHTML = ""
  rows.forEach((row) => tbody.appendChild(row))

  // Update header indicators
  const headers = table.querySelectorAll("th i")
  headers.forEach((icon, index) => {
    if (index === columnIndex) {
      icon.className = sortDirection === "asc" ? "bi bi-arrow-up" : "bi bi-arrow-down"
    } else {
      icon.className = "bi bi-arrow-down-up"
    }
  })
}

// Initialize teams carousel
function initTeamsCarousel() {
  const teamsCarousel = document.querySelector("#teamsCarousel")
  if (teamsCarousel) {
    new window.bootstrap.Carousel(teamsCarousel, {
      interval: 4000,
      wrap: true,
      pause: "hover",
    })
  }
}
