<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Plateforme Artisanale</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .search-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 15px;
        }

        .search-header h1 {
            margin: 0 0 20px 0;
            font-size: 2.5em;
        }

        .search-form {
            display: flex;
            gap: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1.1em;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .search-btn {
            padding: 15px 30px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #219a52;
            transform: translateY(-2px);
        }

        .search-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #667eea;
            font-size: 1.2em;
        }

        .loading i {
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .search-stats {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            color: #333;
            font-size: 1.1em;
        }

        .results-section {
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 1.8em;
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 15px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .result-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            opacity: 0;
            transform: translateY(30px);
        }

        .result-card.show {
            opacity: 1;
            transform: translateY(0);
        }

        .result-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }

        .card-image {
            height: 200px;
            background: linear-gradient(45deg, #f0f2f5, #e1e5e9);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-image i {
            font-size: 3em;
            color: #999;
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .card-subtitle {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .card-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
            color: #999;
        }

        .price {
            font-size: 1.2em;
            font-weight: 600;
            color: #27ae60;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            background: white;
            border-radius: 15px;
            margin: 20px 0;
        }

        .no-results i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }

        .artisan-card .card-meta {
            display: block;
        }

        .artisan-location {
            color: #667eea;
            font-size: 0.9em;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-card .card-subtitle {
            color: #e67e22;
        }

        .event-location {
            color: #95a5a6;
            font-size: 0.9em;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
            
            .search-header h1 {
                font-size: 2em;
            }

            .search-form {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="search-container">
    <div class="search-header">
        <h1>Recherche Artisanale</h1>
        <form class="search-form" id="searchForm">
            <input 
                type="text" 
                class="search-input" 
                id="searchInput" 
                placeholder="Rechercher des œuvres, artisans ou événements..."
                autocomplete="off"
            >
            <button type="submit" class="search-btn" id="searchBtn">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </form>
    </div>

    <div id="loadingSection" class="loading" style="display: none;">
        <i class="fas fa-spinner"></i>
        Recherche en cours...
    </div>

    <div id="statsSection" class="search-stats" style="display: none;"></div>
    
    <div id="errorSection" class="error-message" style="display: none;"></div>

    <div id="resultsContainer"></div>
</div>

<script>
class SearchManager {
    constructor() {
        this.searchForm = document.getElementById('searchForm');
        this.searchInput = document.getElementById('searchInput');
        this.searchBtn = document.getElementById('searchBtn');
        this.loadingSection = document.getElementById('loadingSection');
        this.statsSection = document.getElementById('statsSection');
        this.errorSection = document.getElementById('errorSection');
        this.resultsContainer = document.getElementById('resultsContainer');
        
        this.currentRequest = null;
        this.searchTimeout = null;
        
        this.init();
    }

    init() {
        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Recherche en temps réel avec délai
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                if (this.searchInput.value.trim().length >= 2) {
                    this.performSearch();
                } else if (this.searchInput.value.trim().length === 0) {
                    this.clearResults();
                }
            }, 500);
        });
    }

    performSearch() {
        const query = this.searchInput.value.trim();
        
        if (!query) {
            this.clearResults();
            return;
        }

        // Annuler la requête précédente si elle existe
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        this.showLoading();
        this.hideError();

        // Créer nouvelle requête XMLHttpRequest
        this.currentRequest = new XMLHttpRequest();
        
        this.currentRequest.open('GET', `search-ajax.php?q=${encodeURIComponent(query)}`, true);
        
        this.currentRequest.onreadystatechange = () => {
            if (this.currentRequest.readyState === 4) {
                this.hideLoading();
                
                if (this.currentRequest.status === 200) {
                    try {
                        const response = JSON.parse(this.currentRequest.responseText);
                        this.handleResponse(response, query);
                    } catch (error) {
                        this.showError('Erreur lors du traitement de la réponse');
                    }
                } else {
                    this.showError('Erreur de connexion au serveur');
                }
                
                this.currentRequest = null;
            }
        };

        this.currentRequest.onerror = () => {
            this.hideLoading();
            this.showError('Erreur de réseau');
            this.currentRequest = null;
        };

        this.currentRequest.send();
    }

    handleResponse(response, query) {
        if (!response.success) {
            this.showError(response.message || 'Erreur lors de la recherche');
            return;
        }

        this.showStats(response.total, query);
        
        if (response.total === 0) {
            this.showNoResults(query);
        } else {
            this.displayResults(response);
        }
    }

    showLoading() {
        this.loadingSection.style.display = 'block';
        this.searchBtn.disabled = true;
        this.searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche...';
    }

    hideLoading() {
        this.loadingSection.style.display = 'none';
        this.searchBtn.disabled = false;
        this.searchBtn.innerHTML = '<i class="fas fa-search"></i> Rechercher';
    }

    showError(message) {
        this.errorSection.textContent = message;
        this.errorSection.style.display = 'block';
    }

    hideError() {
        this.errorSection.style.display = 'none';
    }

    showStats(total, query) {
        this.statsSection.innerHTML = `${total} résultat(s) trouvé(s) pour "<strong>${this.escapeHtml(query)}</strong>"`;
        this.statsSection.style.display = 'block';
    }

    showNoResults(query) {
        this.resultsContainer.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h2>Aucun résultat trouvé</h2>
                <p>Aucun résultat pour "${this.escapeHtml(query)}". Essayez avec d'autres mots-clés.</p>
            </div>
        `;
    }

    displayResults(response) {
        let html = '';

        // Œuvres
        if (response.oeuvres.length > 0) {
            html += this.createSection('oeuvres', 'fas fa-palette', 'Œuvres', response.oeuvres, (item) => `
                <a href="oeuvre-details.php?id=${item.id}" class="result-card oeuvre-card">
                    <div class="card-image">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-title">${this.escapeHtml(item.nom)}</div>
                        <div class="card-subtitle">Par ${this.escapeHtml(item.artisan || 'Artisan inconnu')}</div>
                        ${item.description ? `<div class="card-description">${this.escapeHtml(item.description.substring(0, 100))}...</div>` : ''}
                        <div class="card-meta">
                            <span>Œuvre d'art</span>
                            <span class="price">${parseFloat(item.prix).toFixed(2)}€</span>
                        </div>
                    </div>
                </a>
            `);
        }

        // Artisans
        if (response.artisans.length > 0) {
            html += this.createSection('artisans', 'fas fa-users', 'Artisans', response.artisans, (item) => `
                <a href="profil-artisan.php?id=${item.id}" class="result-card artisan-card">
                    <div class="card-image">
                        ${item.photo ? 
                            `<img src="../images/${this.escapeHtml(item.photo)}" alt="${this.escapeHtml(item.nom)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                             <i class="fas fa-user" style="display:none;"></i>` :
                            '<i class="fas fa-user"></i>'
                        }
                    </div>
                    <div class="card-content">
                        <div class="card-title">${this.escapeHtml(item.nom)}</div>
                        <div class="card-subtitle">${this.escapeHtml(item.specialite || 'Spécialité non définie')}</div>
                        <div class="card-meta">
                            <div class="artisan-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                ${this.escapeHtml(item.ville || 'Localisation non définie')}
                            </div>
                        </div>
                    </div>
                </a>
            `);
        }

        // Événements
        if (response.evenements.length > 0) {
            html += this.createSection('evenements', 'fas fa-calendar-alt', 'Événements', response.evenements, (item) => `
                <a href="evenement-details.php?id=${item.id}" class="result-card event-card">
                    <div class="card-image">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-title">${this.escapeHtml(item.nom)}</div>
                        <div class="card-subtitle"><i class="fas fa-calendar"></i> ${item.date}</div>
                        ${item.description ? `<div class="card-description">${this.escapeHtml(item.description.substring(0, 100))}...</div>` : ''}
                        <div class="card-meta">
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                ${this.escapeHtml(item.lieu || 'Lieu non défini')}
                            </div>
                        </div>
                    </div>
                </a>
            `);
        }

        this.resultsContainer.innerHTML = html;
        this.animateResults();
    }

    createSection(type, icon, title, items, cardTemplate) {
        return `
            <div class="results-section">
                <h2 class="section-title">
                    <i class="${icon}"></i>
                    ${title} (${items.length})
                </h2>
                <div class="results-grid">
                    ${items.map(cardTemplate).join('')}
                </div>
            </div>
        `;
    }

    animateResults() {
        const cards = this.resultsContainer.querySelectorAll('.result-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('show');
            }, index * 100);
        });
    }

    clearResults() {
        this.resultsContainer.innerHTML = '';
        this.statsSection.style.display = 'none';
        this.hideError();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    new SearchManager();
});
</script>

</body>
</html>