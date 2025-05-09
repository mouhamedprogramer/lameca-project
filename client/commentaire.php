<?php


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Récupérer les données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Vérifier si les données sont valides
    if (empty($nom) || empty($prenom) || empty($email) || empty($message)) {
        echo "Veuillez remplir tous les champs correctement.";
        exit;
    }

    // Connexion à la base de données
    require_once 'includes/conn.php';

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Préparer la requête SQL pour insérer les données dans la table commentaires
    $stmt = $conn->prepare("INSERT INTO commentaires (nom, prenom, email, message, note, date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $nom, $prenom, $email, $message, $note);

    // Exécuter la requête
    if ($stmt->execute()) {
        echo "Votre commentaire a été ajouté avec succès !";
    } else {
        echo "Erreur lors de l'ajout du commentaire : " . $conn->error;
    }

    // Fermer la connexion
    $stmt->close();
    $conn->close();
}
?>
