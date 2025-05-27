<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$question = $_POST['question'];
		$reponse = $_POST['reponse'];

		// Validation des champs
		if(empty($question) || empty($reponse)){
			$_SESSION['error'] = 'Tous les champs sont obligatoires';
		}
		else{
			// Nettoyer les données
			$question = $conn->real_escape_string(trim($question));
			$reponse = $conn->real_escape_string(trim($reponse));

			$sql = "INSERT INTO faq (question, reponse) VALUES ('$question', '$reponse')";
			if($conn->query($sql)){
				$_SESSION['success'] = 'FAQ ajoutée avec succès';
			}
			else{
				$_SESSION['error'] = 'Erreur lors de l\'ajout de la FAQ: ' . $conn->error;
			}
		}
	}
	else{
		$_SESSION['error'] = 'Remplissez d\'abord le formulaire';
	}

	header('location: faq.php');
?>