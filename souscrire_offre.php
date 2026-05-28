<?php
// souscrire_offres.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=1');
    exit;
}

$matPers = $_SESSION['user_id'];
$success = '';
$error = '';

// Traitement de la souscription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['numOffre'])) {
    $numOffre = $_POST['numOffre'];
    
    // Vérifier si l'offre existe
    $stmt = $pdo->prepare("SELECT * FROM offres WHERE numOffre = ?");
    $stmt->execute([$numOffre]);
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($offre) {
        // Vérifier si l'utilisateur n'a pas déjà souscrit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM souscrire WHERE matPers = ? AND numOffre = ?");
        $stmt->execute([$matPers, $numOffre]);
        
        if ($stmt->fetchColumn() == 0) {
            // Souscrire à l'offre
            $stmt = $pdo->prepare("INSERT INTO souscrire (matPers, numOffre) VALUES (?, ?)");
            if ($stmt->execute([$matPers, $numOffre])) {
                $success = "Vous avez souscrit à l'offre avec succès !";
            } else {
                $error = "Erreur lors de la souscription";
            }
        } else {
            $error = "Vous avez déjà souscrit à cette offre";
        }
    } else {
        $error = "Offre non trouvée";
    }
}

// Récupérer toutes les offres disponibles
$offres = $pdo->query("
    SELECT o.*, st.nomSiteTour, st.descSiteTour, v.nomVille, d.nomDept, r.nomRegion
    FROM offres o
    JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
    JOIN villes v ON st.numVille = v.numVille
    JOIN departements d ON v.idDept = d.idDept
    JOIN regions r ON d.idRegion = r.idRegion
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les souscriptions de l'utilisateur
$stmt = $pdo->prepare("
    SELECT numOffre FROM souscrire WHERE matPers = ?
");
$stmt->execute([$matPers]);
$souscriptions = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<h1>Souscrire à une offre</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="souscription_offre.php" class="btn">Voir mes souscriptions</a>
</div>

<?php if (empty($offres)): ?>
    <div class="alert alert-info">Aucune offre disponible pour le moment.</div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach ($offres as $offre): 
            $dejaSouscrit = in_array($offre['numOffre'], $souscriptions);
        ?>
        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; <?php echo $dejaSouscrit ? 'background-color: #e8f5e8;' : ''; ?>">
            <h3><?php echo htmlspecialchars($offre['nomSiteTour']); ?></h3>
            <p><strong>Localisation :</strong> <?php echo htmlspecialchars($offre['nomVille'] . ', ' . $offre['nomDept'] . ', ' . $offre['nomRegion']); ?></p>
            <p><strong>Description du site :</strong><br><?php echo htmlspecialchars(substr($offre['descSiteTour'], 0, 150)) . '...'; ?></p>
            <p><strong>Offre :</strong> <?php echo htmlspecialchars($offre['descOffre']); ?></p>
            <p><strong>Prix :</strong> <?php echo formatPrice($offre['prixOffre']); ?></p>
            
            <?php if ($dejaSouscrit): ?>
                <p style="color: green; font-weight: bold;">Merci d'avoir souscrit à cette offre</p>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="numOffre" value="<?php echo $offre['numOffre']; ?>">
                    <button type="submit" class="btn" style="background-color: #28a745;">Souscrire à cette offre</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>