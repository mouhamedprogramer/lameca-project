<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['artisan'])) {
    header('Location: connexion.php');
    exit;
}

$idUtilisateur = $_SESSION['artisan'];
$roleUtilisateur = $_SESSION['role'];

// Traitement AJAX pour envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'send_message') {
        $idRecepteur = intval($_POST['id_recepteur']);
        $contenu = htmlspecialchars(trim($_POST['contenu']));
        
        if (!empty($contenu) && $idRecepteur > 0) {
            $sql = "INSERT INTO Message (idEmetteur, idRecepteur, contenu) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $idUtilisateur, $idRecepteur, $contenu);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Message envoyé']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Message vide']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'mark_read') {
        $idMessage = intval($_POST['id_message']);
        $sql = "UPDATE Message SET statut = 'Lu' WHERE idMessage = ? AND idRecepteur = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idMessage, $idUtilisateur);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($_POST['action'] === 'get_messages') {
        $idInterlocuteur = intval($_POST['id_interlocuteur']);
        
        $sql = "SELECT m.*, 
                       u_emetteur.nom as nom_emetteur, u_emetteur.prenom as prenom_emetteur, u_emetteur.photo as photo_emetteur,
                       u_recepteur.nom as nom_recepteur, u_recepteur.prenom as prenom_recepteur, u_recepteur.photo as photo_recepteur
                FROM Message m
                JOIN Utilisateur u_emetteur ON m.idEmetteur = u_emetteur.idUtilisateur
                JOIN Utilisateur u_recepteur ON m.idRecepteur = u_recepteur.idUtilisateur
                WHERE (m.idEmetteur = ? AND m.idRecepteur = ?) OR (m.idEmetteur = ? AND m.idRecepteur = ?)
                ORDER BY m.dateEnvoi ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $idUtilisateur, $idInterlocuteur, $idInterlocuteur, $idUtilisateur);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        // Marquer les messages reçus comme lus
        $sql_update = "UPDATE Message SET statut = 'Lu' WHERE idEmetteur = ? AND idRecepteur = ? AND statut = 'Non Lu'";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $idInterlocuteur, $idUtilisateur);
        $stmt_update->execute();
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        exit;
    }
}

// VERSION SIMPLIFIÉE - Récupérer les conversations de manière compatible
$conversations = [];

// Étape 1 : Récupérer tous les interlocuteurs uniques
$sql_interlocuteurs = "
    SELECT DISTINCT 
        CASE 
            WHEN m.idEmetteur = ? THEN m.idRecepteur 
            ELSE m.idEmetteur 
        END as idInterlocuteur
    FROM Message m
    WHERE m.idEmetteur = ? OR m.idRecepteur = ?
";

$stmt_inter = $conn->prepare($sql_interlocuteurs);
$stmt_inter->bind_param("iii", $idUtilisateur, $idUtilisateur, $idUtilisateur);
$stmt_inter->execute();
$result_inter = $stmt_inter->get_result();

$interlocuteurs = [];
while ($row = $result_inter->fetch_assoc()) {
    $interlocuteurs[] = $row['idInterlocuteur'];
}

// Étape 2 : Pour chaque interlocuteur, récupérer les détails
foreach ($interlocuteurs as $idInterlocuteur) {
    // Récupérer les infos de l'utilisateur
    $sql_user = "SELECT nom, prenom, photo, role FROM Utilisateur WHERE idUtilisateur = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $idInterlocuteur);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user = $result_user->fetch_assoc();
    
    if ($user) {
        // Récupérer la dernière activité
        $sql_derniere = "
            SELECT dateEnvoi, contenu 
            FROM Message 
            WHERE (idEmetteur = ? AND idRecepteur = ?) OR (idEmetteur = ? AND idRecepteur = ?)
            ORDER BY dateEnvoi DESC 
            LIMIT 1
        ";
        $stmt_derniere = $conn->prepare($sql_derniere);
        $stmt_derniere->bind_param("iiii", $idUtilisateur, $idInterlocuteur, $idInterlocuteur, $idUtilisateur);
        $stmt_derniere->execute();
        $result_derniere = $stmt_derniere->get_result();
        $derniere = $result_derniere->fetch_assoc();
        
        // Compter les messages non lus
        $sql_non_lus = "
            SELECT COUNT(*) as count 
            FROM Message 
            WHERE idEmetteur = ? AND idRecepteur = ? AND statut = 'Non Lu'
        ";
        $stmt_non_lus = $conn->prepare($sql_non_lus);
        $stmt_non_lus->bind_param("ii", $idInterlocuteur, $idUtilisateur);
        $stmt_non_lus->execute();
        $result_non_lus = $stmt_non_lus->get_result();
        $non_lus = $result_non_lus->fetch_assoc();
        
        // Ajouter à la liste des conversations
        $conversations[] = [
            'idInterlocuteur' => $idInterlocuteur,
            'nom_interlocuteur' => $user['prenom'] . ' ' . $user['nom'],
            'photo_interlocuteur' => $user['photo'],
            'role_interlocuteur' => $user['role'],
            'derniere_activite' => $derniere ? $derniere['dateEnvoi'] : date('Y-m-d H:i:s'),
            'dernier_message' => $derniere ? $derniere['contenu'] : 'Nouvelle conversation',
            'messages_non_lus' => $non_lus['count']
        ];
    }
}

// Trier par dernière activité
usort($conversations, function($a, $b) {
    return strtotime($b['derniere_activite']) - strtotime($a['derniere_activite']);
});

// Fonction pour formater le temps
function tempsEcoule($date) {
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return "À l'instant";
    if ($difference < 3600) return floor($difference / 60) . " min";
    if ($difference < 86400) return floor($difference / 3600) . " h";
    if ($difference < 2592000) return floor($difference / 86400) . " j";
    return date('d/m/Y', $timestamp);
}

include 'includes/header.php';
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 80px;
            padding-top: 0; /* Supprime l'espace en haut */

        }

        .messages-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            height: calc(100vh - 80px);
        }

        .messages-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .messages-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: black;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .messages-subtitle {
            font-size: 1.1rem;

            color: black;
        }

        .messages-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 1.5rem;
            height: calc(100% - 120px);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .conversations-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .conversations-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .conversations-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .conversation-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .conversation-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            border-left: 4px solid #667eea;
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
            flex-shrink: 0;
        }

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
            margin-bottom: 0.2rem;
        }

        .conversation-role {
            font-size: 0.75rem;
            color: #667eea;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            font-weight: 500;
        }

        .conversation-preview {
            color: #6c757d;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .conversation-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.3rem;
        }

        .conversation-time {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .unread-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        .chat-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
        }

        .chat-info h3 {
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
        }

        .chat-role {
            color: #667eea;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
        }

        .message-item {
            margin-bottom: 0.5rem;
            display: flex;
            animation: slideInMessage 0.3s ease-out;
        }

        .message-item.sent {
            justify-content: flex-end;
        }

        .message-item.received {
            justify-content: flex-start;
        }

        .message-bubble {
            max-width: 70%;
            padding: 0.8rem 1rem;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.4;
            word-wrap: break-word;
            position: relative;
        }

        .message-bubble.sent {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-bubble.received {
            background: white;
            color: #2c3e50;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.3rem;
            text-align: right;
        }

        .message-time.received {
            text-align: left;
        }

        .chat-input-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            background: white;
        }

        .chat-input-form {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .chat-input {
            flex: 1;
            min-height: 45px;
            max-height: 120px;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            resize: none;
            outline: none;
            transition: all 0.3s ease;
        }

        .chat-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .send-button {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .send-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
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

        @keyframes slideInMessage {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .messages-container {
                padding: 1rem;
            }

            .messages-title {
                font-size: 2rem;
            }

            .messages-layout {
                grid-template-columns: 1fr;
                height: auto;
            }

            .conversations-panel {
                order: 2;
                max-height: 300px;
            }

            .chat-panel {
                order: 1;
                height: 60vh;
            }

            .message-bubble {
                max-width: 85%;
            }
        }

        /* Scrollbar styling */
        .conversations-list::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .conversations-list::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 3px;
        }

        .conversations-list::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 3px;
        }
    </style>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Messagerie</h1>
      <ol class="breadcrumb">
        <li><a href="profil.php"><i class="fa fa-dashboard"></i> Accueil</a></li>
        <li class="active">Messages</li>
      </ol>
    </section>

    <section class="content">

      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Erreur!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Succès!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>

      <!-- Début de ton interface de messagerie -->
      <div class="messages-container">
        <div class="messages-header">
            <h1 class="messages-title">Messages</h1>
            <p class="messages-subtitle">
                Communiquez avec vos clients
            </p>
        </div>

        <div class="messages-layout">
          <!-- Conversations Panel -->
          <div class="conversations-panel">
            <div class="conversations-header">
              <h3><i class="fas fa-comments"></i> Conversations (<?= count($conversations) ?>)</h3>
            </div>
            <div class="conversations-list">
              <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv): ?>
                  <div class="conversation-item" 
                       data-interlocuteur="<?= $conv['idInterlocuteur'] ?>"
                       data-nom="<?= htmlspecialchars($conv['nom_interlocuteur']) ?>"
                       data-photo="<?php 
                         $photo = $conv['photo_interlocuteur'] ?? 'profile-placeholder.jpg';
                         echo htmlspecialchars(
                           !empty($conv['photo_interlocuteur']) && $conv['photo_interlocuteur'] !== 'profile-placeholder.jpg' 
                               ? '../images/' . $photo
                               : 'images/' . $photo
                         );
                       ?>"
                       data-role="<?= htmlspecialchars($conv['role_interlocuteur']) ?>">

                    <img src="<?php 
                      $photo = $conv['photo_interlocuteur'] ?? 'profile-placeholder.jpg';
                      echo !empty($conv['photo_interlocuteur']) && $conv['photo_interlocuteur'] !== 'profile-placeholder.jpg' 
                          ? '../images/' . htmlspecialchars($photo)
                          : 'images/' . htmlspecialchars($photo);
                    ?>" 
                    alt="<?= htmlspecialchars($conv['nom_interlocuteur']) ?>" 
                    class="conversation-avatar">

                    <div class="conversation-info">
                      <div class="conversation-name"><?= htmlspecialchars($conv['nom_interlocuteur']) ?></div>
                      <div class="conversation-role"><?= htmlspecialchars($conv['role_interlocuteur']) ?></div>
                      <div class="conversation-preview">
                        <?= htmlspecialchars(substr($conv['dernier_message'], 0, 50)) ?>...
                      </div>
                    </div>

                    <div class="conversation-meta">
                      <div class="conversation-time"><?= tempsEcoule($conv['derniere_activite']) ?></div>
                      <?php if ($conv['messages_non_lus'] > 0): ?>
                        <div class="unread-badge"><?= $conv['messages_non_lus'] ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state">
                  <i class="fas fa-inbox"></i>
                  <h3>Aucune conversation</h3>
                  <p>Commencez une conversation en contactant <?= $roleUtilisateur === 'Client' ? 'un artisan' : 'vos clients' ?>.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Chat Panel -->
          <div class="chat-panel">
            <div class="empty-state" id="empty-chat">
              <i class="fas fa-comments"></i>
              <h3>Sélectionnez une conversation</h3>
              <p>Choisissez une conversation dans la liste pour commencer à discuter.</p>
            </div>

            <div class="chat-header" id="chat-header" style="display: none;">
              <img src="" alt="" class="chat-avatar" id="chat-avatar">
              <div class="chat-info">
                <h3 id="chat-name"></h3>
                <div class="chat-role" id="chat-role"></div>
              </div>
            </div>

            <div class="chat-messages" id="chat-messages" style="display: none;">
              <!-- Messages AJAX here -->
            </div>

            <div class="chat-input-container" id="chat-input-container" style="display: none;">
              <form class="chat-input-form" id="chat-form">
                <textarea class="chat-input" id="message-input" placeholder="Tapez votre message..." rows="1"></textarea>
                <button type="submit" class="send-button" id="send-button" disabled>
                  <i class="fas fa-paper-plane"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Fin de ton interface de messagerie -->

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

    <script>
        let currentInterlocuteur = null;
        let messagesPollInterval = null;

        // Éléments du DOM
        const conversationItems = document.querySelectorAll('.conversation-item');
        const emptyChat = document.getElementById('empty-chat');
        const chatHeader = document.getElementById('chat-header');
        const chatMessages = document.getElementById('chat-messages');
        const chatInputContainer = document.getElementById('chat-input-container');
        const messageInput = document.getElementById('message-input');
        const chatForm = document.getElementById('chat-form');
        const sendButton = document.getElementById('send-button');

        // Sélection d'une conversation
        conversationItems.forEach(item => {
            item.addEventListener('click', function() {
                // Retirer la sélection précédente
                conversationItems.forEach(conv => conv.classList.remove('active'));
                
                // Sélectionner la nouvelle conversation
                this.classList.add('active');
                
                currentInterlocuteur = parseInt(this.dataset.interlocuteur);
                
                // Mettre à jour l'en-tête du chat
                document.getElementById('chat-avatar').src = this.dataset.photo;
                document.getElementById('chat-name').textContent = this.dataset.nom;
                document.getElementById('chat-role').textContent = this.dataset.role;
                
                // Afficher le chat
                emptyChat.style.display = 'none';
                chatHeader.style.display = 'flex';
                chatMessages.style.display = 'block';
                chatInputContainer.style.display = 'block';
                
                // Charger les messages
                loadMessages();
                
                // Démarrer le polling pour les nouveaux messages
                startMessagesPolling();
                
                // Focus sur l'input
                messageInput.focus();
                
                // Masquer le badge non lu
                const badge = this.querySelector('.unread-badge');
                if (badge) {
                    badge.style.display = 'none';
                }
            });
        });

        // Charger les messages d'une conversation
        function loadMessages() {
            if (!currentInterlocuteur) return;
            
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_messages&id_interlocuteur=${currentInterlocuteur}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }

        // Afficher les messages
        function displayMessages(messages) {
            const container = document.getElementById('chat-messages');
            container.innerHTML = '';
            
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message-item ${message.idEmetteur == <?= $idUtilisateur ?> ? 'sent' : 'received'}`;
                
                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = `message-bubble ${message.idEmetteur == <?= $idUtilisateur ?> ? 'sent' : 'received'}`;
                
                // Formater le contenu du message
                const contenu = message.contenu.replace(/\n/g, '<br>');
                bubbleDiv.innerHTML = contenu;
                
                const timeDiv = document.createElement('div');
                timeDiv.className = `message-time ${message.idEmetteur == <?= $idUtilisateur ?> ? 'sent' : 'received'}`;
                timeDiv.textContent = formatTime(message.dateEnvoi);
                
                bubbleDiv.appendChild(timeDiv);
                messageDiv.appendChild(bubbleDiv);
                container.appendChild(messageDiv);
            });
            
            // Scroll vers le bas
            container.scrollTop = container.scrollHeight;
        }

        // Envoyer un message
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const contenu = messageInput.value.trim();
            if (!contenu || !currentInterlocuteur) return;
            
            sendButton.disabled = true;
            
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&id_recepteur=${currentInterlocuteur}&contenu=${encodeURIComponent(contenu)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    autoResize(messageInput);
                    loadMessages();
                } else {
                    alert('Erreur lors de l\'envoi du message');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'envoi du message');
            })
            .finally(() => {
                sendButton.disabled = false;
                messageInput.focus();
            });
        });

        // Auto-resize du textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        messageInput.addEventListener('input', function() {
            autoResize(this);
            sendButton.disabled = !this.value.trim();
        });

        // Envoyer avec Ctrl+Entrée
        messageInput.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                if (this.value.trim()) {
                    chatForm.dispatchEvent(new Event('submit'));
                }
            }
        });

        // Polling pour nouveaux messages
        function startMessagesPolling() {
            if (messagesPollInterval) {
                clearInterval(messagesPollInterval);
            }
            
            messagesPollInterval = setInterval(() => {
                if (currentInterlocuteur) {
                    loadMessages();
                }
            }, 3000);
        }

        // Arrêter le polling
        function stopMessagesPolling() {
            if (messagesPollInterval) {
                clearInterval(messagesPollInterval);
                messagesPollInterval = null;
            }
        }

        // Formater l'heure
        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) {
                return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } else if (diffDays === 1) {
                return 'Hier ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } else if (diffDays < 7) {
                const jours = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                return jours[date.getDay()] + ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }) + ' ' + 
                       date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            }
        }

        // Fonction pour afficher des notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;

            // Styles pour les notifications
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    .notification {
                        position: fixed;
                        top: 100px;
                        right: 20px;
                        padding: 1rem 1.5rem;
                        border-radius: 12px;
                        color: white;
                        font-weight: 500;
                        z-index: 10000;
                        transform: translateX(400px);
                        transition: transform 0.3s ease;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }
                    .notification.show { transform: translateX(0); }
                    .notification-success { 
                        background: linear-gradient(135deg, #27ae60, #2ecc71); 
                    }
                    .notification-info { 
                        background: linear-gradient(135deg, #3498db, #2980b9); 
                    }
                `;
                document.head.appendChild(style);
            }

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }

        // Gestion des erreurs réseau
        window.addEventListener('offline', function() {
            showNotification('Connexion perdue', 'info');
            stopMessagesPolling();
        });

        window.addEventListener('online', function() {
            showNotification('Connexion rétablie', 'success');
            if (currentInterlocuteur) {
                startMessagesPolling();
                loadMessages();
            }
        });

        // Nettoyer en quittant la page
        window.addEventListener('beforeunload', function() {
            stopMessagesPolling();
        });

        // Gestion de la visibilité de la page pour économiser les ressources
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopMessagesPolling();
            } else if (currentInterlocuteur) {
                startMessagesPolling();
                loadMessages();
            }
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📱 Messagerie Artisano initialisée');
            console.log('👤 Utilisateur:', '<?= $_SESSION['prenomUtilisateur'] ?? 'Inconnu' ?> <?= $_SESSION['nomUtilisateur'] ?? '' ?>');
            console.log('🔖 Rôle:', '<?= $roleUtilisateur ?>');
            console.log('💬 Conversations trouvées:', <?= count($conversations) ?>);
            
            // Auto-sélectionner la première conversation si elle existe
            const firstConversation = document.querySelector('.conversation-item');
            if (firstConversation) {
                setTimeout(() => {
                    firstConversation.click();
                }, 500);
                
                showNotification(`${<?= count($conversations) ?>} conversation(s) disponible(s)`, 'info');
            } else {
                if ('<?= $roleUtilisateur ?>' === 'Client') {
                    showNotification('Contactez un artisan pour commencer une conversation !', 'info');
                } else {
                    showNotification('Les clients peuvent vous contacter via votre profil.', 'info');
                }
            }
            
            // Raccourcis clavier
            console.log('⌨️ Raccourcis disponibles:');
            console.log('  • Ctrl/Cmd + Entrée : Envoyer le message');
            console.log('  • Échap : Fermer la conversation');
        });

        // Raccourcis clavier globaux
        document.addEventListener('keydown', function(e) {
            // Échap pour fermer la conversation
            if (e.key === 'Escape' && currentInterlocuteur) {
                conversationItems.forEach(conv => conv.classList.remove('active'));
                currentInterlocuteur = null;
                
                emptyChat.style.display = 'flex';
                chatHeader.style.display = 'none';
                chatMessages.style.display = 'none';
                chatInputContainer.style.display = 'none';
                
                stopMessagesPolling();
                showNotification('Conversation fermée', 'info');
            }
        });

        // Animation d'apparition des conversations
        function animateConversations() {
            const conversations = document.querySelectorAll('.conversation-item');
            conversations.forEach((conv, index) => {
                conv.style.opacity = '0';
                conv.style.transform = 'translateY(20px)';
                conv.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    conv.style.opacity = '1';
                    conv.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        // Lancer l'animation après le chargement
        setTimeout(animateConversations, 300);

        // Fonction pour rafraîchir la liste des conversations
        function refreshConversations() {
            location.reload();
        }

        // Bouton de rafraîchissement (optionnel)
        const refreshBtn = document.createElement('button');
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
        refreshBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        `;
        refreshBtn.title = 'Actualiser les conversations';
        refreshBtn.onclick = refreshConversations;
        
        refreshBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(180deg)';
        });
        
        refreshBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
        
        document.body.appendChild(refreshBtn);

        // Masquer le bouton de rafraîchissement sur mobile
        if (window.innerWidth <= 768) {
            refreshBtn.style.display = 'none';
        }
    </script>
</body>
</html>