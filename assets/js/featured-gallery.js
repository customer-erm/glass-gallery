/**
 * Glass Gallery Pro - Featured Gallery Carousel & Modal
 * 5-Column Vertical Coverflow with Matching Main Gallery Modal
 */

(function() {
    'use strict';

    class FeaturedGallery {
        constructor(container) {
            this.container = container;
            this.imagesData = JSON.parse(container.dataset.images || '[]');
            this.currentIndex = 0;
            this.isCarousel = container.classList.contains('ggp-carousel-mode');
            this.modalIsActive = false; // Track modal state for this instance

            // Touch tracking
            this.touchStartX = 0;
            this.touchEndX = 0;
            this.isDragging = false;

            // Elements
            this.modal = container.querySelector('.ggp-modal');
            this.modalImage = this.modal.querySelector('.ggp-modal-image');
            this.modalTitle = this.modal.querySelector('.ggp-modal-title');
            this.modalCategories = this.modal.querySelector('.ggp-modal-categories');
            this.modalCurrent = this.modal.querySelector('.ggp-modal-current');

            this.init();
        }

        init() {
            if (this.isCarousel) {
                this.initCarousel();
            } else {
                this.initGrid();
            }
            this.initModal();
        }

        initCarousel() {
            this.track = this.container.querySelector('.ggp-carousel-track');
            this.slides = Array.from(this.container.querySelectorAll('.ggp-carousel-slide'));
            this.prevBtn = this.container.querySelector('.ggp-carousel-prev');
            this.nextBtn = this.container.querySelector('.ggp-carousel-next');
            this.slidesToShow = 4; // Show 4 columns
            this.currentPosition = 0;
            this.isTransitioning = false;

            // Clone slides for infinite scroll
            this.setupInfiniteScroll();

            // Navigation
            this.prevBtn.addEventListener('click', () => this.prevSlide());
            this.nextBtn.addEventListener('click', () => this.nextSlide());

            // Touch events
            this.track.addEventListener('touchstart', (e) => this.touchStart(e), { passive: true });
            this.track.addEventListener('touchmove', (e) => this.touchMove(e), { passive: false });
            this.track.addEventListener('touchend', () => this.touchEnd());

            // Click to open modal
            this.slides.forEach((slide, index) => {
                slide.addEventListener('click', () => {
                    if (!this.isDragging) {
                        // Calculate real index (accounting for clones)
                        const realIndex = index % this.imagesData.length;
                        this.openModal(realIndex);
                    }
                });
            });

            // Keyboard navigation for carousel (only when modal is NOT active)
            document.addEventListener('keydown', (e) => {
                // Only handle carousel keys if this instance's modal is NOT active
                if (this.modalIsActive) return;
                // Also check if any other modal is active (main gallery)
                if (document.getElementById('ggpModal')?.classList.contains('ggp-active')) return;
                if (e.key === 'ArrowLeft') this.prevSlide();
                if (e.key === 'ArrowRight') this.nextSlide();
            });

            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.updateCarousel(false);
                }, 100);
            });

            // Initialize position
            this.updateCarousel(false);
        }

        setupInfiniteScroll() {
            // Clone all slides and append to end
            const clones = [];
            this.slides.forEach(slide => {
                const clone = slide.cloneNode(true);
                clone.classList.add('clone');
                this.track.appendChild(clone);
                clones.push(clone);
            });

            // Add click handlers to clones
            clones.forEach((clone, index) => {
                clone.addEventListener('click', () => {
                    if (!this.isDragging) {
                        const realIndex = index % this.imagesData.length;
                        this.openModal(realIndex);
                    }
                });
            });

            // Update slides array to include clones
            this.allSlides = Array.from(this.track.querySelectorAll('.ggp-carousel-slide'));
        }

        initGrid() {
            const items = this.container.querySelectorAll('.ggp-featured-item');
            items.forEach((item, index) => {
                item.addEventListener('click', () => {
                    this.openModal(index);
                });
            });
        }

        initModal() {
            const closeBtn = this.modal.querySelector('.ggp-modal-close');
            const prevBtn = this.modal.querySelector('.ggp-modal-prev');
            const nextBtn = this.modal.querySelector('.ggp-modal-next');

            closeBtn.addEventListener('click', () => this.closeModal());
            prevBtn.addEventListener('click', () => this.modalPrev());
            nextBtn.addEventListener('click', () => this.modalNext());

            // Close on background click
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });

            // Keyboard navigation for modal (only when THIS modal is active)
            document.addEventListener('keydown', (e) => {
                // Only respond if THIS specific modal instance is active
                if (!this.modalIsActive) return;

                if (e.key === 'Escape') this.closeModal();
                if (e.key === 'ArrowLeft') this.modalPrev();
                if (e.key === 'ArrowRight') this.modalNext();
            });

            // Touch swipe in modal
            const imageArea = this.modal.querySelector('.ggp-modal-image-area');
            if (imageArea) {
                let touchStartX = 0;
                let touchEndX = 0;

                imageArea.addEventListener('touchstart', (e) => {
                    touchStartX = e.touches[0].clientX;
                }, { passive: true });

                imageArea.addEventListener('touchmove', (e) => {
                    touchEndX = e.touches[0].clientX;
                }, { passive: true });

                imageArea.addEventListener('touchend', () => {
                    const diff = touchStartX - touchEndX;
                    const threshold = 50;

                    if (Math.abs(diff) > threshold) {
                        if (diff > 0) {
                            this.modalNext();
                        } else {
                            this.modalPrev();
                        }
                    }
                });
            }
        }

        // Carousel Methods
        updateCarousel(animate = true) {
            // Get container width and calculate slide width
            const containerWidth = this.container.querySelector('.ggp-carousel-container').offsetWidth;
            const gap = 24; // 1.5rem in pixels
            const slideWidth = (containerWidth - (gap * 3)) / 4; // 4 columns with 3 gaps

            // Calculate offset
            const offset = -this.currentPosition * (slideWidth + gap);

            if (animate) {
                this.track.style.transition = 'transform 0.5s ease';
            } else {
                this.track.style.transition = 'none';
            }

            this.track.style.transform = `translateX(${offset}px)`;

            // Check for infinite loop reset
            if (animate) {
                this.track.addEventListener('transitionend', () => this.checkInfiniteReset(), { once: true });
            }
        }

        checkInfiniteReset() {
            // If we've scrolled past all original slides, reset to beginning
            if (this.currentPosition >= this.slides.length) {
                this.currentPosition = 0;
                this.updateCarousel(false);
            }
        }

        nextSlide() {
            if (this.isTransitioning) return;
            this.isTransitioning = true;

            // Move by 1 slide at a time
            this.currentPosition += 1;
            this.updateCarousel();

            setTimeout(() => {
                this.isTransitioning = false;
            }, 500);
        }

        prevSlide() {
            if (this.isTransitioning) return;
            this.isTransitioning = true;

            // Move by 1 slide at a time
            if (this.currentPosition === 0) {
                // Jump to end without animation
                this.currentPosition = this.slides.length;
                this.updateCarousel(false);
                // Then animate back one slide
                setTimeout(() => {
                    this.currentPosition -= 1;
                    this.updateCarousel();
                }, 50);
            } else {
                this.currentPosition -= 1;
                this.updateCarousel();
            }

            setTimeout(() => {
                this.isTransitioning = false;
            }, 500);
        }

        // Touch handlers for carousel
        touchStart(e) {
            this.touchStartX = e.touches[0].clientX;
            this.isDragging = false;
        }

        touchMove(e) {
            this.touchEndX = e.touches[0].clientX;
            const diff = Math.abs(this.touchStartX - this.touchEndX);

            // If moved more than 10px, it's a drag
            if (diff > 10) {
                this.isDragging = true;
                e.preventDefault();
            }
        }

        touchEnd() {
            if (!this.isDragging) return;

            const diff = this.touchStartX - this.touchEndX;
            const threshold = 50; // Minimum swipe distance

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }

            this.isDragging = false;
        }

        // Modal Methods
        openModal(index) {
            this.currentIndex = index;
            this.updateModal();

            this.modalIsActive = true; // Set flag before adding classes
            this.modal.classList.add('ggp-active');
            this.modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        closeModal() {
            this.modalIsActive = false; // Clear flag before removing classes
            this.modal.classList.remove('ggp-active');
            this.modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        updateModal() {
            const image = this.imagesData[this.currentIndex];

            if (!image) return;

            // Fade out image
            this.modalImage.style.opacity = '0';

            // Update content after brief fade
            setTimeout(() => {
                this.modalImage.src = image.full_image_url || image.image_url;
                this.modalImage.alt = image.title;
                this.modalTitle.textContent = image.title;
                this.modalCurrent.textContent = this.currentIndex + 1;

                // Get categories for this image (if available from WordPress terms)
                // Note: Categories would need to be added to images_data in PHP template
                this.updateCategories(image);

                // Fade in image
                this.modalImage.style.opacity = '1';
            }, 150);
        }

        updateCategories(image) {
            // Clear existing categories
            this.modalCategories.innerHTML = '';

            // If categories exist, display them as badges (matching main gallery style)
            if (image.categories && image.categories.length > 0) {
                image.categories.forEach(category => {
                    const badge = document.createElement('span');
                    badge.className = 'ggp-modal-cat-badge';
                    badge.textContent = category;
                    this.modalCategories.appendChild(badge);
                });
            }
        }

        modalNext() {
            this.currentIndex = (this.currentIndex + 1) % this.imagesData.length;
            this.updateModal();
        }

        modalPrev() {
            this.currentIndex = (this.currentIndex - 1 + this.imagesData.length) % this.imagesData.length;
            this.updateModal();
        }
    }

    // Initialize all featured galleries on page load
    function initFeaturedGalleries() {
        const galleries = document.querySelectorAll('.ggp-featured-gallery');
        galleries.forEach(gallery => {
            new FeaturedGallery(gallery);
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFeaturedGalleries);
    } else {
        initFeaturedGalleries();
    }

    // Re-initialize if content is loaded dynamically
    window.ggpInitFeaturedGalleries = initFeaturedGalleries;

})();
