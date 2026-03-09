jQuery(document).ready(function($){
    
    var ajaxUrl = ajax_object.ajaxurl;
    var posts_per_page = ajax_object.posts_per_pages;
    var paged = ajax_object.paged;
    var chooseYourProductDataTermId;
    var chooseYourProductDataTermSlug;
    var productChildTermId;
    var productChildTermSlug;
    var searchValueTerm;
    var productSpeedMetaValuesArr = [];
    var productTorqueMetaValueArr = [];
    var productVoltageMetaValueArr = [];
    var productWeightMetaValueArr = [];
    var childCategorySliderContainer = $('.product-category-slider .child-category-slider');
    var productListingDataContainer = $('.drive-system-product-listing-grid');
    var paginationContainer = $('.product-custom-pagination.products-page');
    console.log('INIT: Variables inicializadas');


    let selectedTermIds = [];
    let currentMainTermId = null;
    let currentChildTermId = null;


    // Toggle Speed Metadata Values
    function toggleMetaDataValues(array, metaValueObj) {
    console.log('toggleMetaDataValues called', { array, metaValueObj });
        const index = array.findIndex(item => item.value === metaValueObj.value);

        if (index > -1) {
            array.splice(index, 1); // Remove if already exists
        } else {
            array.push(metaValueObj); // Add new
        }
    }

    // Utility: Replace a specific type of term ID in the shared array
    function updateSelectedTerm(type, newId) {
    console.log('updateSelectedTerm called', { type, newId });
        newId = parseInt(newId);

        // Ensure newId is a valid number
        if (isNaN(newId)) return;

        // Remove previous term ID of the same type
        if (type === 'main' && currentMainTermId !== null) {
            const index = selectedTermIds.indexOf(currentMainTermId);
            if (index > -1) selectedTermIds.splice(index, 1);
        }

        if (type === 'child' && currentChildTermId !== null) {
            const index = selectedTermIds.indexOf(currentChildTermId);
            if (index > -1) selectedTermIds.splice(index, 1);
        }

        // Add the new ID only if not already present
        if (!selectedTermIds.includes(newId)) {
            selectedTermIds.push(newId);
        }

        // Track the new current ID for the type
        if (type === 'main') currentMainTermId = newId;
        if (type === 'child') currentChildTermId = newId;

        // Optional: Debug
        // console.log("Selected Term IDs:", selectedTermIds);
    }

    // Product Category Parent & Direct Child terms 
    $('.product-page-choose-your-product .your-product-card').on('click',  function(e){
    console.log('Click en .your-product-card', { termId: chooseYourProductDataTermId, termSlug: chooseYourProductDataTermSlug });
        e.preventDefault();

        const $thisCard = $(this);
        chooseYourProductDataTermId = $(this).data('term-id');
        chooseYourProductDataTermSlug = $(this).data('term-slug');

        // Reset all term data
        selectedTermIds = [];
        currentMainTermId = null;
        currentChildTermId = null;

        updateSelectedTerm('main', chooseYourProductDataTermId);

        // Add button active class
        $('.btn-container a').removeClass('selected-product-category-term-filter');
        $thisCard.find('.btn-container a').addClass('selected-product-category-term-filter');


        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);
    });

    // Product Category Child's sub child terms
    $(document).on('click', '.product-sub-category-slider .swiper-slide .product-link-card', function(e){
    console.log('Click en .product-link-card', { termId: productChildTermId, termSlug: productChildTermSlug });
        e.preventDefault();

        productChildTermId = $(this).data('term-id');
        productChildTermSlug = $(this).data('term-slug');

        $('.product-link-card').removeClass('active-child-slide');
        $(this).addClass('active-child-slide');

        updateSelectedTerm('child', productChildTermId);

        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);
    });

    // Pagination
    $(document).on('click', '.products-page .custom-pagination a', function(e){
    console.log('Click en paginación productos', { pageUrl });

        // If the link is disabled, don't do anything
        if ($(this).hasClass('disabled')) {
            e.preventDefault();
            return false;
        }

        e.preventDefault();

        var pageUrl = $(this).attr('href');
        var pageNumber = null;

        // First try to match /page/page_number/
        var matchPagePath = pageUrl.match(/page\/(\d+)/);

        // If not found, try to match ?paged=3 or &paged=3
        if (matchPagePath && matchPagePath[1]) {
            pageNumber = matchPagePath[1];
        } else {
            var matchPageQuery = pageUrl.match(/[?&]paged=(\d+)/);
            if (matchPageQuery && matchPageQuery[1]) {
                pageNumber = matchPageQuery[1];
            }
        }

        // Fallback if not found
        if (!pageNumber) {
            pageNumber = 1; // default to first page
        }


        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,pageNumber,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);


    });


    // Product Speed Filters Values
    $('.product-speed-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro velocidad', { speedValue });
        e.preventDefault();

        var speedValue = $(this).val();
        const speedLabel = $(this).next('label').text().trim();
        toggleMetaDataValues(productSpeedMetaValuesArr, {
            value: speedValue,
            label: speedLabel
        });

        // Add - Remove Checked
        if ($(this).prop('checked')) {
            $(this).attr('checked', true);
        } else {
            $(this).attr('checked', false);
        }

        

        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);

    });

    // Product Torque Filters Values
    $('.product-torque-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro torque', { torqueValue });
        e.preventDefault();

        var torqueValue = $(this).val();
        var torqueLable = $(this).next('label').text().trim();
        toggleMetaDataValues(productTorqueMetaValueArr, {
            value: torqueValue,
            label: torqueLable
        });

        // Add - Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);

    });

    // Product Voltage Filters Values
    $('.product-voltage-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro voltaje', { voltageValue });
        e.preventDefault();

        var voltageValue = $(this).val();


        // Add Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

        toggleMetaDataValues(productVoltageMetaValueArr, {
            value: voltageValue,
        });

        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);
    });

    // Product Weight Filter
    $('.product-weight-meta-data .form-check-input').on('change', function(e){
        var weightValue = $(this).val();
        var weightLabel = $(this).next('label').text().trim();
        console.log('Cambio filtro peso', { weightValue });
        e.preventDefault();

        // Add Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

        toggleMetaDataValues(productWeightMetaValueArr,{
            value: weightValue,
            label: weightLabel,
        });

        chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);

    });

    // Search Filter
    let debounceTimer;
    $('.filter-right-block .product-serch-block input').on('input', function () {
    console.log('Input búsqueda productos', { searchValueTerm });

        clearTimeout(debounceTimer);
        searchValueTerm = $(this).val() || '';

        debounceTimer = setTimeout(() => {
            chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,productChildTermId,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);
        }, 300);
    });

    // Ajax Call
    chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,activeChildId = null,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm);
    
    function chooseYourProductAjaxCall(chooseYourProductDataTermId,chooseYourProductDataTermSlug,posts_per_page,paged,selectedTermIds,activeChildId = null,productSpeedMetaValuesArr,productTorqueMetaValueArr,productVoltageMetaValueArr,productWeightMetaValueArr,searchValueTerm){
    console.log('chooseYourProductAjaxCall called', { chooseYourProductDataTermId, chooseYourProductDataTermSlug, posts_per_page, paged, selectedTermIds, activeChildId, productSpeedMetaValuesArr, productTorqueMetaValueArr, productVoltageMetaValueArr, productWeightMetaValueArr, searchValueTerm });

        if( ($('.product-page-choose-your-product .your-product-card').length > 0) || ($('.product-sub-category-slider .swiper-slide .product-link-card').length > 0) || ($('.products-page .custom-pagination a').length > 0) || ($('.product-speed-meta-data .form-check-input').length > 0) || ($('.product-torque-meta-data .form-check-input').length > 0) || ($('.product-voltage-meta-data .form-check-input').length > 0) || ($('.product-weight-meta-data .form-check-input').length > 0) || ($('.filter-right-block .product-serch-block input').length > 0) ){
            $('.loader-overlay, .loader').show();
        }

        $.ajax({
            "url"    : ajaxUrl,
            "method" : "post",
            "data"   : {
                termId : chooseYourProductDataTermId,
                termSlug : chooseYourProductDataTermSlug,
                posts_per_pages : posts_per_page,
                paged : paged,
                selectedTerms : selectedTermIds,
                speedMetaValues : productSpeedMetaValuesArr,
                torqueMetaValues : productTorqueMetaValueArr,
                voltageMetaValues : productVoltageMetaValueArr,
                weightMetaValues : productWeightMetaValueArr,
                searchValue :   searchValueTerm, 
                action : 'choose_your_product_ajax_handler',
            },
            "success" : function(response){

                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var termData = jsonResponse.term_details;
                var productListingData = jsonResponse.product_data;
                var productPagination = jsonResponse.pagination_data;

                console.log('chooseYourProductAjaxCall response', { termData, productListingData, productPagination });

                if(termData!=''){
                    $('.product-category-slider').removeClass('hide-product-sub-category-slider');
                    $(childCategorySliderContainer).html(termData);
                }else{
                    $('.product-category-slider').addClass('hide-product-sub-category-slider');
                    $(childCategorySliderContainer).html();
                }
                if(productListingData){
                    $(productListingDataContainer).html(productListingData);
                    $(paginationContainer).html(productPagination);
                }else{
                    $(productListingDataContainer).html('<p> There are no any posts!</p>');
                    $(paginationContainer).html('');
                }
                initProductCategorySwiperSlider();

                // ✅ Ensure DOM is updated before applying active class
                if (activeChildId !== null) {
                    setTimeout(function() {
                        $(`.product-link-card[data-term-id="${activeChildId}"]`).addClass('active-child-slide');
                    }, 50); // delay to ensure rendering is complete
                }
            },
        });
    }

    function initProductCategorySwiperSlider() {
    console.log('initProductCategorySwiperSlider called');
        const productListingSlider = new Swiper('.product-listing-slider', {
            slidesPerView: 1,
            navigation: {
                nextEl: '.p-image-slider-next',
                prevEl: '.p-image-slider-prev',
            },
        });
        // No datos de salida relevantes
    }


    // Washer System Division Ajax
    var washerTermId;
    var washerSystemPumpsListingContainer = $('.washer-system-types-of-pumps');

    $(window).on('load', function(){
        washerTermId = $('.washer-types-of-pumps-card a.active').data('term-id');
        washerPumpAjaxCall(washerTermId);
    });
    $('.washer-types-of-pumps-card a').on('click', function(e){
    console.log('Click en washer-types-of-pumps-card', { washerTermId });
        e.preventDefault();
        washerTermId = $(this).data('term-id');

        // Add Remove Active Class
        $('.washer-types-of-pumps-card a').removeClass('active');
        $(this).addClass('active');

        washerPumpAjaxCall(washerTermId);
    });
    function washerPumpAjaxCall(washerTermId){
    console.log('washerPumpAjaxCall called', { washerTermId });

        if(($('.washer-types-of-pumps-card a').length > 0)){
            $('.loader-overlay, .loader').show();
        }
        $.ajax({
            'url': ajaxUrl,
            'method': 'post',
            'data':{
                termId: washerTermId,
                action: 'washer_system_pumps_ajax_handler',
            },
            'success':function(response){

                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var washerProductData = jsonResponse.post_data;

                console.log('washerPumpAjaxCall response', { washerProductData });

                if(washerProductData){
                    $(washerSystemPumpsListingContainer).html(washerProductData);
                }else{
                    $washerSystemPumpsListingContainer.html('<p>There are no any posts found for this Pump Type.</p>');
                }

            },
        });
    }


    /* Types Of Tanks Division Ajax Call */
    var tankTypeTermId;
    var tankListingContainer = $('.tanks-system-type-of-tanks-listing');

    $(window).on('load', function(){
        tankTypeTermId = $('.types-of-tank-card a.active').data('term-id');
        typesOfTankAjaxCall(tankTypeTermId);
    });
    $('.types-of-tank-card a').on('click', function(e){
        e.preventDefault();

        tankTypeTermId = $(this).data('term-id');
        
        // Add Remove Active Class
        $('.types-of-tank-card a').removeClass('active');
        $(this).addClass('active');

    console.log('Click en types-of-tank-card', { tankTypeTermId });
        typesOfTankAjaxCall(tankTypeTermId);
    });
    function typesOfTankAjaxCall(tankTypeTermId){
    console.log('typesOfTankAjaxCall called', { tankTypeTermId });

        if(($('.types-of-tank-card a').length > 0)){
            $('.loader-overlay, .loader').show();
        }
        $.ajax({
            "url":ajaxUrl,
            "method":"post",
            "data":{
                term_ID:tankTypeTermId,
                action:'types_of_tank_ajax_handler',
            },
            "success": function(response){

                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var product_data = jsonResponse.product_data;

                console.log('typesOfTankAjaxCall response', { product_data });

                if(product_data){
                    // console.log('TANKS AJAX HTML:', product_data);
                    $(tankListingContainer).html(product_data);
                }else{
                    $(tankListingContainer).html('<p> There are no any products. </p>');
                }

            },
        });
    }    

    /* Accessories Division Ajax Call */
    var accessoriesTermId;
    var accessoriesContainer = $('.accessories-listing-data');
    $(window).on('load', function(){
        accessoriesTermId = $('.accessories-term-card a.active').data('term-id');
        accessoriesAjaxCall(accessoriesTermId);
    });
    $('.accessories-term-card a').on('click', function(e){
        e.preventDefault();

        // Add Remove Active Class
        $('.accessories-term-card a').removeClass('active');
        $(this).addClass('active');
        
        accessoriesTermId = $(this).data('term-id');
        accessoriesAjaxCall(accessoriesTermId);
    });
    function accessoriesAjaxCall(accessoriesTermId){
    console.log('accessoriesAjaxCall called', { accessoriesTermId });

        if(($('.accessories-term-card a').length > 0)){
            $('.loader-overlay, .loader').show();
        }

        $.ajax({
            "url":ajaxUrl,
            "method":"post",
            "data":{
                term_ID:accessoriesTermId,
                action:'accessories_ajax_handler',
            },
            "success": function(response){
                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var product_data = jsonResponse.product_data;

                console.log('accessoriesAjaxCall response', { product_data });

                if(product_data){
                    //console.log('ACCESSORIES AJAX HTML:', product_data);
                    $(accessoriesContainer).html(product_data);
                }else{
                    $(accessoriesContainer).html('<p> There are no any products. </p>');
                }

            },
        });
    }

    /* Washer Tank Liter Filter */
    var washerSelectFilterValue ;
    var washerProductListingContainer = $('.wash-tank-select-leters-filter-listing-grid');
    $(window).on('load', function(){
        washerSelectFilterValue = $('.washer-select-filter').val();
        washerTanksLiterAjaxCall(washerSelectFilterValue);
    });
    $('.washer-select-filter').on('change', function(e){
        washerSelectFilterValue = $(this).val();
       
        washerTanksLiterAjaxCall(washerSelectFilterValue);
    });

    function washerTanksLiterAjaxCall(washerSelectFilterValue){
    console.log('washerTanksLiterAjaxCall called', { washerSelectFilterValue });

        if(($('.washer-select-filter').length > 0)){
            $('.loader-overlay, .loader').show();
        }

        $.ajax({
            "url":ajaxUrl,
            "method":'post',
            "data":{
                washerTankLiterMetaValue:washerSelectFilterValue,
                action:'washer_tanks_liter_filter_ajax_handler',
            },
            "success": function(response){

                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var product_data = jsonResponse.product_data;

                console.log('washerTanksLiterAjaxCall response', { product_data });
    console.log('Click en category-page-child-category-slider', { categoryChildTermID });

                if(product_data){
                    $(washerProductListingContainer).html(product_data);
                }else{
                    $(washerProductListingContainer).html('<p> There are not any posts found! </p>');
                }

                // console.log(product_data);
                
                initProductCategorySwiperSlider();
            },
        });        
    }

    /* Product Category Page Ajax Call */
    var themeUrl = ajax_object.theme_url;
    var taxonomy_posts_per_page = ajax_object.taxonomy_posts_per_page;
    var paged = ajax_object.paged;
    var categoryProductListingCotnainer = $('.product-category-product-listing-block');
    var categoryPagePaginationContainer = $('.product-custom-pagination.category-page');
    var categoryPageID = $('.products-wrapper.product-category-product-listing-block').data('term-id');


    var categoryProductSearchValueTerm;
    var categoryChildTermID;
    var categoryProductSpeedMetaValuesArr = [];
    var categoryProductTorqueMetaValueArr = [];
    var categoryProductVoltageMetaValueArr = [];
    var categoryProductWeightMetaValueArr = [];


    // Category Child Terms Filter
    $(document).on('click', '.category-page-child-category-slider .product-link-card', function(){
        categoryChildTermID = $(this).data('term-id');

        $('.product-link-card').removeClass('active-child-slide');
        $(this).addClass('active-child-slide');
        
        categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);

    });

    // Search Filter
    let categoryProductDebounceTimer;
    $('.filter-right-block .category-product-search-block input').on('input', function () {
    console.log('Input búsqueda categoría', { categoryProductSearchValueTerm });

        clearTimeout(categoryProductDebounceTimer);
        categoryProductSearchValueTerm = $(this).val() || '';

        categoryProductDebounceTimer = setTimeout(() => {
            categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);
        }, 300);
    });

    // Pagination
    $(document).on('click', '.category-page .custom-pagination a', function(e){
    console.log('Click en paginación categoría', { pageUrl });

        // If the link is disabled, don't do anything
        if ($(this).hasClass('disabled')) {
            e.preventDefault();
            return false;
        }

        e.preventDefault();

        var pageUrl = $(this).attr('href');
        var pageNumber = null;

        // First try to match /page/page_number/
        var matchPagePath = pageUrl.match(/page\/(\d+)/);

        // If not found, try to match ?paged=3 or &paged=3
        if (matchPagePath && matchPagePath[1]) {
            pageNumber = matchPagePath[1];
        } else {
            var matchPageQuery = pageUrl.match(/[?&]paged=(\d+)/);
            if (matchPageQuery && matchPageQuery[1]) {
                pageNumber = matchPageQuery[1];
            }
        }

        // Fallback if not found
        if (!pageNumber) {
            pageNumber = 1; // default to first page
        }

        categoryPageAjaxCall(taxonomy_posts_per_page,pageNumber,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);

    });    

    // Category Product Speed Filter
    $('.product-cat-speed-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro velocidad categoría', { speedValue });
        e.preventDefault();

        var speedValue = $(this).val();
        const speedLabel = $(this).next('label').text().trim();
        toggleMetaDataValues(categoryProductSpeedMetaValuesArr, {
            value: speedValue,
            label: speedLabel
        });

        // Add - Remove Checked
        if ($(this).prop('checked')) {
            $(this).attr('checked', true);
        } else {
            $(this).attr('checked', false);
        }
        
        categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);

    });

    // Product Torque Filters Values
    $('.product-cat-torque-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro torque categoría', { torqueValue });
        e.preventDefault();

        var torqueValue = $(this).val();
        var torqueLable = $(this).next('label').text().trim();
        toggleMetaDataValues(categoryProductTorqueMetaValueArr, {
            value: torqueValue,
            label: torqueLable
        });

        // Add - Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

            categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);


    });

    // Product Voltage Filters Values
    $('.product-cat-voltage-meta-data .form-check-input').on('change', function(e){
    console.log('Cambio filtro voltaje categoría', { voltageValue });
        e.preventDefault();

        var voltageValue = $(this).val();


        // Add Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

        toggleMetaDataValues(categoryProductVoltageMetaValueArr, {
            value: voltageValue,
        });

        categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);

    });

    // Product Weight Filter
    $('.product-cat-weight-meta-data .form-check-input').on('change', function(e){

        e.preventDefault();

        weightValue = $(this).val();
        weightLabel = $(this).next('label').text().trim();
        console.log('Cambio filtro peso categoría', { weightValue });

        // Add Remove Checked
        if($(this).prop('checked')){
            $(this).attr('checked',true);
        }else{
            $(this).attr('checked',false);
        }

        toggleMetaDataValues(categoryProductWeightMetaValueArr,{
            value: weightValue,
            label: weightLabel,
        });

        categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);


    });

    // Ajax Call
    categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID);

    function categoryPageAjaxCall(taxonomy_posts_per_page,paged,categoryPageID,categoryProductSpeedMetaValuesArr,categoryProductTorqueMetaValueArr,categoryProductVoltageMetaValueArr,categoryProductWeightMetaValueArr,categoryProductSearchValueTerm,categoryChildTermID){
    console.log('categoryPageAjaxCall called', { taxonomy_posts_per_page, paged, categoryPageID, categoryProductSpeedMetaValuesArr, categoryProductTorqueMetaValueArr, categoryProductVoltageMetaValueArr, categoryProductWeightMetaValueArr, categoryProductSearchValueTerm, categoryChildTermID });

        if(($('.category-page-child-category-slider .product-link-card').length > 0) || ($('.filter-right-block .category-product-search-block input').length > 0) || ($('.category-page .custom-pagination a').length > 0) || ($('.product-cat-speed-meta-data .form-check-input').length > 0) || ($('.product-cat-torque-meta-data .form-check-input').length > 0) || ($('.product-cat-voltage-meta-data .form-check-input').length > 0) || ($('.product-cat-weight-meta-data .form-check-input').length > 0) ){
            $('.loader-overlay, .loader').show();
        }

        $.ajax({
            "url"    : ajaxUrl,
            "method" : "post",
            "data"   : {
                term_id:categoryPageID,
                posts_per_page:taxonomy_posts_per_page,
                paged:paged,
                catSpeedMetavalue:categoryProductSpeedMetaValuesArr,
                catTorqueMetaValue:categoryProductTorqueMetaValueArr,
                catVoltageMetaValue:categoryProductVoltageMetaValueArr,
                catWeightMetaValue:categoryProductWeightMetaValueArr,
                catSearchValue:categoryProductSearchValueTerm,
                childCategoryTerm:categoryChildTermID,
                action : 'category_page_ajax_handler',
            },
            "success" : function(response){

                $('.loader-overlay, .loader').hide();

                var jsonResponse = JSON.parse(response);
                var productData = jsonResponse.product_data;
                var productPaginationData = jsonResponse.pagination_data;

                console.log('categoryPageAjaxCall response', { productData, productPaginationData });

                if(productData){
                    $(categoryProductListingCotnainer).html(productData);
                    $(categoryPagePaginationContainer).html(productPaginationData);
                }else{
                    $(categoryProductListingCotnainer).html('<div class="not-found-products text-center"><p> There are not any product found for this match!</p></div>');
                    $(categoryPagePaginationContainer).html('');
                }

                
                initProductCategorySwiperSlider();
            },
        });
    }


    /* Market Archive Page Filter */
    var marketTopLevelTermId;
    var secondLevelMarketTermId;
    var secondLevelMarketsDataContainer = $('.market-second-level-child-terms-block');
    var thirdLevelMarketDataContainer = $('.market-third-terms-block');
    $(window).on('load',function(){
        $('.market-top-level-filter-block .ds-market-filter-link.show').each(function(){
            marketTopLevelTermId = $(this).data('term-id');
            marketTermsAndProductFilerAjax(marketTopLevelTermId);
        });

        setTimeout(function () {
            $('.second-level-market-archive-card a.active-level-2-card').each(function () {
                secondLevelMarketTermId = $(this).data('term-id');
                marketSecondLevelTermsAndProductFilerAjax(secondLevelMarketTermId);
            });
        }, 500);

    });
    $(document).on('click', '.market-top-level-filter-block .ds-market-filter-link', function () {
        $('.market-top-level-filter-block .ds-market-filter-link').removeClass('show');
        $(this).addClass('show');

        // Use local variable
        marketTopLevelTermId = $(this).data('term-id');


        // Call your function with only top-level term
        marketTermsAndProductFilerAjax(marketTopLevelTermId); // or handle null accordingly
    });
    function marketTermsAndProductFilerAjax(marketTopLevelTermId){
        console.log('marketTermsAndProductFilerAjax called', { marketTopLevelTermId });
        if($('.market-top-level-filter-block').length > 0){
            $('.loader-overlay, .loader').show();
        }
        $.ajax({
            "url":ajaxUrl,
            "method":"post",
            "data":{
                topLevelTermID:marketTopLevelTermId,
                action:'market_archive_filter_ajax_handler',
            },
            "success":function(response){

                $('.loader-overlay, .loader').hide();

                jsonResponse = JSON.parse(response);
                
                var secondLevelMarketData = jsonResponse.second_level_term_data;
                var childTermscounter =  jsonResponse.child_terms_counter;
                var secondLevelWithDirectPostsData = jsonResponse.second_level_different_layout;

                console.log('marketTermsAndProductFilerAjax response', { secondLevelMarketData, childTermscounter, secondLevelWithDirectPostsData });

                if(childTermscounter != 0){
                    if(secondLevelMarketData != ''){
                        $('.ds-market-filter-cards').removeClass('hide-second-level-market-terms');
                        $(secondLevelMarketsDataContainer).html(secondLevelMarketData);

                        thirdLevelMarketDataContainer.html('');
                        secondLevelMarketTermId = $('.second-level-market-archive-card a.active-level-2-card').data('term-id');
                        if(secondLevelMarketTermId){
                            marketSecondLevelTermsAndProductFilerAjax(secondLevelMarketTermId);
                        }
                    }
                }else{
                    $('.ds-market-filter-cards').addClass('hide-second-level-market-terms');
                    thirdLevelMarketDataContainer.html(secondLevelWithDirectPostsData);
                }


            },
        });
    }

    /* Market Third Level Terms */
    $(document).on('click', '.second-level-market-archive-card a', function (e) {
        e.preventDefault();

        secondLevelMarketTermId = $(this).data('term-id');
        // marketTopLevelTermId = $('.market-top-level-filter-block .ds-market-filter-link.show').data('term-id');

        // Add Remove Active Class
        $('.second-level-market-archive-card a').removeClass('active-level-2-card');
        $(this).addClass('active-level-2-card');

        marketSecondLevelTermsAndProductFilerAjax(secondLevelMarketTermId);
    });
    function marketSecondLevelTermsAndProductFilerAjax(secondLevelMarketTermId){
        console.log('marketSecondLevelTermsAndProductFilerAjax called', { secondLevelMarketTermId });
        if($('.second-level-market-archive-card a').length > 0){
            $('.loader-overlay, .loader').show();
        }
        $.ajax({

            "url":ajaxUrl,
            "method":"post",
            "data":{
                secondLevelTermID:secondLevelMarketTermId,
                action:'market_archive_filter_ajax_handler',
            },
            "success":function(response){

                $('.loader-overlay, .loader').hide();

                jsonResponse = JSON.parse(response);
                var thirdLevelMarketData = jsonResponse.third_level_term_data; 

                console.log('marketSecondLevelTermsAndProductFilerAjax response', { thirdLevelMarketData });

                if(thirdLevelMarketData){
                    $(thirdLevelMarketDataContainer).html(thirdLevelMarketData);
                }else{
                    $(thirdLevelMarketDataContainer).html('<div class="not-found-products text-center"><p> There are not any product found for this match!</p></div>');
                }

            },
        });
    }
});
