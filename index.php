<?php
include('header.php');

// si l'utilisateur est pas connecté on le renvoie sur le login
if (!isset($_SESSION['id'])) {
    header("Location: ./login.php"); exit();
}

echo '<p>Connecté en tant que '.$_SESSION['username'].' <a href="./logout.php">Se déconnecter</a></p>';

include('allTasks.php');


include('footer.php');
?>
