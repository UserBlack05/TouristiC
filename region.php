<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM regions ");
$regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Liste des régions</h1>
<table>
    <tr>
        <th>ID</th>
        <th>Nom de la région</th>
        <th>Action</th>
    </tr>
    <?php foreach($regions as $region): ?>
    <tr>
        <td><?php echo $region['idRegion']; ?></td>
        <td><?php echo htmlspecialchars($region['nomRegion']); ?></td>
        <td><a href="departements.php?region_id=<?php echo $region['idRegion']; ?>" class="btn">Voir départements</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>