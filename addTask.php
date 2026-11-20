<?php
include('header.php');

// si l'utilisateur est pas connecté on le renvoie sur le login
if (!isset($_SESSION['id'])) {
    header("Location: ./login.php"); exit();
}
?>

<form action="sendTask.php" method="post">
    <div class="form">
        <label for="title">titre</label>
        <input type="text" name="title" id="title" maxlength="150" required />
    </div>
    <div class="form">
        <label for="description">description</label>
        <textarea name="description" id="description"></textarea>
    </div>
    <input type="submit" value="Ajouter" />
</form>
<a href="./index.php">Retour</a>

<?php
include('footer.php');
?>
