<?php
	include 'includes/session.php';

	$return = 'home.php';
	if(isset($_GET['return'])){
		$return = $_GET['return'];
	}

	if(isset($_POST['save'])){
		$title = $_POST['title'];

		$file = 'config.ini';
		$content = 'election_title = '.$title;

		file_put_contents($file, $content);

		$_SESSION['success'] = 'Le titre de l\'élection est modifié avec succés';
		
	}
	else{
		$_SESSION['error'] = "Remplissez d'abord le formulaire de configuration.";
	}

	header('location: '.$return);

?>