
document.addEventListener("DOMContentLoaded", function () {
    const galleries = document.querySelectorAll(".mgwpp-gallery-container");

    galleries.forEach(container => {
        const gridContainer = container.querySelector(".mgwpp-grid-container");
        if (!gridContainer) return;

        const layoutBtns = container.querySelectorAll(".mgwpp-layout-btn");
        let currentLayout = gridContainer.getAttribute("data-layout") || "grid";
        let intervalRef;

        // Carousel functionality
        function initCarousel() {
            // Use current container's grid
            const wrapper = gridContainer;

            // Check visibility/dimensions
            if (wrapper.clientWidth === 0) {
                // Try again later if hidden
                setTimeout(initCarousel, 200);
                return;
            }

            let slides = Array.from(wrapper.querySelectorAll(".mgwpp-grid-item"));
            if (slides.length < 3) return; // Need at least 3 slides for this carousel logic

            let currentIndex = 0;
            let isDragging = false;
            let startX = 0, currentTranslate = 0, prevTranslate = 0, isAnimating = false;
            const slideWidth = wrapper.clientWidth / 3;

            // Remove old clones
            wrapper.querySelectorAll(".clone").forEach(clone => clone.remove());

            // Clone start/end (3 each for loop effect)
            slides.slice(-3).forEach(slide => {
                const clone = slide.cloneNode(true);
                clone.classList.add("clone");
                wrapper.insertBefore(clone, wrapper.firstChild);
            });
            slides.slice(0, 3).forEach(slide => {
                const clone = slide.cloneNode(true);
                clone.classList.add("clone");
                wrapper.appendChild(clone);
            });

            slides = Array.from(wrapper.querySelectorAll(".mgwpp-grid-item"));
            wrapper.style.transition = "none";
            wrapper.style.transform = `translateX(-${slideWidth * 3}px)`;
            currentIndex = 3;

            function moveToIndex(index) {
                wrapper.style.transition = "transform 0.5s ease";
                wrapper.style.transform = `translateX(-${index * slideWidth}px)`;
            }

            function nextSlide() {
                if (isAnimating) return;
                isAnimating = true;
                currentIndex++;
                moveToIndex(currentIndex);
            }

            function prevSlide() {
                if (isAnimating) return;
                isAnimating = true;
                currentIndex--;
                moveToIndex(currentIndex);
            }

            wrapper.addEventListener("transitionend", () => {
                isAnimating = false;
                if (currentIndex >= slides.length - 3) {
                    currentIndex = 3;
                    wrapper.style.transition = "none";
                    wrapper.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
                }
                if (currentIndex < 3) {
                    currentIndex = slides.length - 6;
                    wrapper.style.transition = "none";
                    wrapper.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
                }
            });

            function dragStart(e) {
                if (isAnimating) return;
                isDragging = true;
                startX = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
                prevTranslate = -currentIndex * slideWidth;
                currentTranslate = prevTranslate;
                wrapper.style.transition = "none";
            }

            function dragMove(e) {
                if (!isDragging) return;
                const x = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
                const diff = x - startX;
                currentTranslate = prevTranslate + diff;
                wrapper.style.transform = `translateX(${currentTranslate}px)`;
            }

            function dragEnd() {
                if (!isDragging) return;
                isDragging = false;
                const movedBy = currentTranslate - prevTranslate;
                if (movedBy < -slideWidth / 4) {
                    nextSlide();
                } else if (movedBy > slideWidth / 4) {
                    prevSlide();
                } else {
                    moveToIndex(currentIndex);
                }
            }

            wrapper.addEventListener("mousedown", dragStart);
            wrapper.addEventListener("touchstart", dragStart, { passive: true });
            window.addEventListener("mousemove", dragMove);
            window.addEventListener("touchmove", dragMove, { passive: true });
            window.addEventListener("mouseup", dragEnd);
            window.addEventListener("touchend", dragEnd);

            // Autoplay
            intervalRef = setInterval(() => {
                if (!isDragging && (currentLayout === "grid" || currentLayout === "minimal")) {
                    nextSlide();
                }
            }, 3000);

            container.addEventListener("mouseenter", () => clearInterval(intervalRef));
            container.addEventListener("mouseleave", () => {
                if (currentLayout === "grid" || currentLayout === "minimal") {
                    clearInterval(intervalRef);
                    intervalRef = setInterval(() => nextSlide(), 3000);
                }
            });
        }

        function destroyCarousel() {
            clearInterval(intervalRef);
            const wrapper = gridContainer;
            wrapper.style.transition = "none";
            wrapper.style.transform = "none";
            wrapper.querySelectorAll(".clone").forEach(clone => clone.remove());

            // Re-clone to purge event listeners properly without replacing the element (which breaks refs)
            const newWrapper = wrapper.cloneNode(true);
            wrapper.parentNode.replaceChild(newWrapper, wrapper);
            // Wait, we need to update the gridContainer ref if we replace it
            // gridContainer = newWrapper; // and this is trapped in the closure
        }

        layoutBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                layoutBtns.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                currentLayout = btn.getAttribute("data-layout");
                gridContainer.setAttribute("data-layout", currentLayout);

                destroyCarousel();
                if (currentLayout !== "masonry") {
                    setTimeout(() => initCarousel(), 100);
                }
            });
        });

        // Init default layout
        if (currentLayout !== "masonry") {
            setTimeout(initCarousel, 150); // Small initial delay to ensure DOM is ready
        }
    });
});

