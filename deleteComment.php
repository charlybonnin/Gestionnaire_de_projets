<?php
    session_start();
    include("./include/tasks.php");
    include("./include/comments.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    if (isset($_GET['id'])) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $comment = selectCommentById($pdo, $_GET['id']);

            // l'auteur ou un admin peut supprimer le commentaire
            if ($comment && canDeleteComment($comment)) {
                deleteComment($pdo, $comment['id']);
                header("Location: ./task.php?id=".$comment['task_id']); exit();
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }
    header("Location: ./index.php"); exit();
?>
