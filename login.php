<?php
require_once 'config/database.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM personnes WHERE loginPers = ? AND pswdPers = ?");
    $stmt->execute([$login,$password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        $_SESSION['user_id'] = $user['matPers'];
        $_SESSION['user_nom'] = $user['nomPers'] . ' ' . $user['prenomPers'];
        $_SESSION['user_type'] = $user['idTypePers'];
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Login ou mot de passe incorrect';
    }
}

require_once 'includes/header.php';
?>

<h1>Connexion</h1>

<?php if($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label>Login :</label>
        <input type="text" name="login" required>
    </div>
    
    <div class="form-group">
        <label>Mot de passe :</label>
        <input type="password" name="password" required>
    </div>
    
    <button type="submit" class="btn">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a></p>

<?php require_once 'includes/footer.php'; ?>