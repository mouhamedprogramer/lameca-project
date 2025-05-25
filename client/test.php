<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débogage Wishlist - Artisano</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .debug-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .debug-title {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        .test-button {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }
        .test-button:hover {
            background: #2980b9;
        }
        .success {
            background: #27ae60;
        }
        .error {
            background: #e74c3c;
        }
        .result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .result.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .result.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .result.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .simulated-oeuvre {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .oeuvre-info {
            flex: 1;
        }
        .wishlist-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .wishlist-btn:hover {
            background: #c0392b;
            transform: scale(1.05);
        }
        .wishlist-btn.in-wishlist {
            background: #27ae60;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Débogage du Système Wishlist</h1>
        <p>Cette page vous aide à identifier et résoudre les problèmes avec votre système de liste de souhaits.</p>
    </div>

    <div class="container">
        <div class="debug-section">
            <h2 class="debug-title">1. Tests de Connectivité</h2>
            <button class="test-button" onclick="testConnexion()">Tester la Session</button>
            <button class="test-button" onclick="testDatabase()">Tester la Base de Données</button>
            <button class="test-button" onclick="testWishlistAPI()">Tester l'API Wishlist</button>
            <div id="connectivity-result" class="result" style="display: none;"></div>
        </div>

        <div class="debug-section">
            <h2 class="debug-title">2. Simulation d'Ajout/Suppression</h2>
            <p>Testez l'ajout et la suppression d'œuvres fictives :</p>
            
            <div class="simulated-oeuvre">
                <div class="oeuvre-info">
                    <strong>Œuvre Test #1</strong><br>
                    <small>ID: 1 - Pour tester l'ajout</small>
                </div>
                <button class="wishlist-btn" data-id="1" onclick="testWishlistAction(1, this)">
                    ❤️ Ajouter aux favoris
                </button>
            </div>

            <div class="simulated-oeuvre">
                <div class="oeuvre-info">
                    <strong>Œuvre Test #2</strong><br>
                    <small>ID: 2 - Pour tester l'ajout</small>
                </div>
                <button class="wishlist-btn" data-id="2" onclick="testWishlistAction(2, this)">
                    ❤️ Ajouter aux favoris
                </button>
            </div>

            <div id="simulation-result" class="result" style="display: none;"></div>
        </div>

        <div class="debug-section">
            <h2 class="debug-title">3. Vérification du Code JavaScript</h2>
            <button class="test-button" onclick="testJavaScript()">Tester les Fonctions JS</button>
            <button class="test-button" onclick="inspectOeuvresManager()">Inspecter OeuvresManager</button>
            <div id="javascript-result" class="result" style="display: none;"></div>
        </div>

        <div class="debug-section">
            <h2 class="debug-title">4. Tests de l'Interface</h2>
            <button class="test-button" onclick="simulateOeuvresPage()">Simuler la Page Œuvres</button>
            <button class="test-button" onclick="testNotifications()">Tester les Notifications</button>
            <div id="interface-result" class="result" style="display: none;"></div>
        </div>

        <div class="debug-section">
            <h2 class="debug-title">5. Diagnostic Complet</h2>
            <button class="test-button" onclick="runFullDiagnosis()">🔍 Diagnostic Complet</button>
            <div id="diagnosis-result" class="result" style="display: none;"></div>
        </div>
    </div>

    <script>
        // Fonction de notification réutilisable
        function showResult(elementId, message, type = 'info') {
            const element = document.getElementById(elementId);
            element.className = `result ${type}`;
            element.textContent = message;
            element.style.display = 'block';
        }

        // Test 1: Connectivité
        async function testConnexion() {
            showResult('connectivity-result', 'Test de la session en cours...', 'info');
            
            try {
                // Test de session PHP via un appel à wishlist.php
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ test: true })
                });
                
                const data = await response.json();
                
                if (response.status === 401) {
                    showResult('connectivity-result', 
                        '❌ PROBLÈME: Utilisateur non connecté ou pas de rôle Client\n' +
                        'Solution: Vérifiez que vous êtes connecté avec un compte Client', 'error');
                } else {
                    showResult('connectivity-result', 
                        '✅ Session OK: L\'utilisateur est bien connecté en tant que Client\n' +
                        'Réponse: ' + JSON.stringify(data, null, 2), 'success');
                }
            } catch (error) {
                showResult('connectivity-result', 
                    '❌ ERREUR de connectivité: ' + error.message + '\n' +
                    'Vérifiez que le fichier actions/wishlist.php existe et est accessible', 'error');
            }
        }

        async function testDatabase() {
            showResult('connectivity-result', 'Test de la base de données en cours...', 'info');
            
            try {
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        idOeuvre: 9999, 
                        action: 'add'
                    })
                });
                
                const data = await response.json();
                
                if (data.message && data.message.includes('wishlist n\'est pas encore disponible')) {
                    showResult('connectivity-result', 
                        '❌ PROBLÈME: Table wishlist n\'existe pas\n' +
                        'Solution: Exécutez votre script SQL pour créer la table wishlist', 'error');
                } else if (data.message && data.message.includes('Œuvre introuvable')) {
                    showResult('connectivity-result', 
                        '✅ Base de données OK: La table wishlist existe\n' +
                        'L\'erreur "Œuvre introuvable" est normale pour cet ID fictif', 'success');
                } else {
                    showResult('connectivity-result', 
                        'Réponse inattendue: ' + JSON.stringify(data, null, 2), 'info');
                }
            } catch (error) {
                showResult('connectivity-result', 
                    '❌ ERREUR base de données: ' + error.message, 'error');
            }
        }

        async function testWishlistAPI() {
            showResult('connectivity-result', 'Test de l\'API wishlist avec une œuvre réelle...', 'info');
            
            try {
                // D'abord, essayons de récupérer la liste des œuvres
                const oeuvresResponse = await fetch('api/get-oeuvres.php');
                let testOeuvreId = 1; // ID par défaut
                
                if (oeuvresResponse.ok) {
                    const oeuvres = await oeuvresResponse.json();
                    if (oeuvres.length > 0) {
                        testOeuvreId = oeuvres[0].idOeuvre;
                    }
                }
                
                // Test d'ajout
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        idOeuvre: testOeuvreId, 
                        action: 'add'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showResult('connectivity-result', 
                        '✅ API Wishlist OK: Ajout réussi\n' +
                        `Œuvre ${testOeuvreId} ajoutée avec succès\n` +
                        'Réponse: ' + JSON.stringify(data, null, 2), 'success');
                } else {
                    showResult('connectivity-result', 
                        '⚠️ API Wishlist: Réponse d\'erreur (peut être normale)\n' +
                        'Message: ' + data.message + '\n' +
                        'Réponse complète: ' + JSON.stringify(data, null, 2), 'info');
                }
            } catch (error) {
                showResult('connectivity-result', 
                    '❌ ERREUR API: ' + error.message, 'error');
            }
        }

        // Test 2: Simulation
        async function testWishlistAction(oeuvreId, button) {
            const originalText = button.textContent;
            button.textContent = '⏳ Test...';
            button.classList.add('loading');
            
            try {
                const isInWishlist = button.classList.contains('in-wishlist');
                const action = isInWishlist ? 'remove' : 'add';
                
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        idOeuvre: oeuvreId, 
                        action: action
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    button.classList.toggle('in-wishlist');
                    button.textContent = isInWishlist ? '❤️ Ajouter aux favoris' : '✅ Dans les favoris';
                    
                    showResult('simulation-result', 
                        `✅ Succès: ${action === 'add' ? 'Ajout' : 'Suppression'} réussi(e)\n` +
                        `Œuvre ${oeuvreId}: ${data.message}\n` +
                        'Réponse: ' + JSON.stringify(data, null, 2), 'success');
                } else {
                    showResult('simulation-result', 
                        `❌ Échec: ${data.message}\n` +
                        'Réponse: ' + JSON.stringify(data, null, 2), 'error');
                    button.textContent = originalText;
                }
            } catch (error) {
                showResult('simulation-result', 
                    `❌ Erreur lors du test: ${error.message}`, 'error');
                button.textContent = originalText;
            } finally {
                button.classList.remove('loading');
            }
        }

        // Test 3: JavaScript
        function testJavaScript() {
            let results = [];
            
            // Vérifier si OeuvresManager existe
            if (typeof window.OeuvresManager !== 'undefined') {
                results.push('✅ Classe OeuvresManager trouvée');
            } else {
                results.push('❌ Classe OeuvresManager non trouvée');
            }
            
            // Vérifier si l'instance existe
            if (typeof window.oeuvresManager !== 'undefined') {
                results.push('✅ Instance oeuvresManager trouvée');
            } else {
                results.push('❌ Instance oeuvresManager non trouvée');
            }
            
            // Vérifier les event listeners
            const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
            results.push(`ℹ️ ${wishlistButtons.length} bouton(s) wishlist trouvé(s) sur cette page`);
            
            showResult('javascript-result', results.join('\n'), 
                results.some(r => r.startsWith('❌')) ? 'error' : 'success');
        }

        function inspectOeuvresManager() {
            if (typeof window.oeuvresManager !== 'undefined') {
                const methods = Object.getOwnPropertyNames(Object.getPrototypeOf(window.oeuvresManager));
                showResult('javascript-result', 
                    '✅ OeuvresManager actif\n' +
                    'Méthodes disponibles: ' + methods.join(', '), 'success');
            } else {
                showResult('javascript-result', 
                    '❌ OeuvresManager non initialisé\n' +
                    'Vérifiez que le fichier oeuvres.js est bien chargé', 'error');
            }
        }

        // Test 4: Interface
        function simulateOeuvresPage() {
            // Créer des boutons de test dynamiquement
            const container = document.getElementById('interface-result');
            container.innerHTML = `
                <div style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>Simulation de la page œuvres:</h4>
                    <div style="display: flex; gap: 10px; align-items: center; margin: 10px 0;">
                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA2MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjZGRkIi8+Cjx0ZXh0IHg9IjMwIiB5PSIyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSIjOTk5Ij7FkXV2cmU8L3RleHQ+Cjwvc3ZnPgo=" alt="Œuvre test" style="border-radius: 4px;">
                        <div style="flex: 1;">
                            <strong>Œuvre de Test</strong><br>
                            <small>Par Artisan Test - 150€</small>
                        </div>
                        <button class="wishlist-btn add-to-wishlist" data-id="999" onclick="testWishlistInInterface(this)">
                            ❤️
                        </button>
                    </div>
                </div>
            `;
            container.className = 'result info';
            container.style.display = 'block';
        }

        function testWishlistInInterface(button) {
            // Simuler le comportement de OeuvresManager
            if (window.oeuvresManager && typeof window.oeuvresManager.handleWishlist === 'function') {
                window.oeuvresManager.handleWishlist(button);
            } else {
                alert('⚠️ OeuvresManager non disponible. Le JavaScript ne fonctionne pas correctement.');
            }
        }

        function testNotifications() {
            // Test des notifications
            if (window.oeuvresManager && typeof window.oeuvresManager.showNotification === 'function') {
                window.oeuvresManager.showNotification('✅ Test de notification réussi !', 'success');
                setTimeout(() => {
                    window.oeuvresManager.showNotification('ℹ️ Notification d\'information', 'info');
                }, 1000);
                setTimeout(() => {
                    window.oeuvresManager.showNotification('❌ Notification d\'erreur (test)', 'error');
                }, 2000);
                
                showResult('interface-result', 
                    '✅ Notifications testées\n' +
                    'Vous devriez voir 3 notifications apparaître en haut à droite', 'success');
            } else {
                showResult('interface-result', 
                    '❌ Système de notifications non disponible\n' +
                    'OeuvresManager non initialisé', 'error');
            }
        }

        // Test 5: Diagnostic complet
        async function runFullDiagnosis() {
            showResult('diagnosis-result', '🔍 Diagnostic en cours...', 'info');
            
            let diagnosticResults = [];
            
            // 1. Vérification JavaScript
            if (typeof window.oeuvresManager !== 'undefined') {
                diagnosticResults.push('✅ JavaScript: OeuvresManager initialisé');
            } else {
                diagnosticResults.push('❌ JavaScript: OeuvresManager manquant');
            }
            
            // 2. Test de connectivité
            try {
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ test: true })
                });
                
                if (response.status === 401) {
                    diagnosticResults.push('❌ Session: Utilisateur non connecté ou mauvais rôle');
                } else {
                    diagnosticResults.push('✅ Session: Utilisateur connecté correctement');
                }
            } catch (error) {
                diagnosticResults.push('❌ Connectivité: Impossible de joindre l\'API');
            }
            
            // 3. Vérification de la table
            try {
                const response = await fetch('actions/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idOeuvre: 9999, action: 'add' })
                });
                
                const data = await response.json();
                
                if (data.message && data.message.includes('wishlist n\'est pas encore disponible')) {
                    diagnosticResults.push('❌ Base de données: Table wishlist manquante');
                } else {
                    diagnosticResults.push('✅ Base de données: Table wishlist existe');
                }
            } catch (error) {
                diagnosticResults.push('❌ Base de données: Erreur de connexion');
            }
            
            // 4. Vérifications des fichiers
            diagnosticResults.push('ℹ️ Fichiers requis:');
            diagnosticResults.push('  - actions/wishlist.php (API backend)');
            diagnosticResults.push('  - js/oeuvres.js (JavaScript frontend)');
            diagnosticResults.push('  - wishlist.php (page d\'affichage)');
            
            // 5. Conseils de dépannage
            diagnosticResults.push('\n🔧 SOLUTIONS RECOMMANDÉES:');
            
            if (diagnosticResults.some(r => r.includes('OeuvresManager manquant'))) {
                diagnosticResults.push('  1. Vérifiez que js/oeuvres.js est inclus dans vos pages');
                diagnosticResults.push('  2. Vérifiez les erreurs JavaScript dans la console (F12)');
            }
            
            if (diagnosticResults.some(r => r.includes('non connecté'))) {
                diagnosticResults.push('  3. Connectez-vous avec un compte Client');
                diagnosticResults.push('  4. Vérifiez la gestion des sessions PHP');
            }
            
            if (diagnosticResults.some(r => r.includes('Table wishlist manquante'))) {
                diagnosticResults.push('  5. Exécutez le script SQL de création de la table wishlist');
                diagnosticResults.push('  6. Vérifiez les permissions de la base de données');
            }
            
            diagnosticResults.push('\n📋 VÉRIFICATIONS SUPPLÉMENTAIRES:');
            diagnosticResults.push('  - Ouvrez la console du navigateur (F12) pour voir les erreurs');
            diagnosticResults.push('  - Vérifiez que les boutons ont bien l\'attribut data-id');
            diagnosticResults.push('  - Testez d\'abord sur la page oeuvres.php');
            
            const hasErrors = diagnosticResults.some(r => r.startsWith('❌'));
            showResult('diagnosis-result', diagnosticResults.join('\n'), 
                hasErrors ? 'error' : 'success');
        }
    </script>
</body>
</html>