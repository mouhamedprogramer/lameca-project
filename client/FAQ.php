<?php
// Inclure le header
include 'includes/header.php';

// Récupérer toutes les FAQ depuis la base de données
$faqs = [];
if (isset($conn)) {
    try {
        $query = "SELECT idFaq, question, reponse FROM FAQ ORDER BY idFaq ASC";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $faqs[] = $row;
            }
        }
    } catch (Exception $e) {
        // En cas d'erreur, on continue avec un tableau vide
        $faqs = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Questions Fréquentes | Artisano</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding-top: 0; /* Supprime l'espace en haut */
}

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .faq-title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .faq-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .search-container {
            position: relative;
            max-width: 500px;
            margin: 2rem auto;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            border: none;
            border-radius: 50px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input:focus {
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.2rem;
        }

        .faq-list {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .faq-item {
            background: white;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .faq-question {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .faq-question::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            transition: all 0.5s ease;
        }

        .faq-question:hover::before {
            left: 0;
        }

        .faq-question-text {
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .faq-toggle {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        .faq-answer-content {
            padding: 2rem;
            color: #4a5568;
            line-height: 1.8;
            font-size: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
            font-size: 1.1rem;
            display: none;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .contact-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            color: white;
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .contact-cta h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .contact-cta p {
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .contact-btn {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .faq-container {
                padding: 1rem;
            }

            .faq-title {
                font-size: 2rem;
            }

            .faq-subtitle {
                font-size: 1rem;
            }

            .faq-question {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }

            .faq-answer-content {
                padding: 1.5rem;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .faq-title {
                font-size: 1.8rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .search-input {
                padding: 0.8rem 0.8rem 0.8rem 2.5rem;
            }

            .search-icon {
                left: 0.8rem;
            }
        }
    </style>
</head>
<body>
<br><br><br><br><br>
    <div class="faq-container">
        <!-- Header Section -->
        <div class="faq-header">
            <h1 class="faq-title">Questions Fréquentes</h1>
            <p class="faq-subtitle">
                Trouvez rapidement les réponses à vos questions sur Artisano. 
                Notre équipe a compilé les questions les plus courantes pour vous aider.
            </p>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= count($faqs) ?></div>
                <div class="stat-label">Questions disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support disponible</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">< 2h</div>
                <div class="stat-label">Temps de réponse</div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Rechercher une question...">
        </div>

        <!-- FAQ List -->
        <div class="faq-list" id="faqList">
            <?php if (!empty($faqs)): ?>
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item" data-question="<?= strtolower(htmlspecialchars($faq['question'])) ?>" data-answer="<?= strtolower(htmlspecialchars($faq['reponse'])) ?>">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text"><?= htmlspecialchars($faq['question']) ?></span>
                            <i class="fas fa-chevron-down faq-toggle"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <?= nl2br(htmlspecialchars($faq['reponse'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-question-circle"></i>
                    <p>Aucune question fréquente disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- No Results Message -->
        <div class="no-results" id="noResults">
            <i class="fas fa-search"></i>
            <p>Aucune question ne correspond à votre recherche.</p>
        </div>

        <!-- Contact CTA -->
        <div class="contact-cta">
            <h3>Vous ne trouvez pas votre réponse ?</h3>
            <p>Notre équipe de support est là pour vous aider. Contactez-nous et nous vous répondrons rapidement.</p>
            <a href="contact.php" class="contact-btn">
                <i class="fas fa-envelope"></i> Nous contacter
            </a>
        </div>
    </div>

    <script>
        // Toggle FAQ items
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const faqItems = document.querySelectorAll('.faq-item');
            const noResults = document.getElementById('noResults');
            let hasResults = false;

            faqItems.forEach(item => {
                const question = item.dataset.question;
                const answer = item.dataset.answer;
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                    item.classList.remove('active');
                }
            });

            // Show/hide no results message
            if (searchTerm === '') {
                noResults.style.display = 'none';
            } else {
                noResults.style.display = hasResults ? 'none' : 'block';
            }
        });

        // Add smooth scroll behavior
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                setTimeout(() => {
                    this.parentElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
            }
        });
    </script>
    
</script>
<?php
// Inclure le footer        
include 'includes/footer.php';
?>
</body>
</html>