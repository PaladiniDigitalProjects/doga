// // jQuery(document).ready(function ($) {
// // });
// gsap.registerPlugin(ScrollTrigger);

// function initAnimation() {
//     if (window.innerWidth > 880) {

//         // ScrollTrigger.getAll().forEach(trigger => trigger.kill());
//         ScrollTrigger.refresh();

//         let cards = gsap.utils.toArray(".card");

//         let stickDistance = 0;

//         let firstCardST = ScrollTrigger.create({
//             trigger: cards[0],
//             start: "center top+=200"
//         });

//         let lastCardST = ScrollTrigger.create({
//             trigger: cards[cards.length - 1],
//             start: "center top+=200"
//         });

//         cards.forEach((card, index) => {

//             var scale = 1 - (cards.length - index) * 0.040;
//             let scaleDown = gsap.to(card, { scale: scale, 'transform-origin': '"50% ' + (lastCardST.start + stickDistance) + '"' });

//             ScrollTrigger.create({
//                 trigger: card,
//                 start: "center top+=200",
//                 end: () => lastCardST.start + stickDistance,
//                 pin: true,
//                 markers: false,
//                 pinSpacing: false,
//                 ease: "none",
//                 animation: scaleDown,
//                 toggleActions: "restart none none reverse"
//             });
//         });

//         ScrollTrigger.refresh();
//     } else {
//         ScrollTrigger.getAll().forEach(trigger => trigger.kill());
//     }
// }

// initAnimation();

// window.addEventListener("resize", () => {
//     setTimeout(() => {
//         initAnimation();
//     }, 500);
// });

// window.addEventListener("load", () => {
//     setTimeout(() => {
//         initAnimation();
//     }, 500);
// });