import AOS from "aos";

export default defineNuxtPlugin(() => {
  requestAnimationFrame(() => {
    AOS.init({
      duration: 900,
      easing: "ease-out-cubic",
      once: true,
      offset: 60,
      delay: 0,
      startEvent: "DOMContentLoaded",
      initClassName: false,
      useClassNames: false,
      disableMutationObserver: false,
    });
  });
});
