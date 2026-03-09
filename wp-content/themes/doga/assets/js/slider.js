jQuery(document).ready(function ($) {
    // Our Systems Slider Start
    const ourSystemsSlider = new Swiper('#ourSystemsSlider', {
        // watchOverflow: true,
        navigation: {
            nextEl: '.m-our-systems-next',
            prevEl: '.m-our-systems-prev',
        },
    });
    // Our Systems Slider End

    // Product Category Slider Start
    const productCategorySlider = new Swiper('.productCategorySlider', {
        slidesPerView: "auto",
        breakpoints: {
            320: {
            spaceBetween: 10,
            },
            576: {
            spaceBetween: 20,
            },
        },
    });
    // Product Category Slider End

    // Product Listing Slider Start
    const productListingSlider = new Swiper('.product-listing-slider', {
        slidesPerView: 1,
        navigation: {
            nextEl: '.p-image-slider-next',
            prevEl: '.p-image-slider-prev',
        },
    });
    // Product Listing Slider End

    // Filter Wrapper Start
    $(function () {
        $('.filter-wrapper .accordion .accordion-collapse.show').each(function () {
            new bootstrap.Collapse(this, { toggle: false });
        });

        $(document).click(function (e) {
            if (!$(e.target).closest('.filter-wrapper .accordion').length) {
                $('.filter-wrapper .accordion .accordion-collapse.show').each(function () {
                    bootstrap.Collapse.getInstance(this)?.hide();
                });
            }
        });
    });
    // Filter Wrapper End

    // ==================== Product Inner Page Slider Start ====================

    // Thumb Slider Start
    const sliderThumb = new Swiper(".slider-thumb", {
        // loop: true,
        watchSlidesProgress: true,
        spaceBetween: 0,
        breakpoints: {
            320: {
                direction:'horizontal',
                slidesPerView: 3,
            },
            768: {
                direction:'vertical',  
                slidesPerView: 3,
            },
            992: {
                direction:'vertical',  
                slidesPerView: "auto",
            },
        },
    });
    // Thumb Slider End

    // Main Slider Start
    const sliderMain = new Swiper(".slider-main", {
        loop: true,
        effect:"fade",
        thumbs: {
            swiper: sliderThumb,
        },
        on: {
            slideChange: function () {
            sliderThumb.slideTo(this.activeIndex);
            },
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
    // Main Slider End

    // Fancybox Start

    Fancybox.bind('[data-fancybox="gallery"]', {
    
    });

    $('.search-icon').on('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior

        // Get the active image and index
        let activeSlide = $('.product-inner-page-card .slider-main .swiper-slide-active');
        let currentIMG = activeSlide.find('.img-container').attr('href');
        let galleryItems = $('[data-fancybox="gallery"]').map(function () {
            return {
                src: $(this).attr('href'),
                type: 'image'
            };
        }).get();
        let currentSlideNumber = $('[data-fancybox="gallery"]').index(activeSlide.find('.img-container'));

        // Open Fancybox manually
        Fancybox.show(galleryItems, {
            startIndex: currentSlideNumber
        });
    });

    // Fancybox End
    // ==================== Product Inner Page Slider End====================

    // Product Category Slider Start
    const washerPumpSlider = new Swiper('.washer-pump-slider', {
        slidesPerView: "auto",
        breakpoints: {
            320: {
            spaceBetween: 14,
            },
            992: {
            spaceBetween: 32,
            },
        },
    });
    // Product Category Slider End

    // Common Plastic Slider Start
    const commonPlasticSlider = new Swiper('.common-plastic-slider', {
        slidesPerView: "auto",
        breakpoints: {
            320: {
            spaceBetween: 12,
            },
            992: {
            spaceBetween: 16,
            },
        },
        pagination: {
            el: '.plastic-slider-pagination',
            type: "progressbar",
        },
    });
    // Common Plastic Slider End

    // Common Plastic Type Slider Start ( Accessories Page )
	const accePlasticSlider = new Swiper('.acce-plastic-slider', {
		slidesPerView: "auto",
		breakpoints: {
			320: {
				spaceBetween: 14,
			},
			992: {
				spaceBetween: 20,
			},
		},
		pagination: {
			el: '.acce-plastic-slider-pagination',
			type: "progressbar",
		},
		navigation: {
			nextEl: '.acce-plastic-slider-next',
			prevEl: '.acce-plastic-slider-prev',
		},
	});
    // Common Plastic Type Slider Start ( Accessories Page )
});