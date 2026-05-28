<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$region_id = isset($_GET['region_id']) ? $_GET['region_id'] : 0;

if($region_id) {
    $stmt = $pdo->prepare("
        SELECT d.*, r.nomRegion 
        FROM departements d
        JOIN regions r ON d.idRegion = r.idRegion
        WHERE d.idRegion = ?
        ORDER BY d.nomDept
    ");
    $stmt->execute([$region_id]);
    $departements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT nomRegion FROM regions WHERE idRegion = ?");
    $stmt->execute([$region_id]);
    $region = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("
        SELECT d.*, r.nomRegion 
        FROM departements d
        JOIN regions r ON d.idRegion = r.idRegion
        ORDER BY d.nomDept
    ");
    $departements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1>Départements <?php echo isset($region) ? 'de ' . htmlspecialchars($region['nomRegion']) : ''; ?></h1>
<table>
    <tr>
        <th>ID</th>
        <th>Nom du département</th>
        <th>Région</th>
        <th>Action</th>
    </tr>
    <?php foreach($departements as $dept): ?>
    <tr>
        <td><?php echo $dept['idDept']; ?></td>
        <td><?php echo htmlspecialchars($dept['nomDept']); ?></td>
        <td><?php echo htmlspecialchars($dept['nomRegion']); ?></td>
        <td><a href="villes.php?dept_id=<?php echo $dept['idDept']; ?>" class="btn">Voir villes</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>