(function () {
  var sliderTrack = document.getElementById("jcp-plugin-slider-track");
  var sliderViewport = sliderTrack ? sliderTrack.parentElement : null;
  var sliderPrev = document.getElementById("jcp-plugin-slider-prev");
  var sliderNext = document.getElementById("jcp-plugin-slider-next");
  var wrapper = document.querySelector(".jcp-combined-components");
  var sliderContainer = document.querySelector(".jcp-plugin-slider");
  var currentIndex = 0;
  var totalCards = 0;
  var isFirstRun = true;

  /**
   * Read the actual gap from the slider track's computed style.
   */
  function getTrackGap() {
    if (!sliderTrack) return 24;
    var gap = parseFloat(window.getComputedStyle(sliderTrack).gap);
    return isNaN(gap) ? 24 : gap;
  }

  /**
   * Read the actual horizontal padding from the slider container.
   */
  function getSliderPadding() {
    if (!sliderContainer) return window.innerWidth >= 768 ? 112 : 80;
    var styles = window.getComputedStyle(sliderContainer);
    return parseFloat(styles.paddingLeft) + parseFloat(styles.paddingRight);
  }

  /**
   * How many cards should be visible at the current width.
   */
  function getVisibleCardCount() {
    var w = getConstrainedWidth();
    if (w < 640) return 1;
    if (w < 1024) return 2;
    return 3;
  }

  /**
   * Measure the parent's real width.
   * First run: wrapper is display:none (from CSS), parent has no inflating content.
   * Subsequent runs: wrapper has a pixel width, use position:absolute to re-measure.
   */
  function getConstrainedWidth() {
    if (!wrapper || !wrapper.parentElement) return window.innerWidth;
    var parent = wrapper.parentElement;

    if (isFirstRun) {
      // Wrapper is display:none — parent is naturally sized
      var prevParentWidth = parent.style.width;
      parent.style.width = "100%";
      var parentWidth = parent.clientWidth;
      parent.style.width = prevParentWidth || "";
      return parentWidth || window.innerWidth;
    }

    // Subsequent runs: take wrapper out of flow to measure
    var prevPosition = wrapper.style.position;
    var prevWidth = wrapper.style.width;
    var prevParentWidth = parent.style.width;
    wrapper.style.position = "absolute";
    wrapper.style.width = "0px";
    parent.style.width = "100%";
    var parentWidth = parent.clientWidth;
    parent.style.width = prevParentWidth || "";
    wrapper.style.position = prevPosition || "";
    wrapper.style.width = prevWidth || "";
    return parentWidth || window.innerWidth;
  }

  /**
   * Calculate card widths from the constrained parent width and set as CSS variable.
   */
  function updateCardSizing() {
    if (!sliderViewport || !wrapper) return;
    var containerWidth = getConstrainedWidth();
    wrapper.style.width = containerWidth + "px";
    // Reveal wrapper on first run
    if (isFirstRun) {
      wrapper.style.display = "block";
      isFirstRun = false;
    }
    var padding = getSliderPadding();
    var available = containerWidth - padding;
    var gap = getTrackGap();
    var cardsVisible = getVisibleCardCount();
    var cardWidth = (available - (cardsVisible - 1) * gap) / cardsVisible;
    cardWidth = Math.max(cardWidth, 100);
    sliderViewport.style.setProperty("--jcp-card-width", cardWidth + "px");
  }

  /**
   * Get the pixel step for sliding one card (card width + gap).
   */
  function getCardStepSize() {
    if (!sliderTrack) return 324;
    var firstCard = sliderTrack.querySelector(".jcp-plugin-card");
    if (!firstCard) return 324;
    return firstCard.offsetWidth + getTrackGap();
  }

  /**
   * Recalculate card sizing, card count, clamp index, and update position.
   */
  function recalculateSlider() {
    if (!sliderTrack) return;
    updateCardSizing();
    totalCards = sliderTrack.querySelectorAll(".jcp-plugin-card").length;
    var cardsVisible = getVisibleCardCount();
    currentIndex = Math.max(
      0,
      Math.min(currentIndex, Math.max(0, totalCards - cardsVisible)),
    );
    updateSliderPosition();
    updateSliderButtons(cardsVisible);
  }

  function updateSliderPosition() {
    if (!sliderTrack) return;
    var step = getCardStepSize();
    sliderTrack.style.transform = "translateX(-" + currentIndex * step + "px)";
  }

  function updateSliderButtons(cardsVisible) {
    var visible = cardsVisible || getVisibleCardCount();
    if (sliderPrev) sliderPrev.disabled = currentIndex <= 0;
    if (sliderNext)
      sliderNext.disabled =
        totalCards <= visible || currentIndex >= totalCards - visible;
  }

  function slidePrev() {
    if (currentIndex <= 0) return;
    currentIndex -= 1;
    updateSliderPosition();
    updateSliderButtons();
  }

  function slideNext() {
    var visible = getVisibleCardCount();
    if (totalCards <= visible || currentIndex >= totalCards - visible) return;
    currentIndex += 1;
    updateSliderPosition();
    updateSliderButtons(visible);
    if (window.jcpSliderAutoFetch && currentIndex >= totalCards - visible * 2) {
      window.jcpSliderAutoFetch();
    }
  }

  window.jcpSliderRefresh = function () {
    recalculateSlider();
    initCardCarousels();
    initDescriptionToggles();
  };

  function initCardCarousels() {
    document
      .querySelectorAll(".jcp-plugin-card__gallery[data-carousel]")
      .forEach(function (gallery) {
        if (gallery.dataset.carouselBound) return;
        gallery.dataset.carouselBound = "1";
        var slides = gallery.querySelectorAll(".jcp-plugin-card__slide");
        var prevBtn = gallery.querySelector(".jcp-plugin-card__nav--prev");
        var nextBtn = gallery.querySelector(".jcp-plugin-card__nav--next");
        var dots = gallery.querySelectorAll(".jcp-plugin-card__dot");
        var total = slides.length;
        var active = 0;
        function setActive(i) {
          if (i < 0) i = total - 1;
          if (i >= total) i = 0;
          active = i;
          slides.forEach(function (slide, idx) {
            if (idx === active) {
              slide.classList.add("jcp:opacity-100", "jcp:visible");
              slide.classList.remove("jcp:opacity-0", "jcp:invisible");
            } else {
              slide.classList.add("jcp:opacity-0", "jcp:invisible");
              slide.classList.remove("jcp:opacity-100", "jcp:visible");
            }
          });
          dots.forEach(function (dot, idx) {
            if (idx === active) {
              dot.classList.add("jcp:bg-white", "jcp:scale-[1.2]");
              dot.classList.remove("jcp:bg-white/60");
            } else {
              dot.classList.add("jcp:bg-white/60");
              dot.classList.remove("jcp:bg-white", "jcp:scale-[1.2]");
            }
          });
        }
        if (prevBtn)
          prevBtn.addEventListener("click", function () {
            setActive(active - 1);
          });
        if (nextBtn)
          nextBtn.addEventListener("click", function () {
            setActive(active + 1);
          });
        dots.forEach(function (dot, idx) {
          dot.addEventListener("click", function () {
            setActive(idx);
          });
        });
      });
  }

  function initDescriptionToggles() {
    document.querySelectorAll(".jcp-plugin-card").forEach(function (card) {
      var text = card.querySelector("[data-desc-text]");
      var toggle = card.querySelector("[data-desc-toggle]");
      if (!text || !toggle) return;
      if (!toggle.dataset.bound) {
        toggle.addEventListener("click", function () {
          var expanded = text.classList.contains("jcp:line-clamp-none");
          if (expanded) {
            text.classList.remove("jcp:line-clamp-none", "jcp:overflow-visible");
            text.classList.add("jcp:line-clamp-4", "jcp:overflow-hidden");
            toggle.setAttribute("aria-expanded", "false");
            toggle.textContent = "Read more";
          } else {
            text.classList.add("jcp:line-clamp-none", "jcp:overflow-visible");
            text.classList.remove("jcp:line-clamp-4", "jcp:overflow-hidden");
            toggle.setAttribute("aria-expanded", "true");
            toggle.textContent = "Show less";
          }
        });
        toggle.dataset.bound = "1";
      }
      if (text.classList.contains("jcp:line-clamp-none")) {
        toggle.hidden = false;
        return;
      }
      requestAnimationFrame(function () {
        toggle.hidden = text.scrollHeight <= text.clientHeight + 2;
      });
    });
  }

  if (sliderPrev) sliderPrev.addEventListener("click", slidePrev);
  if (sliderNext) sliderNext.addEventListener("click", slideNext);

  function init() {
    recalculateSlider();
    initCardCarousels();
    initDescriptionToggles();
  }

  // Debounced window resize — no ResizeObserver to avoid infinite loops
  var resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(recalculateSlider, 100);
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
