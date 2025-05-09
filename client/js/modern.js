document.addEventListener('DOMContentLoaded', function () {
  // Gestion du menu mobile
  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });
  }

  // Gestion du slider
  const slides = document.querySelectorAll('.slide');
  const dotsContainer = document.querySelector('.slider-dots');
  const prevBtn = document.querySelector('.prev-slide');
  const nextBtn = document.querySelector('.next-slide');
  let currentSlide = 0;
  const slideInterval = 4000; // 5 secondes
  let slideTimer;

  if (slides.length > 0 && dotsContainer) {
    // Création dynamique des dots
    slides.forEach((_, i) => {
      const dot = document.createElement('span');
      if (i === 0) dot.classList.add('active');
      dotsContainer.appendChild(dot);
      dot.addEventListener('click', () => {
        showSlide(i);
        resetInterval();
      });
    });

    const dots = dotsContainer.querySelectorAll('span');

    function showSlide(index) {
      const offset = -index * 100;
      document.querySelector('.slides').style.transform = `translateX(${offset}%)`;
    
      slides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));
      slides[index].classList.add('active');
      dots[index].classList.add('active');
      currentSlide = index;
    }
    

    

    function nextSlide() {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }

    function prevSlide() {
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      showSlide(currentSlide);
    }

    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });

    function resetInterval() {
      clearInterval(slideTimer);
      slideTimer = setInterval(nextSlide, slideInterval);
    }

    slideTimer = setInterval(nextSlide, slideInterval);
  }

  // Wishlist
  document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const icon = this.querySelector('i');
      icon.classList.toggle('fas');
      icon.classList.toggle('far');
      showNotification('Œuvre ajoutée à votre liste de souhaits !');
    });
  });

  // Panier
  document.querySelectorAll('.cart-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      this.classList.add('added');
      setTimeout(() => this.classList.remove('added'), 1000);

      const cartCount = document.getElementById('cart-count');
      if (cartCount) {
        cartCount.textContent = parseInt(cartCount.textContent) + 1;
      }

      showNotification('Œuvre ajoutée au panier !');
    });
  });

  // Newsletter
  const newsletterForm = document.getElementById('newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const email = this.querySelector('input[type="email"]').value;
      if (!validateEmail(email)) {
        showNotification('Veuillez entrer une adresse email valide.', 'error');
        return;
      }
      showNotification('Merci pour votre inscription à notre newsletter !');
      this.reset();
    });
  }

  // Validation email
  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  // Notification
  function showNotification(message, type = 'success') {
    let notification = document.querySelector('.notification');
    if (!notification) {
      notification = document.createElement('div');
      notification.className = 'notification';
      document.body.appendChild(notification);
    }
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.classList.add('show');
    setTimeout(() => notification.classList.remove('show'), 3000);
  }

  // Styles pour les notifications
  const style = document.createElement('style');
  style.textContent = `
    .notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 15px 20px;
      background-color: var(--success-color);
      color: white;
      border-radius: 5px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s ease;
      z-index: 9999;
    }
    .notification.show {
      transform: translateY(0);
      opacity: 1;
    }
    .notification.error {
      background-color: var(--danger-color);
    }
    .cart-btn.added {
      background-color: var(--success-color);
      color: white;
    }
  `;
  document.head.appendChild(style);
});



document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.querySelector('.carousel');
  const items = document.querySelectorAll('.carousel-item');
  const prevButton = document.querySelector('.carousel-control.prev');
  const nextButton = document.querySelector('.carousel-control.next');
  const indicators = document.querySelectorAll('.carousel-indicators .indicator');
  let currentIndex = 0;
  let interval;

  function showItem(index) {
      items.forEach((item, i) => {
          item.classList.toggle('active', i === index);
          indicators[i].classList.toggle('active', i === index);
      });
      currentIndex = index;
  }

  function nextItem() {
      const newIndex = (currentIndex + 1) % items.length;
      showItem(newIndex);
  }

  function prevItem() {
      const newIndex = (currentIndex - 1 + items.length) % items.length;
      showItem(newIndex);
  }

  function startAutoSlide() {
      interval = setInterval(nextItem, 5000);
  }

  function stopAutoSlide() {
      clearInterval(interval);
  }

  prevButton.addEventListener('click', () => {
      stopAutoSlide();
      prevItem();
      startAutoSlide();
  });

  nextButton.addEventListener('click', () => {
      stopAutoSlide();
      nextItem();
      startAutoSlide();
  });

  indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', () => {
          stopAutoSlide();
          showItem(index);
          startAutoSlide();
      });
  });

  carousel.addEventListener('mouseenter', stopAutoSlide);
  carousel.addEventListener('mouseleave', startAutoSlide);

  startAutoSlide();
});