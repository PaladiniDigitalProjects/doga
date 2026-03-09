jQuery(document).ready(function ($) {

    console.log('We are on LOCAL DEVELOPMENT');
    // Soluciones Animation Start
    function switchActiveBlock($newActive) {

        if ($(window).outerWidth() <= 767) return; // Skip animation logic for small screens

        var $container = $newActive.closest('.solution-main-block');
        var $currentActive = $container.find('.solutions-details-sers-block.active');

        if (!$newActive.is($currentActive)) {

            var $currentContent = $currentActive.find('.detailed-content');
            var $newContent = $newActive.find('.detailed-content');

            // Collapse current

            var currentHeight = $currentContent.outerHeight();
            $currentContent.css('height', currentHeight + 'px');
            $currentActive.addClass('animating');

            // setTimeout(() => {
                $currentContent.css('height', '0px');
            // }, 10);


            // setTimeout(() => {

                $currentActive.removeClass('active animating');
                $currentContent.css('height', '');

            // }, 500);



            // Expand new

            $newActive.addClass('active');
            var newHeight = $newContent.prop('scrollHeight');
            $newContent.css('height', '0px');
            $newActive.addClass('animating');
            // setTimeout(() => {
                $newContent.css('height', newHeight + 'px');
            // }, 10);

            // setTimeout(() => {

                $newContent.css('height', '');
                $newActive.removeClass('animating');

            // }, 500);

        }

    }

    function initializeSolutionBlocks() {

        if ($(window).outerWidth() > 767) {

            $('.solution-main-block').each(function () {

                $(this).find('.solutions-details-sers-block').removeClass('active').first().addClass('active');

            });



            $(document).on('mouseenter', '.solution-main-block .solutions-details-sers-block', function () {

                switchActiveBlock($(this));

            });

        } else {

            $('.solution-main-block .solutions-details-sers-block').addClass('active');

        }

    }

    initializeSolutionBlocks();


    // Optional: re-run on window resize
    $(window).on('resize', function () {

        initializeSolutionBlocks();

    });
    // Soluciones Animation End

});

