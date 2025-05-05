document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu mobile
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (navToggle) {
      navToggle.addEventListener('click', function() {
        navLinks.classList.toggle('active');
      });
    }
    
    // Gestion du slider
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-slide');
    const nextBtn = document.querySelector('.next-slide');
    
    if (slides.length > 0) {
      let currentSlide = 0;
      const slideInterval = 5000; // Intervalle en millisecondes
      
      // Afficher le slide actuel
      function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
      }
      
      // Passer au slide suivant
      function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
      }
      
      // Passer au slide précédent
      function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
      }
      
      // Événements des boutons
      if (nextBtn) {
        nextBtn.addEventListener('click', function() {
          nextSlide();
          resetInterval();
        });
      }
      
      if (prevBtn) {
        prevBtn.addEventListener('click', function() {
          prevSlide();
          resetInterval();
        });
      }
      
      // Événements des points
      dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
          showSlide(index);
          resetInterval();
        });
      });
      
      // Défilement automatique
      let slideTimer = setInterval(nextSlide, slideInterval);
      
      function resetInterval() {
        clearInterval(slideTimer);
        slideTimer = setInterval(nextSlide, slideInterval);
      }
    }
    
    // Gestion des boutons de wishlist
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    
    wishlistBtns.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const oeuvreId = this.getAttribute('data-id');
        
        // Animation du bouton
        this.querySelector('i').classList.toggle('fas');
        this.querySelector('i').classList.toggle('far');
        
        // Envoyer la requête AJAX pour ajouter à la wishlist
        // Pour l'exemple, on montre juste une notification
        showNotification('Œuvre ajoutée à votre liste de souhaits !');
        
        // En réalité, vous enverriez une requête AJAX ici
        /*
        fetch('add-to-wishlist.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'oeuvre_id=' + oeuvreId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Œuvre ajoutée à votre liste de souhaits !');
          } else {
            showNotification('Erreur: ' + data.message, 'error');
          }
        });
        */
      });
    });
    
    // Gestion des boutons de panier
    const cartBtns = document.querySelectorAll('.cart-btn');
    
    cartBtns.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const oeuvreId = this.getAttribute('data-id');
        
        // Animation du bouton
        this.classList.add('added');
        setTimeout(() => {
          this.classList.remove('added');
        }, 1000);
        
        // Mettre à jour le compteur du panier
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
          cartCount.textContent = parseInt(cartCount.textContent) + 1;
        }
        
        // Afficher la notification
        showNotification('Œuvre ajoutée au panier !');
        
        // En réalité, vous enverriez une requête AJAX ici
        /*
        fetch('add-to-cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'oeuvre_id=' + oeuvreId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Œuvre ajoutée au panier !');
            // Mettre à jour le compteur
            if (cartCount) {
              cartCount.textContent = data.cart_count;
            }
          } else {
            showNotification('Erreur: ' + data.message, 'error');
          }
        });
        */
      });
    });
    
    // Formulaire de newsletter
    const newsletterForm = document.getElementById('newsletter-form');
    
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        
        // Valider l'email
        if (!validateEmail(email)) {
          showNotification('Veuillez entrer une adresse email valide.', 'error');
          return;
        }
        
        // Afficher la notification
        showNotification('Merci pour votre inscription à notre newsletter !');
        
        // Réinitialiser le formulaire
        this.reset();
        
        // En réalité, vous enverriez une requête AJAX ici
        /*
        fetch('subscribe-newsletter.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Merci pour votre inscription à notre newsletter !');
            this.reset();
          } else {
            showNotification('Erreur: ' + data.message, 'error');
          }
        });
        */
      });
    }
    
    // Fonction pour valider un email
    function validateEmail(email) {
      const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
    }
    
    // Fonction pour afficher des notifications
    function showNotification(message, type = 'success') {
      // Vérifier si une notification existe déjà
      let notification = document.querySelector('.notification');
      
      // Créer la notification si elle n'existe pas
      if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification';
        document.body.appendChild(notification);
      }
      
      // Ajouter la classe de type
      notification.className = 'notification ' + type;
      
      // Définir le message
      notification.textContent = message;
      
      // Afficher la notification
      notification.classList.add('show');
      
      // Cacher la notification après 3 secondes
      setTimeout(() => {
        notification.classList.remove('show');
      }, 3000);
    }
    
    // Ajouter le style pour les notifications
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