<?php
    session_start();
    include("./include/tasks.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    if (isset($_GET['id'])){
        $pdo = new PDO($dsn, $user, $pass, $options);
        deleteTask($pdo, $_GET['id'], $_SESSION['id']);
    }
    header("Location: ./index.php"); exit(); //redirige vers index pour mettre à jour direct la page
?>
