<?php
include('header.php');

// si sendRegister.php nous renvoie ici avec une erreur
if (isset($_GET['erreur'])) {
    echo '<p>Ce login ou cet email est déjà utilisé</p>';
}
?>

<form action="sendRegister.php" method="post">
    <div class="form">
        <label for="login">login</label>
        <input type="text" name="login" id="login" maxlength="50" required />
    </div>
    <div class="form">
        <label for="email">email</label>
        <input type="email" name="email" id="email" maxlength="100" required />
    </div>
    <div class="form">
        <label for="password">mot de passe</label>
        <input type="password" name="password" id="password" required />
    </div>
    <input type="submit" value="Créer le compte" />
</form>
<a href="./login.php">Retour</a>

<?php
include('footer.php');
?>
