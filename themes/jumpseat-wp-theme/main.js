/**
 * JumpSeat Theme - Main JavaScript
 * Pure Vanilla JS implementation for WordPress
 */

document.addEventListener('DOMContentLoaded', function () {
  // ========================================
  // IntersectionObserver for Scroll Animations
  // ========================================
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };

  const observerCallback = (entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
      }
    });
  };

  const scrollObserver = new IntersectionObserver(observerCallback, observerOptions);

  // Observe all sections
  const sections = document.querySelectorAll('section');
  sections.forEach(section => {
    scrollObserver.observe(section);
  });

  // ========================================
  // Header Scroll Effect
  // ========================================
  const header = document.querySelector('.header');

  const handleHeaderScroll = () => {
    if (window.scrollY > 100) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleHeaderScroll, { passive: true });

  // ========================================
  // Parallax Effects for Hero & Services
  // ========================================
  const topWrapper = document.querySelector('.top-wrapper');
  const servicesSection = document.querySelector('.services');
  const servicesBgText = document.querySelector('.services__bg-text');
  const feelingLostSection = document.querySelector('.feeling-lost');
  const feelingLostBgText = document.querySelector('.feeling-lost__bg-text');
  let ticking = false;

  const handleParallax = () => {
    const scrolled = window.scrollY;

    // Parallax for Hero Background
    if (topWrapper) {
      const horizontalSpeed = 0.12;
      const horizontalX = scrolled * horizontalSpeed;
      topWrapper.style.setProperty('--parallax-x', `-${horizontalX}px`);
    }

    // Parallax for Services Background Text (only when in viewport)
    if (servicesSection && servicesBgText) {
      const rect = servicesSection.getBoundingClientRect();
      const windowHeight = window.innerHeight;

      // Verifica si la sección está visible en la pantalla
      if (rect.top < windowHeight && rect.bottom > 0) {
        // Calcula el progreso del scroll solo dentro de esta sección
        // Cuando rect.top == windowHeight (apenas entra), progress es 0
        const progress = (windowHeight - rect.top) / (windowHeight + rect.height);

        // Mueve el texto de 0 a -15% de su propio ancho (ajusta el multiplicador si es necesario)
        const moveX = progress * -255;

        // Usamos requestAnimationFrame implícito a través de transform para mejor rendimiento
        servicesBgText.style.transform = `translateX(${moveX}%)`;
      }
    }

    // Animación Parallax para Feeling Lost (START HERE)
    if (feelingLostSection && feelingLostBgText) {
      const rect = feelingLostSection.getBoundingClientRect();
      const windowHeight = window.innerHeight;

      // Verifica si la sección está visible
      if (rect.top < windowHeight && rect.bottom > 0) {
        // Calcula el progreso
        const progress = (windowHeight - rect.top) / (windowHeight + rect.height);
        
        // Multiplicador alto (-120) para que se mueva rápido como pediste antes
        const moveX = progress * -120;
        
        feelingLostBgText.style.transform = `translateX(${moveX}%)`;
      }
    }

    ticking = false;
  };

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(handleParallax);
      ticking = true;
    }
  }, { passive: true });

  // ========================================
  // Mobile Menu Toggle
  // ========================================
  const menuBtn = document.querySelector('.header__menu-btn');
  const nav = document.querySelector('.header__nav');

  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function () {
      nav.classList.toggle('is-open');
      menuBtn.classList.toggle('is-active');
    });
  }

  // ========================================
  // Smooth Scroll for Anchor Links
  // ========================================
  const anchorLinks = document.querySelectorAll('a[href^="#"]');

  anchorLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      const href = this.getAttribute('href');

      if (href === '#' || !href.startsWith('#')) return;

      e.preventDefault();

      const target = document.querySelector(href);

      if (target) {
        const headerHeight = header ? header.offsetHeight : 0;
        const targetPosition = target.getBoundingClientRect().top + window.scrollY - headerHeight;

        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });

        // Close mobile menu if open
        if (nav && nav.classList.contains('is-open')) {
          nav.classList.remove('is-open');
          menuBtn.classList.remove('is-active');
        }
      }
    });
  });

  // ========================================
  // Split-Flap Animation for Departure Board
  // ========================================
  const departureBoard = document.querySelector('.departure-board');
  const boardRows = document.querySelectorAll('.departure-board__row');

  const createFlapCells = (container, text, maxLength) => {
    container.innerHTML = '';
    const chars = text.toUpperCase().padEnd(maxLength, ' ').split('');

    chars.forEach(char => {
      const cell = document.createElement('span'); // Changed back to span to match HTML
      cell.className = 'flap-cell';
      if (char === ' ') cell.classList.add('flap-cell--empty');
      if (char === ':') cell.classList.add('flap-cell--separator');
      if (char === ' ') {
        cell.innerHTML = '&nbsp;'; // Use non-breaking space for empty cells
      } else {
        cell.textContent = char;
      }
      container.appendChild(cell);
    });
  };

  const scrambleCell = (cell, finalChar, delay = 0) => {
    if (finalChar === ':' || finalChar === ' ') return;

    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const duration = 1500;
    const startTime = performance.now();

    setTimeout(() => {
      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        if (progress < 1) {
          cell.textContent = chars[Math.floor(Math.random() * chars.length)];
          requestAnimationFrame(animate);
        } else {
          cell.textContent = finalChar;
        }
      };
      requestAnimationFrame(animate);
    }, delay);
  };

  if (departureBoard && boardRows.length > 0) {
    // Inicializar celdas
    boardRows.forEach(row => {
      const timeEl = row.querySelector('.departure-board__time');
      const destEl = row.querySelector('.departure-board__destination');
      const gateEl = row.querySelector('.departure-board__gate');

      if (timeEl) createFlapCells(timeEl, timeEl.textContent.trim(), 5);
      if (destEl) createFlapCells(destEl, destEl.textContent.trim(), 15);
      if (gateEl) createFlapCells(gateEl, gateEl.textContent.trim(), 3);
    });

    const boardObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          boardRows.forEach((row, rowIndex) => {
            const cells = row.querySelectorAll('.flap-cell');
            cells.forEach((cell, cellIndex) => {
              const finalChar = cell.textContent;
              scrambleCell(cell, finalChar, (rowIndex * 200) + (cellIndex * 50));
            });
          });
          boardObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    boardObserver.observe(departureBoard);
  }

  // ========================================
  // Contact Form Handling (Basic)
  // ========================================
  const contactForm = document.getElementById('contactForm');

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      // In a real WP theme, this would usually be handled by a plugin or AJAX
      // For now, we keep the UI feedback logic
      e.preventDefault();

      const submitBtn = this.querySelector('.contact__submit');
      if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'SENDING...';
        submitBtn.disabled = true;

        setTimeout(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
          this.reset();
          alert('Message sent successfully! (Simulation)');
        }, 1500);
      }
    });
  }

  // ========================================
  // Case Studies Slider
  // ========================================
  const slider = document.querySelector('.case-studies__slider');
  const track = document.querySelector('.case-studies__track');
  const slides = document.querySelectorAll('.case-studies__slide');
  const nextBtn = document.querySelector('.case-studies__arrow--next');
  const prevBtn = document.querySelector('.case-studies__arrow--prev');
  const dotsContainer = document.querySelector('.case-studies__dots');

  if (slider && track && slides.length > 0) {
    let currentIndex = 0;
    const slideCount = slides.length;

    // Create Dots
    slides.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.classList.add('case-studies__dot');
      if (index === 0) dot.classList.add('is-active');
      dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
      dot.addEventListener('click', () => goToSlide(index));
      dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.case-studies__dot');

    const updateSlider = () => {
      const slideWidth = slider.getBoundingClientRect().width;
      track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;

      // Update arrows visibility
      if (prevBtn) {
        prevBtn.style.opacity = currentIndex === 0 ? '0' : '1';
        prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
      }
      if (nextBtn) {
        // Asumiendo que slideCount es la cantidad total de slides
        nextBtn.style.opacity = currentIndex === slideCount - 1 ? '0' : '1';
        nextBtn.style.pointerEvents = currentIndex === slideCount - 1 ? 'none' : 'auto';
      }

      // Update dots
      dots.forEach((dot, index) => {
        dot.classList.toggle('is-active', index === currentIndex);
      });

      // Update accessibility
      slides.forEach((slide, index) => {
        slide.setAttribute('aria-hidden', index !== currentIndex);
      });
    };

    const goToSlide = (index) => {
      currentIndex = index;
      updateSlider();
    };

    const nextSlide = () => {
      currentIndex = (currentIndex + 1) % slideCount;
      updateSlider();
    };

    const prevSlide = () => {
      currentIndex = (currentIndex - 1 + slideCount) % slideCount;
      updateSlider();
    };

    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);

    // Initial state
    updateSlider();

    // Optional: Auto-play
    // let interval = setInterval(nextSlide, 5000);
    // slider.addEventListener('mouseenter', () => clearInterval(interval));
    // slider.addEventListener('mouseleave', () => interval = setInterval(nextSlide, 5000));

    // Swipe support (basic)
    let touchStartX = 0;
    slider.addEventListener('touchstart', e => touchStartX = e.touches[0].clientX, { passive: true });
    slider.addEventListener('touchend', e => {
      const touchEndX = e.changedTouches[0].clientX;
      const diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) nextSlide();
        else prevSlide();
      }
    }, { passive: true });
  }
});
