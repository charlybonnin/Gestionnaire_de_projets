<?php
include('header.php');

// si sendLogin.php nous renvoie ici avec une erreur
if (isset($_GET['erreur'])) {
    echo '<p>Login ou mot de passe incorrect</p>';
}
// si on vient de créer son compte
if (isset($_GET['inscrit'])) {
    echo '<p>Compte créé, vous pouvez vous connecter</p>';
}
?>

<form action="sendLogin.php" method="post">
    <div class="form">
        <label for="login">login</label>
        <input type="text" name="login" id="login" required />
    </div>
    <div class="form">
        <label for="password">mot de passe</label>
        <input type="password" name="password" id="password" required />
    </div>
    <input type="submit" value="Se connecter" />
</form>
<a href="./register.php">Créer un compte</a>

<?php
include('footer.php');
?>
