/**
 * APDB Product Slider - Horizontal Scroll Version
 * Funciona en frontend y Gutenberg editor
 */
(function() {
    'use strict';

    function initSlider(slider) {
        // Skip if already initialized
        if (slider.dataset.apdbInitialized === 'true') {
            return;
        }
        slider.dataset.apdbInitialized = 'true';

        var slidesContainer = slider.querySelector('[data-slides-container]');
        if (!slidesContainer) return;

        var slides = Array.prototype.slice.call(
            slidesContainer.querySelectorAll('.apdb-product-slider__slide')
        );
        if (!slides.length) return;

        var thumbsContainer = slider.querySelector('[data-thumbs-container]');
        var thumbs = thumbsContainer ? Array.prototype.slice.call(
            thumbsContainer.querySelectorAll('.apdb-product-slider__thumb')
        ) : [];

        var prevBtn = slider.querySelector('[data-action="prev"]');
        var nextBtn = slider.querySelector('[data-action="next"]');

        var lightbox = slider.querySelector('[data-lightbox]');
        var lightboxSlides = lightbox ? lightbox.querySelector('[data-lightbox-slides]') : null;
        var lightboxCloseTriggers = lightbox ? lightbox.querySelectorAll('[data-apdb-lightbox-close]') : [];
        var lightboxPrevBtn = lightbox ? lightbox.querySelector('[data-lightbox-action="prev"]') : null;
        var lightboxNextBtn = lightbox ? lightbox.querySelector('[data-lightbox-action="next"]') : null;

        var currentIndex = 0;

        // Lazy load images
        function loadImageForSlide(slide) {
            if (!slide) return;
            var img = slide.querySelector('img');
            if (!img) return;

            var dataSrc = img.getAttribute('data-src');
            if (dataSrc && !img.getAttribute('src')) {
                img.setAttribute('src', dataSrc);
                img.removeAttribute('data-src');
            }
        }

        // Scroll to specific slide
        function scrollToSlide(index) {
            if (index < 0 || index >= slides.length) return;

            var slide = slides[index];
            if (!slide) return;

            currentIndex = index;

            // Scroll con comportamiento suave
            slidesContainer.scrollTo({
                left: slide.offsetLeft,
                behavior: 'smooth'
            });

            // Lazy load de imágenes cercanas
            loadImageForSlide(slide);
            if (slides[index + 1]) loadImageForSlide(slides[index + 1]);
            if (slides[index - 1]) loadImageForSlide(slides[index - 1]);

            // Actualizar thumbnails
            updateThumbnails(index);

            // Scroll thumbnails para que el activo sea visible
            if (thumbs[index]) {
                scrollThumbnailIntoView(thumbs[index]);
            }
        }

        // Update thumbnail active state
        function updateThumbnails(index) {
            thumbs.forEach(function(thumb, i) {
                if (i === index) {
                    thumb.classList.add('is-active');
                } else {
                    thumb.classList.remove('is-active');
                }
            });
        }

        // Scroll thumbnail into view
        function scrollThumbnailIntoView(thumb) {
            if (!thumbsContainer || !thumb) return;

            var containerRect = thumbsContainer.getBoundingClientRect();
            var thumbRect = thumb.getBoundingClientRect();

            if (thumbRect.left < containerRect.left) {
                thumbsContainer.scrollTo({
                    left: thumbsContainer.scrollLeft - (containerRect.left - thumbRect.left) - 10,
                    behavior: 'smooth'
                });
            } else if (thumbRect.right > containerRect.right) {
                thumbsContainer.scrollTo({
                    left: thumbsContainer.scrollLeft + (thumbRect.right - containerRect.right) + 10,
                    behavior: 'smooth'
                });
            }
        }

        // Detect current slide based on scroll position
        function updateCurrentSlideFromScroll() {
            var scrollLeft = slidesContainer.scrollLeft;
            var slideWidth = slides[0] ? slides[0].offsetWidth : 0;
            if (!slideWidth) return;

            var newIndex = Math.round(scrollLeft / slideWidth);
            if (newIndex !== currentIndex && newIndex >= 0 && newIndex < slides.length) {
                currentIndex = newIndex;
                updateThumbnails(currentIndex);
                loadImageForSlide(slides[currentIndex]);
            }
        }

        // Navigation functions
        function goNext() {
            var nextIndex = currentIndex + 1;
            if (nextIndex >= slides.length) {
                nextIndex = 0; // Loop back to start
            }
            scrollToSlide(nextIndex);
        }

        function goPrev() {
            var prevIndex = currentIndex - 1;
            if (prevIndex < 0) {
                prevIndex = slides.length - 1; // Loop to end
            }
            scrollToSlide(prevIndex);
        }

        // Event listeners for main slider
        if (nextBtn) {
            nextBtn.addEventListener('click', goNext);
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', goPrev);
        }

        // Botón lupa — abre el lightbox en la imagen activa
        var zoomBtn = slider.querySelector('[data-action="zoom"]');
        if (zoomBtn) {
            zoomBtn.addEventListener('click', function() {
                openLightbox(currentIndex);
            });
        }

        // Thumbnail clicks
        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                var index = parseInt(thumb.getAttribute('data-index'), 10);
                if (!isNaN(index)) {
                    scrollToSlide(index);
                }
            });
        });

        // Scroll event to update current slide
        var scrollTimeout;
        slidesContainer.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(updateCurrentSlideFromScroll, 100);
        });

        // ========== LIGHTBOX ==========

        // Open lightbox
        slides.forEach(function(slide) {
            var img = slide.querySelector('img');
            if (!img) return;

            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function() {
                if (!lightbox || !lightboxSlides) return;

                var index = parseInt(slide.getAttribute('data-index'), 10);
                if (!isNaN(index)) {
                    openLightbox(index);
                }
            });
        });

        function openLightbox(index) {
            if (!lightbox || !lightboxSlides) return;

            lightbox.setAttribute('aria-hidden', 'false');
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';

            // Saltar directamente al slide correcto sin animación
            var lightboxSlideElements = lightboxSlides.querySelectorAll('.apdb-lightbox__slide');
            var targetSlide = lightboxSlideElements[index];
            if (targetSlide) {
                lightboxSlides.scrollTo({ left: targetSlide.offsetLeft, behavior: 'instant' });
            }
        }

        function closeLightbox() {
            if (!lightbox) return;

            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function scrollLightboxToSlide(index) {
            if (!lightboxSlides) return;

            var lightboxSlideElements = lightboxSlides.querySelectorAll('.apdb-lightbox__slide');
            var targetSlide = lightboxSlideElements[index];

            if (targetSlide) {
                lightboxSlides.scrollTo({
                    left: targetSlide.offsetLeft,
                    behavior: 'smooth'
                });
            }
        }

        function getLightboxCurrentIndex() {
            if (!lightboxSlides) return 0;

            var scrollLeft = lightboxSlides.scrollLeft;
            var lightboxSlideElements = lightboxSlides.querySelectorAll('.apdb-lightbox__slide');
            var slideWidth = lightboxSlideElements[0] ? lightboxSlideElements[0].offsetWidth : 0;

            if (!slideWidth) return 0;

            return Math.round(scrollLeft / slideWidth);
        }

        function lightboxGoNext() {
            var currentLightboxIndex = getLightboxCurrentIndex();
            var nextIndex = currentLightboxIndex + 1;

            if (nextIndex >= slides.length) {
                nextIndex = 0;
            }

            scrollLightboxToSlide(nextIndex);
        }

        function lightboxGoPrev() {
            var currentLightboxIndex = getLightboxCurrentIndex();
            var prevIndex = currentLightboxIndex - 1;

            if (prevIndex < 0) {
                prevIndex = slides.length - 1;
            }

            scrollLightboxToSlide(prevIndex);
        }

        // Lightbox close triggers (botón × y backdrop)
        Array.prototype.forEach.call(lightboxCloseTriggers, function(el) {
            el.addEventListener('click', closeLightbox);
        });

        // Cerrar al clicar el fondo (área blanca fuera de la imagen)
        if (lightbox) {
            lightbox.addEventListener('click', function(e) {
                if (
                    !e.target.closest('.apdb-lightbox__image') &&
                    !e.target.closest('.apdb-lightbox__caption') &&
                    !e.target.closest('.apdb-lightbox__arrow') &&
                    !e.target.closest('.apdb-lightbox__close')
                ) {
                    closeLightbox();
                }
            });
        }

        // Lightbox navigation buttons
        if (lightboxNextBtn) {
            lightboxNextBtn.addEventListener('click', lightboxGoNext);
        }

        if (lightboxPrevBtn) {
            lightboxPrevBtn.addEventListener('click', lightboxGoPrev);
        }

        // Keyboard navigation for lightbox
        function handleLightboxKeydown(event) {
            if (!lightbox || !lightbox.classList.contains('is-open')) return;

            if (event.key === 'Escape') {
                closeLightbox();
            } else if (event.key === 'ArrowRight') {
                lightboxGoNext();
            } else if (event.key === 'ArrowLeft') {
                lightboxGoPrev();
            }
        }

        document.addEventListener('keydown', handleLightboxKeydown);

        // Touch swipe support for mobile
        var touchStartX = 0;
        var touchEndX = 0;

        function handleSwipe() {
            var diff = touchStartX - touchEndX;
            var threshold = 50;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    goNext();
                } else {
                    goPrev();
                }
            }
        }

        slidesContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slidesContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        // Lightbox swipe
        if (lightboxSlides) {
            lightboxSlides.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            lightboxSlides.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                var diff = touchStartX - touchEndX;
                var threshold = 50;

                if (Math.abs(diff) > threshold) {
                    if (diff > 0) {
                        lightboxGoNext();
                    } else {
                        lightboxGoPrev();
                    }
                }
            }, { passive: true });
        }

        // Initialize
        scrollToSlide(0);
    }

    function initAllSliders() {
        var sliders = document.querySelectorAll('[data-apdb-slider]');
        sliders.forEach(function(slider) {
            initSlider(slider);
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllSliders);
    } else {
        initAllSliders();
    }

    // Re-initialize for Gutenberg editor (when ServerSideRender updates)
    if (window.wp && window.wp.domReady) {
        window.wp.domReady(function() {
            // Watch for new sliders added by ServerSideRender
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if (node.matches && node.matches('[data-apdb-slider]')) {
                                initSlider(node);
                            }
                            var nestedSliders = node.querySelectorAll && node.querySelectorAll('[data-apdb-slider]');
                            if (nestedSliders) {
                                nestedSliders.forEach(initSlider);
                            }
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }
})();
