<?php
    session_start();
    session_destroy();      // supprime la session donc l'utilisateur est déconnecté
    header("Location: ./login.php"); exit();
?>
