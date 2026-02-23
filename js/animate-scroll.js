/**
 * Custom JS / CSS animation on scroll library.
 * Ued to replace Animate On Scroll to support layout shift, respect reduced motion and supporting delays in initialisation.
 * This was developed as AOS was causing issues with accordions and other dynamic height elements.
 * This uses more a modern approach to check if an element is in the viewport, bu ysing the Intersection Observer API.
 */
class EzpzAnimateOnScroll {
  constructor(options = {}) {
    this.options = {
      rootMargin: options.rootMargin || "0px 0px -80px 0px", // Distance from bottom of viewport before triggering (in pixels)
      once: options.once !== undefined ? options.once : true, // Only animate once by default
      selector: options.selector || "[data-animate]", // Selector for elements to animate
      animateClass: options.animateClass || "animate-in", // Class to add when animated
      initialClass: options.initialClass || "animate-init", // Class for initial state
      respectReducedMotion:
        options.respectReducedMotion !== undefined
          ? options.respectReducedMotion
          : true, // Respect prefers-reduced-motion
    };
    this.reducedMotion = this.checkReducedMotion(); // Whether the user has reduced motion enabled
    this.observer = null; // The IntersectionObserver instance which is created on init
    this.elements = new Set(); // Stores which elements are being observed
    this.initialised = false; // Whether the library has been initialised
  }

  /**
   * Initialise the class
   */
  init() {
    if (this.initialised) {
      // console.warn("ScrollAnimate already initialised");
      return;
    }

    if (this.reducedMotion) {
      this.handleReducedMotion();
      return;
    }

    this.handleFragments();

    console.log("initialised EzpzAnimateOnScroll with options:", this.options);

    // Create the intersection observer
    this.observer = new IntersectionObserver(
      (entries) => this.handleIntersection(entries),
      {
        rootMargin: this.options.rootMargin,
        threshold: 0,
      },
    );

    this.refresh();
    this.initialised = true;

    // Listen for reduced motion changes
    const mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
    mediaQuery.addEventListener("change", (e) => {
      this.reducedMotion = e.matches;
      if (this.reducedMotion) {
        this.handleReducedMotion();
      }
    });
  }

  /**
   * Check if user prefers reduced motion
   */
  checkReducedMotion() {
    if (!this.options.respectReducedMotion) return false;
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  /**
   * Ensures that all elements are observed
   */
  refresh() {
    if (this.reducedMotion) return;

    console.log("Selecting elements with selector:", this.options.selector);
    const elements = document.querySelectorAll(this.options.selector);

    if (elements.length === 0) {
      console.log("No elements found to animate on scroll.");
      return;
    }

    elements.forEach((el) => {
      if (this.elements.has(el)) return; // Skip, it's already being observed

      el.classList.add(this.options.initialClass);

      this.observer.observe(el);
      this.elements.add(el);
    });

    // console.log(
    //   "EzpzAnimateOnScroll refreshing, observing elements:",
    //   elements
    // );
  }

  /**
   * Handle when a user has reduced motion enabled
   */
  handleReducedMotion() {
    // console.log("Reduced motion enabled, skipping animations");
    // Remove observer
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }

    // Add animate class to all elements
    this.elements.forEach((el) => {
      el.classList.add(this.options.animateClass);
      el.classList.remove(this.options.initialClass);
    });

    this.elements.clear();
    this.initialised = false;
  }

  handleFragments() {
    // Gets all elements with data-fragment and fragments their immediate children
    let fragmentContainers = document.querySelectorAll("[data-fragment]");
    if (fragmentContainers.length > 0) {
      fragmentContainers.forEach((container) => {
        let animationStyle = container.getAttribute("data-fragment") || "";
        let animationDelay =
          container.getAttribute("data-fragment-delay") || "false";

        this.fragmentItems(container, animationStyle, animationDelay);

        container.removeAttribute("data-fragment");
        container.removeAttribute("data-fragment-delay");
      });
    }
  }

  /**
   * Initialise the observer on all elements
   * @param {IntersectionObserverEntry[]} entries - Entries from the observer
   *
   */
  handleIntersection(entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const element = entry.target;
        this.animateElement(element);
      } else {
        const element = entry.target;
        // Element left viewport - remove animation class if once is false
        if (!this.options.once) {
          element.classList.remove(this.options.animateClass);
        }
      }
    });
  }

  /**
   * Animate an element
   */
  animateElement(element) {
    element.classList.add(this.options.animateClass);

    // If once is true, stop observing
    if (this.options.once) {
      this.observer.unobserve(element);
      this.elements.delete(element);
    }
  }

  /**
   * Destroy the instance
   */
  destroy() {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }

    // Remove all classes
    if (this.elements.size > 0) {
      this.elements.forEach((el) => {
        el.classList.remove(this.options.initialClass);
        el.classList.remove(this.options.animateClass);
      });

      this.elements.clear();
    }
    this.initialised = false;
  }

  /**
   * Reset all animations
   */
  reset() {
    this.elements.forEach((el) => {
      el.classList.remove(this.options.animateClass);
    });

    // Re-observe all elements
    if (this.observer && !this.reducedMotion) {
      this.elements.forEach((el) => {
        this.observer.observe(el);
      });
    }
  }

  addDelay(element, delayMs = 0) {
    element.style.setProperty("--animation-delay", `${delayMs}ms`);
  }

  initialiseElement(selector, animationStyle = "fade-up", delayMs = 0) {
    let foundElements = document.querySelectorAll(selector);
    // console.log("animating for " + selector);
    if (foundElements.length > 0) {
      foundElements.forEach((el) => {
        el.setAttribute("data-animate", animationStyle);
        if (delayMs > 0) {
          this.addDelay(el, delayMs);
        }
      });
    }
  }

  fragmentElement(selector, animationStyle = "fade-up", withDelay = false) {
    let foundElements = document.querySelectorAll(selector);
    // console.log("fragmenting for " + selector);
    if (foundElements.length > 0) {
      foundElements.forEach((el) => {
        this.fragmentItems(el, animationStyle, withDelay);
      });
    }
  }

  /**
   * Helper methods for this particular project
   */

  /**
   * Iterate through all immediate children of a container and add animation attributes.
   * This will 'fragment' children so that each child has it's own animation. Eg h2, p, p, ul>li etc.
   * This includes handling lists (ul, ol, menu) to add to each li instead.
   */
  fragmentItems(container, animationStyle = "", withDelay = false) {
    let immediateChildren = container.children;
    if (immediateChildren.length > 0) {
      Array.from(immediateChildren).forEach((child, index) => {
        // console.log(child);

        // Skip if it's a div.row
        if (child.tagName.toLowerCase() === "div") {
          if (child.classList.contains("row")) {
            return; // continue to next immediate child
          }
        }

        // If its a ul, li, or ol, add  to each li instead
        if (
          child.tagName.toLowerCase() === "ul" ||
          child.tagName.toLowerCase() === "ol" ||
          child.tagName.toLowerCase() === "menu"
        ) {
          let listItems = child.querySelectorAll("li");
          listItems.forEach((li, liIndex) => {
            li.setAttribute("data-animate", animationStyle);
            if (withDelay) {
              let delay = liIndex * 100;
              li.style.setProperty("--animation-delay", `${delay}ms`);
            }
          });
          return; // continue to next immediate child
        }

        child.setAttribute("data-animate", animationStyle);
        if (withDelay) {
          let delay = index * 100;
          child.style.setProperty("--animation-delay", `${delay}ms`);
        }
      });
    }
  }

  /**
   * Initialise Block Text Columns
   */
  initialiseBlockColumns() {
    let blockColumns = document.querySelectorAll(".block-columns");
    if (blockColumns.length > 0) {
      blockColumns.forEach((block) => {
        let columns = block.querySelectorAll(
          ".wp-block-ezpz-column .inner-content",
        );
        if (columns.length > 0) {
          let i = 0;
          columns.forEach((column) => {
            column.setAttribute("data-animate", "fade-up");
            this.addDelay(column, i * 75);
            i++;
          });
        }
      });
    }
  }

  /**
   * Initialise text media blocks
   */
  initialiseTextMediaBlocks() {
    let boxes = document.querySelectorAll(".block-text-media");
    if (boxes.length > 0) {
      boxes.forEach((box) => {
        // Find any .type-text inside and fragment with delay
        let textContent = box.querySelector(
          ".wp-block-ezpz-content-restricted",
        );

        if (textContent) {
          textContent.setAttribute("data-fragment", "fade-up");
          textContent.setAttribute("data-fragment-delay", "true");
        }

        let blockImage = box.querySelector(".wp-block-image");
        if (blockImage) {
          blockImage.setAttribute("data-animate", "fade-up");
          blockImage.style.setProperty("--animation-delay", `100ms`);
        }
      });
    }

    let textMediaBoxes = document.querySelectorAll(".text-media-box");
    if (textMediaBoxes.length > 0) {
      textMediaBoxes.forEach((box) => {
        // console.log(box);
        box.setAttribute("data-animate", "fade-up");
      });
    }
  }

  initialiseHomeHero() {
    let figures = document.querySelectorAll(".home .hero figure");
    figures.forEach((figure) => {
      figure.setAttribute("data-animate", "fade-up-hero");

      figure.style.setProperty("--animation-delay", `1000ms`);
    });
  }

  initialiseHeroTitle() {
    let figures = document.querySelectorAll(".hero h1");
    figures.forEach((figure) => {
      figure.setAttribute("data-animate", "fade-up");

      figure.style.setProperty("--animation-delay", `200ms`);
    });
  }

  initialiseTimeline() {
    let timeLines = document.querySelectorAll(".block-timeline");

    timeLines.forEach((timeLine) => {
      let timeContainers = timeLine.querySelectorAll(".time-container");

      timeContainers.forEach((item, index) => {
        item.setAttribute("data-animate", "fade-up");
        this.addDelay(item, index * 75);
      });
    });
  }

  initialiseAccordion() {
    let sections = document.querySelectorAll(".block-section");

    sections.forEach((section) => {
      let accordions = section.querySelectorAll(".accordion");

      console.log("Accordions found in this section:", accordions.length);

      accordions.forEach((accordion, accordionIndex) => {
        let accordionHeadings = accordion.querySelectorAll(
          ".accordion__heading",
        );
        let accordionAnswers = accordion.querySelectorAll(".answer");

        accordionHeadings.forEach((heading, index) => {
          heading.setAttribute("data-animate", "fade-up");
          this.addDelay(
            heading,
            (accordionIndex * accordionHeadings.length + index) * 35,
          );
        });

        accordionAnswers.forEach((answer, index) => {
          answer.setAttribute("data-animate", "fade-up");
          this.addDelay(
            answer,
            (accordionIndex * accordionAnswers.length + index) * 35,
          );
        });
      });
    });
  }

  initialiseStandardText() {
    let sections = document.querySelectorAll(".block-section");

    sections.forEach((section) => {
      let innerContent = section.querySelector(".inner-content");

      if (innerContent) {
        // Skip if it contains elements we don't want to animate
        let hasTimeline = innerContent.querySelector(
          ".block-timeline, .timeline",
        );
        let hasImage = innerContent.querySelector(
          "img, .wp-block-image, figure",
        );
        let hasVideo = innerContent.querySelector(
          "video, iframe, .wp-block-video, .wp-block-embed",
        );

        if (hasTimeline || hasImage || hasVideo) {
          return; // Skip this section
        }

        // Check content
        let headings = innerContent.querySelectorAll("h2, h3");
        let paragraphs = innerContent.querySelectorAll("p");
        let buttons = innerContent.querySelectorAll(
          ".button, .wp-block-button",
        );
        let children = innerContent.children;

        // Calculate expected children count
        let expectedCount =
          headings.length + paragraphs.length + buttons.length;

        // Animate if we have at least 1 heading and 1 paragraph,
        // optionally a button, and nothing else unexpected
        if (
          headings.length >= 1 &&
          paragraphs.length >= 1 &&
          children.length === expectedCount
        ) {
          innerContent.setAttribute("data-animate", "fade-up");
          this.addDelay(innerContent, 0);

          // Also animate the button if present
          if (buttons.length > 0) {
            buttons.forEach((button, index) => {
              button.setAttribute("data-animate", "fade-up");
              this.addDelay(button, (index + 1) * 100);
            });
          }
        }
      }
    });
  }

  /**
   * Add delays to cards
   */
  initialiseCardGrids() {
    let cardGrids = document.querySelectorAll(".card-grid");
    if (cardGrids.length > 0) {
      cardGrids.forEach((grid) => {
        // Get the --grid-cols property to determine number of columns
        let computedStyles = window.getComputedStyle(grid);
        let gridCols = computedStyles.getPropertyValue("--grid-cols").trim();
        let numCols = parseInt(gridCols, 10);

        // Loop through each .card inside the grid, adding delays of 100ms per column, and resetting each row
        let cards = grid.querySelectorAll(".card");
        cards.forEach((card, index) => {
          let colIndex = index % numCols;
          let delayMs = colIndex * 120; // 120ms per column
          animator.addDelay(card, delayMs);
        });

        // console.log("Adding delays to card grid items", grid);
      });
    }
  }
}

// Initialise on DOM ready
const animator = new EzpzAnimateOnScroll();

document.addEventListener("DOMContentLoaded", () => {
  // Note: Deliberately did not include all of these in init, so that it is easier to lift and shift this code to other projects if desidred
  // animator.initialiseCardGrids();
  animator.initialiseBlockColumns();
  animator.initialiseTextMediaBlocks();
  animator.initialiseHomeHero();
  animator.initialiseTimeline();
  animator.initialiseAccordion();
  animator.initialiseStandardText();
  animator.initialiseHeroTitle();

  // animator.fragmentElement(".site-footer .widget");
  // animator.fragmentElement(".wp-block-pullquote blockquote");
  // animator.initialiseElement(".accordion-item");
  // animator.initialiseElement("form.searchandfilter");
  //animator.initialiseElement(".block-title");

  animator.init();
});
