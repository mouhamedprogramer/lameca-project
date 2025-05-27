<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$id = $_POST['id'];
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

			$sql = "UPDATE faq SET question = '$question', reponse = '$reponse' WHERE idFaq = '$id'";
			if($conn->query($sql)){
				$_SESSION['success'] = 'FAQ modifiée avec succès';
			}
			else{
				$_SESSION['error'] = 'Erreur lors de la modification de la FAQ: ' . $conn->error;
			}
		}
	}
	else{
		$_SESSION['error'] = 'Sélectionnez d\'abord une FAQ à modifier';
	}

	header('location: faq.php');
?>