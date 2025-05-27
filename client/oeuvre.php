<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil - Artisano</title>
  <link rel="stylesheet" href="oeuvre.css">
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
    header, footer { background-color: #f5f5f5; padding: 10px 20px; }
    nav a { margin: 0 10px; text-decoration: none; color: purple; font-weight: bold; }

    .hero {
      background-image: url('../images/exposition_artisant_4.jpg');
      background-size: cover;
      background-position: center;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white !important;
      font-size: 2em;
      font-weight: bold;
    }

    .content {
      display: flex;
      padding: 20px;
    }

    .filters {
      width: 250px;
      padding-right: 20px;
    }

    .filters h4 { margin-bottom: 5px; }

    .main-area {
      flex: 1;
      text-align: center;
    }

    .footer-columns {
      display: flex;
      justify-content: space-between;
      padding: 20px;
    }

    .footer-columns div { width: 30%; }

    /* Modale */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 15% auto;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      text-align: center;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }
  </style>
</head>

<body>

<?php include 'includes/header.php'; ?>

<div class="hero">
  <h1 style="color:white">Oeuvres d’art</h1>
</div>

<div class="content">
  <aside class="filters">
  <h4>Mots-clés</h4>
<div id="selectedCategories"></div>

    <h4>Label</h4>
    <input type="checkbox" checked> Label<br>
    <small>Description</small><br><br>

    <input type="checkbox" checked> Label<br>
    <small>Description</small><br><br>

    <input type="checkbox" checked> Label<br>
    <small>Description</small><br><br>

    <h4>Prix</h4>
    <input type="range" id="priceRange" min="0" max="300" value="300">
    <span id="priceValue">300 €</span>

    <h4>Couleurs</h4>
    <input type="checkbox" checked> Marron<br>
    <input type="checkbox" checked> Vert<br>
    <input type="checkbox" checked> Rouge<br>

    <h4>Taille</h4>
    <input type="checkbox" checked> Grand<br>
    <input type="checkbox" checked> Moyen<br>
    <input type="checkbox" checked> Petit<br>
  </aside>

  <main class="main-area">
    <?php include 'afficher_oeuvres.php'?>
  </main>
</div>

<footer>
  <div class="footer-columns">
    <div>
      <img src="logo.png" alt="Logo Artisano" style="height: 40px;">
    </div>
    <div>
      <h4>Menu</h4>
      <p>Accueil<br>Forum<br>Vente<br>FAQ</p>
    </div>
    <div>
      <h4>Contact</h4>
      <p>notreequipe@lameca-group.com</p>
    </div>
  </div>
  <p style="text-align: center;">https://www.lameca.com/accueil</p>
</footer>

<!-- Fenêtre pop-up -->
<!-- Fenêtre pop-up -->
<div id="popupModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <h2>Choisissez des catégories</h2>
    <form id="categoryForm">
      <label><input type="checkbox" value="Vase"> Vase</label><br>
      <label><input type="checkbox" value="Peinture"> Peinture</label><br>
      <label><input type="checkbox" value="Bol"> Bol</label><br>
      <label><input type="checkbox" value="Statue"> Statue</label><br>
      <label><input type="checkbox" value="Table"> Table</label><br><br>
      <button type="submit">Ajouter</button>
    </form>
  </div>
</div>


<script>
  const modal = document.getElementById("popupModal");
  const btn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");

  btn.onclick = function () {
    modal.style.display = "block";
  }

  closeBtn.onclick = function () {
    modal.style.display = "none";
  }

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
  const form = document.getElementById("categoryForm");
  const selectedCategoriesDiv = document.getElementById("selectedCategories");

  let selectedCategories = [];

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const checked = form.querySelectorAll("input[type='checkbox']:checked");

    checked.forEach((checkbox) => {
      const value = checkbox.value;

      if (!selectedCategories.includes(value)) {
        selectedCategories.push(value);
        addCategoryToSidebar(value);
      }

      checkbox.checked = false;
    });

    modal.style.display = "none";
  });

  function addCategoryToSidebar(category) {
  const div = document.createElement("div");
  div.textContent = category + " ";

  const removeBtn = document.createElement("span");
  removeBtn.textContent = "✕";
  removeBtn.style.cursor = "pointer";
  removeBtn.style.color = "red";
  removeBtn.onclick = function () {
    selectedCategories = selectedCategories.filter(c => c !== category);
    div.remove();
    localStorage.setItem("selectedCategories", JSON.stringify(selectedCategories)); // 🔁 mettre à jour le stockage
  };

  div.appendChild(removeBtn);
  selectedCategoriesDiv.appendChild(div);

  // 🔁 Sauvegarder dans le stockage local
  localStorage.setItem("selectedCategories", JSON.stringify(selectedCategories));
}

 // 🔁 Restauration des catégories stockées
const savedCategories = JSON.parse(localStorage.getItem("selectedCategories")) || [];
savedCategories.forEach(category => {
  if (!selectedCategories.includes(category)) {
    selectedCategories.push(category);
    addCategoryToSidebar(category);
  }
});

// 🔁 Sauvegarde + restauration de la valeur du curseur
const priceRange = document.getElementById("priceRange");
const priceValue = document.getElementById("priceValue");

// Charger la valeur enregistrée
const savedPrice = localStorage.getItem("selectedPrice");
if (savedPrice !== null) {
  priceRange.value = savedPrice;
  priceValue.textContent = savedPrice + " €";
} else {
  priceValue.textContent = priceRange.value + " €";
}

priceRange.addEventListener("input", function () {
  priceValue.textContent = priceRange.value + " €";
  localStorage.setItem("selectedPrice", priceRange.value); // 🔁 mettre à jour le stockage
});


</script>

</body>
</html>
