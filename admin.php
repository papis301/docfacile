<?php require 'config.php';
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
$err = $info = ''; $new = [];

if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) { http_response_code(400); exit('Requête invalide'); }
    if (isset($_POST['password'])) {
        usleep(500000); // freine les essais répétés
        if (ADMIN_PASSWORD_HASH === '') $err = 'Aucun mot de passe configuré : voir ADMIN_PASSWORD_HASH dans config.php.';
        elseif (password_verify((string)$_POST['password'], ADMIN_PASSWORD_HASH)) { session_regenerate_id(true); $_SESSION['admin'] = 1; }
        else $err = 'Mot de passe incorrect.';
    } elseif (!empty($_SESSION['admin'])) {
        if (isset($_POST['generate'])) {
            $n = max(1, min(100, (int)$_POST['count']));
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sans O/0/I/1
            $ins = db()->prepare('INSERT IGNORE INTO codes (code) VALUES (?)');
            while (count($new) < $n) {
                $c = 'CV-';
                for ($i = 0; $i < 6; $i++) $c .= $chars[random_int(0, strlen($chars) - 1)];
                $ins->execute([$c]);
                if ($ins->rowCount() === 1) $new[] = $c;
            }
            $info = count($new).' code(s) généré(s).';
        } elseif (isset($_POST['delete'])) {
            db()->prepare('DELETE FROM codes WHERE code=? AND used=0')->execute([$_POST['delete']]);
            $info = 'Code supprimé.';
        } elseif (isset($_POST['done'])) {
            db()->prepare('UPDATE demandes SET statut=1 WHERE id=?')->execute([(int)$_POST['done']]);
            $info = 'Demande marquée comme traitée.';
        }
    }
}

head('Administration');
if (empty($_SESSION['admin'])): ?>
<main class="container mt-5" style="max-width:380px"><div class="card shadow-sm"><div class="card-body">
<h1 class="h5">Espace administrateur</h1>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="password" name="password" class="form-control mb-2" placeholder="Mot de passe" required autofocus>
<button class="btn btn-dark w-100">Connexion</button></form></div></div></main>
<?php foot(); exit; endif;

$s = db()->query('SELECT COUNT(*) total, SUM(used=0) libres, SUM(used=1) utilises FROM codes')->fetch();
$dl = db()->query('SELECT COALESCE(SUM(downloads),0) FROM templates')->fetchColumn();
$top = db()->query('SELECT name, downloads FROM templates ORDER BY downloads DESC LIMIT 5')->fetchAll();
$codes = db()->query('SELECT * FROM codes ORDER BY created_at DESC, code LIMIT 100')->fetchAll();
?>
<main class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h1 class="h4 mb-0">Administration</h1><div><a href="admin_offres.php" class="btn btn-sm btn-primary me-1">Offres</a><a href="?logout=1" class="btn btn-sm btn-outline-secondary">Déconnexion</a></div></div>
<?php if ($info): ?><div class="alert alert-success py-2"><?= e($info) ?></div><?php endif; ?>

<div class="row g-2 mb-3 text-center">
<div class="col-6 col-md-3"><div class="card p-2"><div class="h4 mb-0"><?= (int)$s['libres'] ?></div><small>Codes disponibles</small></div></div>
<div class="col-6 col-md-3"><div class="card p-2"><div class="h4 mb-0"><?= (int)$s['utilises'] ?></div><small>Codes utilisés</small></div></div>
<div class="col-6 col-md-3"><div class="card p-2"><div class="h4 mb-0"><?= (int)$s['utilises'] * 500 ?></div><small>FCFA encaissés (est.)</small></div></div>
<div class="col-6 col-md-3"><div class="card p-2"><div class="h4 mb-0"><?= (int)$dl ?></div><small>Téléchargements</small></div></div>
</div>

<?php // Statistiques des CV internationaux (modèles 16 à 19)
$intlFiles = ['modele-16.docx', 'modele-17.docx', 'modele-18.docx', 'modele-19.docx'];
$intlRows = []; $intlErr = false;
try {
    $qi = db()->prepare('SELECT t.name, t.downloads,
        (SELECT COUNT(*) FROM telechargements l WHERE l.template_id=t.id AND l.created_at > NOW() - INTERVAL 7 DAY) AS d7,
        (SELECT COUNT(*) FROM telechargements l WHERE l.template_id=t.id AND l.created_at > NOW() - INTERVAL 30 DAY) AS d30
        FROM templates t WHERE t.file IN ('.implode(',', array_fill(0, count($intlFiles), '?')).') ORDER BY t.downloads DESC');
    $qi->execute($intlFiles); $intlRows = $qi->fetchAll();
} catch (Throwable $e) { $intlErr = true; }
$sum = fn($k) => array_sum(array_column($intlRows, $k));
?>
<div class="card mb-3"><div class="card-body"><h2 class="h6">CV internationaux téléchargés</h2>
<?php if ($intlErr): ?><div class="alert alert-warning py-2 mb-0 small">Table <code>telechargements</code> manquante : exécutez la requête indiquée dans schema.sql.</div>
<?php else: ?><div class="table-responsive"><table class="table table-sm align-middle mb-1">
<thead><tr><th>Modèle</th><th class="text-end">7 jours</th><th class="text-end">30 jours</th><th class="text-end">Total</th></tr></thead><tbody>
<?php foreach ($intlRows as $r): ?><tr><td><?= e($r['name']) ?></td><td class="text-end"><?= (int)$r['d7'] ?></td><td class="text-end"><?= (int)$r['d30'] ?></td><td class="text-end"><?= (int)$r['downloads'] ?></td></tr><?php endforeach; ?>
<tr class="fw-bold"><td>Total</td><td class="text-end"><?= $sum('d7') ?></td><td class="text-end"><?= $sum('d30') ?></td><td class="text-end"><?= $sum('downloads') ?></td></tr></tbody></table></div>
<div class="small text-muted"><?= $dl > 0 ? round($sum('downloads') * 100 / $dl).' % de tous les téléchargements. ' : '' ?>Les colonnes 7 et 30 jours comptent à partir de l'activation du suivi ; le total inclut l'historique.</div>
<?php endif; ?></div></div>

<div class="card mb-3"><div class="card-body">
<h2 class="h6">Générer des codes</h2>
<form method="post" class="row g-2"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<div class="col-6 col-md-3"><input type="number" name="count" class="form-control" value="10" min="1" max="100"></div>
<div class="col-6 col-md-3"><button name="generate" value="1" class="btn btn-primary w-100">Générer</button></div></form>
<?php if ($new): ?>
<textarea id="newcodes" class="form-control mt-3" rows="<?= min(10, count($new)) ?>" readonly><?= e(implode("\n", $new)) ?></textarea>
<button class="btn btn-sm btn-success mt-2" onclick="navigator.clipboard.writeText(document.getElementById('newcodes').value);this.textContent='Copié ✔'">Copier tout</button>
<?php endif; ?></div></div>

<div class="row g-3"><div class="col-lg-8">
<div class="card"><div class="card-body"><h2 class="h6">100 derniers codes</h2>
<div class="table-responsive"><table class="table table-sm align-middle mb-0">
<thead><tr><th>Code</th><th>Statut</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($codes as $c): ?><tr>
<td class="font-monospace"><?= e($c['code']) ?></td>
<td><?= $c['used'] ? '<span class="badge bg-secondary">Utilisé</span>' : '<span class="badge bg-success">Libre</span>' ?></td>
<td class="small"><?= e($c['used'] ? $c['used_at'] : $c['created_at']) ?></td>
<td><?php if (!$c['used']): ?><form method="post" onsubmit="return confirm('Supprimer ce code ?')">
<input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="delete" value="<?= e($c['code']) ?>">
<button class="btn btn-sm btn-outline-danger">✕</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h6">Modèles les plus téléchargés</h2>
<?php foreach ($top as $t): ?><div class="d-flex justify-content-between small border-bottom py-1">
<span><?= e($t['name']) ?></span><strong><?= (int)$t['downloads'] ?></strong></div><?php endforeach; ?>
</div></div></div></div>
<div class="card mt-3"><div class="card-body"><h2 class="h6">Demandes de services</h2>
<?php foreach (db()->query('SELECT * FROM demandes ORDER BY statut, created_at DESC LIMIT 30')->fetchAll() as $d): ?>
<div class="border-bottom py-2 small"><div class="d-flex justify-content-between">
<strong><?= e($d['nom']) ?> · <?= e($d['service']) ?></strong>
<?php if (!$d['statut']): ?><form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><button name="done" value="<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-success">Traité</button></form><?php else: ?><span class="badge bg-secondary">Traité</span><?php endif; ?></div>
<div><?= e($d['contact']) ?> · <span class="text-muted"><?= e($d['created_at']) ?></span></div><div><?= nl2br(e($d['message'])) ?></div></div>
<?php endforeach; ?></div></div>
</main>
<?php foot();