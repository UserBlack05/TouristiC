<?php
require_once 'config/database.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

$exclude = ['admin'];
$request = "SELECT * FROM type_personnes WHERE roleTypePers NOT IN ('admin') ";
$prepare = $pdo -> prepare($request);
$prepare -> execute();
$reponse = $prepare -> FETCH(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $tel = $_POST['tel'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    switch(trim($role)) {
        case "Client":
            $idTypePers = 1;
            break;
         case "Responsable_de_site_touristique":
            $idTypePers = 2;
            break;
         case "Responsable_d'hebergement":
            $idTypePers = 3;
            break;
    }
    
    
    // Vérifier si le login existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM personnes WHERE loginPers = ?");
    $stmt->execute([$login]);
    if($stmt->fetchColumn() > 0) {
        $error = 'Ce login est déjà utilisé';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO personnes (nomPers, prenomPers, telPers, loginPers, pswdPers, idTypePers) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if($stmt->execute([$nom, $prenom, $tel, $login, $password, $idTypePers])) {
            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = 'Erreur lors de l\'inscription';
        }
    }
}

require_once 'includes/header.php';
?>

<h1>Inscription</h1>

<?php if($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" action="">

    <div class="form-group">
        <label>Vous vous inscrivez en tant que :</label>
        <select name="role">
        <?php do{
           echo "<option value =".$reponse ['roleTypePers'].">".$reponse ['roleTypePers'] ."</option>";
        } while ($reponse = $prepare -> FETCH(PDO::FETCH_ASSOC))?>
        </select>
    </div>

    <div class="form-group">
        <label>Nom :</label>
        <input type="text" name="nom" required>
    </div>
    
    <div class="form-group">
        <label>Prénom :</label>
        <input type="text" name="prenom" required>
    </div>
    
    <div class="form-group">
        <label>Téléphone :</label>
        <input type="text" name="tel" required>
    </div>
    
    <div class="form-group">
        <label>Login :</label>
        <input type="text" name="login" required>
    </div>
    
    <div class="form-group">
        <label>Mot de passe :</label>
        <input type="password" name="password" required>
    </div>
    
    <button type="submit" class="btn">S'inscrire</button>
</form>

<p>Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>

<?php require_once 'includes/footer.php'; ?>