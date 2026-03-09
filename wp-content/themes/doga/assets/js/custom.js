jQuery(document).ready(function($){

    var lastScrollTop = 0;
    var $header = $('.main-header-block'); // Replace with your header's class if different

    $(window).on('scroll', function () {
        var st = $(this).scrollTop();
        var $menuBlock = $('.drive-system-menu-block');

        if (st > lastScrollTop) {
            // Scrolling Down
            $header.removeClass('scrolling-up').addClass('scrolling-down');
            $menuBlock.removeClass('sticky-hide').addClass('sticky-show');
        } else {
            // Scrolling Up
            if (st > 100) {
                $header.removeClass('scrolling-down').addClass('scrolling-up');
                $menuBlock.removeClass('sticky-show').addClass('sticky-hide');
            } else {
                $header.removeClass('scrolling-up scrolling-down');
                $menuBlock.removeClass('sticky-hide sticky-show');
            }
        }
        lastScrollTop = st;
    });

    // $(window).scroll(function() {
    //     var scroll = $(window).scrollTop();
    //     var $menuBlock = $('.drive-system-menu-block');

    //     if (scroll >= 100) {
    //         $menuBlock.addClass('menu-sticky');
    //     } else {
    //         $menuBlock.removeClass('menu-sticky');
    //     }
    // });

// jQuery(document).ready(function($){
//   $('a[href^="#"]').on('click', function(e) {
//     e.preventDefault();
//     var target = $($(this).attr('href'));
//     if (target.length) {
//       e.preventDefault();
//       $('html, body').animate({
//         scrollTop: target.offset().top - 170 // ajusta el -100 si tienes header fijo
//       }, 600); // duración en ms
//     }
//   });
// });


});