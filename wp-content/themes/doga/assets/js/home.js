(function ($) {

    'use strict';

    // Home



    $(document).ready(function () {



        var $rings = $('.rings');



        if ($rings.length > 0) {



            let $ringsItems = $rings.find('.rings-item');

            let ringsItemsLength = $ringsItems.length;



            gsap.set($ringsItems, {

                y: '-50%',

                x: '-50%',

                scale: 0,

            });



            $ringsItems.each((i, el) => {

                let $el = $(el);

                let widthPercent = Math.max(0, Math.min(100, (i + 1) * (100 / ringsItemsLength)));



                gsap.to($el, {

                    scale: widthPercent / 100,

                    duration: 1.7,

                    ease: "elastic.out(1,1)",

                    delay: i * 0.1,

                    scrollTrigger: {

                        trigger: $rings,

                        start: 'top 80%',

                        scrub: false,

                    }

                });

            });





            // Fondo

            gsap.to('.all-green-bg', {

                '--bg-top': '50%',

                scrollTrigger: {

                    trigger: '.all-green-bg',

                    start: 'top bottom',

                    end: 'bottom top',

                    scrub: 1,

                },

            });

        }





        $('.title-effect').each((i, el) => {

            let $el = $(el);



            let $text = new SplitText($el, {

                type: 'lines,words, chars',

                linesClass: 'title-effect-lines',

                wordsClass: 'title-effect-words',

                charsClass: 'title-effect-chars'

            });



            gsap.set($text.chars, {

                // y: '100%',

                opacity: 0,

            });



            gsap.to($text.chars, {

                // y: '0%',

                opacity: 1,

                duration: .4,

                ease: 'power4.out',

                stagger: 0.05,

                // delay: i * 0.2,

                scrollTrigger: {

                    trigger: $el,

                    start: 'top 80%',

                    scrub: false,

                }

            });

        });





        $('.text-effect').each((i, el) => {

            let $el = $(el);



            gsap.set($el, {

                opacity: 0,

            });



            gsap.to($el, {

                opacity: 1,

                duration: .8,

                delay: .4,

                ease: 'power4.out',

                scrollTrigger: {

                    trigger: $el,

                    start: 'top 80%',

                    scrub: false,

                }

            });

        });





        var $secondrow = $('.secondrow-home');

        var $video = $secondrow.find('.elementor-background-video-container video');



        if ($video.length > 0) {

            $video.get(0).pause();



            gsap.to($video, {

                opacity: 1,

                ease: 'none',

                scrollTrigger: {

                    trigger: $secondrow,

                    start: 'top center',

                    end: 'bottom top',

                    scrub: false,

                    onEnter: () => {

                        $video.get(0).currentTime = 0;

                        $video.get(0).play();

                    },

                }

            });

        }











    });



})(jQuery);

