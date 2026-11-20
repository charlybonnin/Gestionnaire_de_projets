<?php
    session_start();
    include("./include/tasks.php");
    include("./include/comments.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    if (isset($_POST['id']) && isset($_POST['content']) && trim($_POST['content']) != '') {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $comment = selectCommentById($pdo, $_POST['id']);

            // seulement l'auteur peut modifier son commentaire
            if ($comment && canEditComment($comment)) {
                updateComment($pdo, $comment['id'], $_SESSION['id'], trim($_POST['content']));
                header("Location: ./task.php?id=".$comment['task_id']); exit();
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }
    header("Location: ./index.php"); exit();
?>
