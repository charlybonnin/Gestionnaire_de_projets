<?php
    session_start();
    include("./include/tasks.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    // on vérifie que le formulaire a bien été envoyé
    if (isset($_POST['title']) && $_POST['title'] != '') {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            addTask($pdo, $_SESSION['id'], $_POST['title'], $_POST['description']);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }
    header("Location: ./index.php"); exit();
?>
