<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<!-- Hero Section interne (sans image pleine page car déjà dans le header) -->
<div style="text-align: center; margin-bottom: 50px;">
    <h1 style="border-bottom: none; font-size: 4rem;">L'ART DU VOYAGE</h1>
    <p style="font-size: 1.3rem; color: #b8a58b; max-width: 700px; margin: 0 auto;">Des expériences exclusives dans les plus beaux endroits de Côte d'Ivoire</p>
</div>

<!-- Statistiques -->
<div class="stats-luxe">
    <div class="stats-grid">
        <?php
        $nbSites = $pdo->query("SELECT COUNT(*) FROM sites_touristiques")->fetchColumn();
        $nbOffres = $pdo->query("SELECT COUNT(*) FROM offres")->fetchColumn();
        $nbHebergements = $pdo->query("SELECT COUNT(*) FROM hebergements")->fetchColumn();
        $nbRegions = $pdo->query("SELECT COUNT(*) FROM regions")->fetchColumn();
        ?>
        <div>
            <div class="stat-number"><?php echo $nbSites; ?></div>
            <div class="stat-label">Sites d'exception</div>
        </div>
        <div>
            <div class="stat-number"><?php echo $nbOffres; ?></div>
            <div class="stat-label">Offres exclusives</div>
        </div>
        <div>
            <div class="stat-number"><?php echo $nbHebergements; ?></div>
            <div class="stat-label">Hébergements luxe</div>
        </div>
        <div>
            <div class="stat-number"><?php echo $nbRegions; ?></div>
            <div class="stat-label">Régions</div>
        </div>
    </div>
</div>

<!-- Sites Touristiques -->
<h2>🏛️ SITES D'EXCEPTION</h2>
<p>Découvrez nos sites soigneusement sélectionnés pour leur beauté et leur authenticité</p>

<div class="grid-luxe">
    <?php
    $stmt = $pdo->query("
        SELECT st.*, v.nomVille 
        FROM sites_touristiques st
        JOIN villes v ON st.numVille = v.numVille
        ORDER BY RAND()
        LIMIT 3
    ");
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($sites as $site):
    ?>
    <div class="card-luxe">
        <div style="font-size: 3rem; margin-bottom: 20px; color: #c4a35a;">🏛️</div>
        <h3><?php echo htmlspecialchars($site['nomSiteTour']); ?></h3>
        <p style="color: #c4a35a; font-size: 1rem;">📍 <?php echo htmlspecialchars($site['nomVille']); ?></p>
        <p><?php echo htmlspecialchars(substr($site['descSiteTour'], 0, 100)); ?>...</p>
        <a href="offres.php?site_id=<?php echo $site['numSiteTour']; ?>" class="btn">DÉCOUVRIR</a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Grille de destinations (inspirée de la galerie) -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 60px 0;">
    <?php
    $images = [
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800',
        'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800',
        'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800'
    ];
    $lieux = ['Assinie', 'Grand-Bassam', 'Yamoussoukro', 'Sassandra'];
    
    for($i = 0; $i < 4; $i++):
    ?>
    <div style="height: 250px; background: linear-gradient(rgba(139,115,85,0.2), rgba(139,115,85,0.2)), url('<?php echo $images[$i]; ?>'); background-size: cover; background-position: center; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(139,115,85,0.7); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; color: white; font-size: 1.5rem;" 
             onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
            <?php echo $lieux[$i]; ?>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Offres en vedette -->
<h2>✨ OFFRES EXCLUSIVES</h2>
<p>Des expériences uniques pour des moments inoubliables</p>

<table>
    <thead>
        <tr>
            <th>Site</th>
            <th>Description</th>
            <th>Prix</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->query("
            SELECT o.*, st.nomSiteTour 
            FROM offres o
            JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
            ORDER BY o.prixOffre DESC
            LIMIT 5
        ");
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($offres as $offre):
        ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($offre['nomSiteTour']); ?></strong></td>
            <td><?php echo htmlspecialchars(substr($offre['descOffre'], 0, 100)); ?>...</td>
            <td style="color: #c4a35a; font-weight: bold;"><?php echo number_format($offre['prixOffre'], 0, ',', ' '); ?> FCFA</td>
            <td>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 1): ?>
                    <a href="souscrire_offre.php?offre=<?php echo $offre['numOffre']; ?>" class="btn">RÉSERVER</a>
                <?php else: ?>
                    <a href="login.php" class="btn">CONNEXION</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Hébergements -->
<h2>🏨 HÉBERGEMENTS DE PRESTIGE</h2>
<p>Séjournez dans nos établissements sélectionnés pour leur excellence</p>

<div class="grid-luxe">
    <?php
    $stmt = $pdo->query("
        SELECT h.*, v.nomVille 
        FROM hebergements h
        JOIN villes v ON h.numVille = v.numVille
        ORDER BY RAND()
        LIMIT 3
    ");
    $hebergements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($hebergements as $heb):
    ?>
    <div class="card-luxe">
        <div style="font-size: 3rem; margin-bottom: 20px; color: #c4a35a;">🏨</div>
        <h3><?php echo htmlspecialchars($heb['nomHeb']); ?></h3>
        <p style="color: #c4a35a;"><?php echo htmlspecialchars($heb['adrHeb']); ?></p>
        <p>📍 <?php echo htmlspecialchars($heb['nomVille']); ?></p>
        <a href="sejours.php?id=<?php echo $heb['idHeb']; ?>" class="btn">VOIR LES SÉJOURS</a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Témoignage -->
<div class="card-luxe" style="max-width: 800px; margin: 60px auto; padding: 50px;">
    <p style="font-size: 1.5rem; color: #8b7355; font-style: italic; line-height: 1.8;">"Un service irréprochable, des attentions délicates à chaque étape. Élite Escapes a su rendre notre séjour en Côte d'Ivoire inoubliable."</p>
    <div style="color: #c4a35a; margin-top: 30px;">— Marie & Jean Dupont</div>
</div>

<!-- Appel à l'action pour les non-connectés -->
<?php if(!isset($_SESSION['user_id'])): ?>
<div class="card-luxe" style="text-align: center; background: #fcf9f5; margin-top: 40px;">
    <h3>Prêt pour l'aventure ?</h3>
    <p style="max-width: 600px; margin: 20px auto;">Rejoignez notre communauté de voyageurs exigeants</p>
    <div style="display: flex; gap: 20px; justify-content: center;">
        <a href="inscription.php" class="btn btn-primary">CRÉER UN COMPTE</a>
        <a href="login.php" class="btn">SE CONNECTER</a>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>