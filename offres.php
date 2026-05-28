<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$site_id = isset($_GET['site_id']) ? $_GET['site_id'] : 0;

if($site_id) {
    $stmt = $pdo->prepare("
        SELECT o.*, st.nomSiteTour
        FROM offres o
        JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
        WHERE o.numSiteTour = ?
    ");
    $stmt->execute([$site_id]);
    $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT nomSiteTour FROM sites_touristiques WHERE numSiteTour = ?");
    $stmt->execute([$site_id]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("
        SELECT o.*, st.nomSiteTour
        FROM offres o
        JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
    ");
    $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1>Offres <?php echo isset($site) ? 'pour ' . htmlspecialchars($site['nomSiteTour']) : ''; ?></h1>
<table>
    <tr>
        
        <th>Description</th>
        <th>Prix</th>
        <th>Site touristique</th>
        <?php if(isset($_SESSION['user_id'])): ?>
        <th>Action</th>
        <?php endif; ?>
    </tr>
    <?php foreach($offres as $offre): ?>
    <tr>
        
        <td><?php echo htmlspecialchars($offre['descOffre']); ?></td>
        <td><?php echo number_format($offre['prixOffre'], 0, ',', ' '); ?> FCFA</td>
        <td><?php echo htmlspecialchars($offre['nomSiteTour']); ?></td>
        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 1): ?>
        <td>
            <form method="POST" action="souscrire_offre.php">
                <input type="hidden" name="numOffre" value="<?php echo $offre['numOffre']; ?>">
                <button type="submit" class="btn">Souscrire</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>