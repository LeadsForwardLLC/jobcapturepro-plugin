import Swiper from 'swiper';
import { Navigation, Autoplay } from 'swiper/modules';

(function () {
  var sliderEl = document.querySelector('.jcp-plugin-slider .swiper');
  if (!sliderEl) return;

  var swiper = new Swiper(sliderEl, {
    modules: [Navigation, Autoplay],
    slidesPerView: 1,
    spaceBetween: 24,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
    },
    breakpoints: {
      640: { slidesPerView: 2 },
      1280: { slidesPerView: 3 },
    },
    navigation: {
      nextEl: '#jcp-plugin-slider-next',
      prevEl: '#jcp-plugin-slider-prev',
    },
    on: {
      reachEnd: function () {
        if (typeof window.jcpSliderAutoFetch === 'function') {
          window.jcpSliderAutoFetch();
        }
      },
    },
  });

  // Exposed so pagination can refresh after appending new slides
  window.jcpSliderRefresh = function () {
    swiper.update();
    initCardCarousels();
    initDescriptionToggles();
  };

  function initCardCarousels() {
    document
      .querySelectorAll('.jcp-plugin-card__gallery[data-carousel]')
      .forEach(function (gallery) {
        if (gallery.dataset.carouselBound) return;
        gallery.dataset.carouselBound = '1';
        var slides = gallery.querySelectorAll('.jcp-plugin-card__slide');
        var prevBtn = gallery.querySelector('.jcp-plugin-card__nav--prev');
        var nextBtn = gallery.querySelector('.jcp-plugin-card__nav--next');
        var dots = gallery.querySelectorAll('.jcp-plugin-card__dot');
        var total = slides.length;
        var active = 0;
        function setActive(i) {
          if (i < 0) i = total - 1;
          if (i >= total) i = 0;
          active = i;
          slides.forEach(function (slide, idx) {
            if (idx === active) {
              slide.classList.add('jcp:opacity-100', 'jcp:visible');
              slide.classList.remove('jcp:opacity-0', 'jcp:invisible');
            } else {
              slide.classList.add('jcp:opacity-0', 'jcp:invisible');
              slide.classList.remove('jcp:opacity-100', 'jcp:visible');
            }
          });
          dots.forEach(function (dot, idx) {
            if (idx === active) {
              dot.classList.add('jcp:bg-white', 'jcp:scale-[1.2]');
              dot.classList.remove('jcp:bg-white/60');
            } else {
              dot.classList.add('jcp:bg-white/60');
              dot.classList.remove('jcp:bg-white', 'jcp:scale-[1.2]');
            }
          });
        }
        if (prevBtn)
          prevBtn.addEventListener('click', function () {
            setActive(active - 1);
          });
        if (nextBtn)
          nextBtn.addEventListener('click', function () {
            setActive(active + 1);
          });
        dots.forEach(function (dot, idx) {
          dot.addEventListener('click', function () {
            setActive(idx);
          });
        });
      });
  }

  function initDescriptionToggles() {
    document.querySelectorAll('.jcp-plugin-card').forEach(function (card) {
      var text = card.querySelector('[data-desc-text]');
      var toggle = card.querySelector('[data-desc-toggle]');
      if (!text || !toggle) return;
      if (!toggle.dataset.bound) {
        toggle.addEventListener('click', function () {
          var expanded = text.classList.contains('jcp:line-clamp-none');
          if (expanded) {
            text.classList.remove('jcp:line-clamp-none', 'jcp:overflow-visible');
            text.classList.add('jcp:line-clamp-4', 'jcp:overflow-hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.textContent = 'Read more';
          } else {
            text.classList.add('jcp:line-clamp-none', 'jcp:overflow-visible');
            text.classList.remove('jcp:line-clamp-4', 'jcp:overflow-hidden');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.textContent = 'Show less';
          }
        });
        toggle.dataset.bound = '1';
      }
      if (text.classList.contains('jcp:line-clamp-none')) {
        toggle.hidden = false;
        return;
      }
      requestAnimationFrame(function () {
        toggle.hidden = text.scrollHeight <= text.clientHeight + 2;
      });
    });
  }

  initCardCarousels();
  initDescriptionToggles();
})();
