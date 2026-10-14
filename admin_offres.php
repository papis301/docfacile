<?php require 'config.php';
session_start();
if (empty($_SESSION['admin'])) { header('Location: admin.php'); exit; }
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf']; $err = $info = '';
$dir = __DIR__.'/uploads/offres/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) { http_response_code(400); exit('Requête invalide'); }
    if (isset($_POST['add'])) {
        $type  = ($_POST['type'] ?? '') === 'stage' ? 'stage' : 'emploi';
        $titre = mb_substr(trim($_POST['titre'] ?? ''), 0, 120);
        $lien  = trim($_POST['lien'] ?? '');
        $exp   = trim($_POST['expire_le'] ?? '');
        $f     = $_FILES['image'] ?? null;
        $ext   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($lien !== '' && (strlen($lien) > 500 || !preg_match('~^https?://~i', $lien) || !filter_var($lien, FILTER_VALIDATE_URL))) $err = 'Lien invalide : il doit commencer par http:// ou https://.';
        elseif ($exp !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $exp)) $err = 'Date limite invalide.';
        elseif (!$f || $f['error'] !== UPLOAD_ERR_OK) $err = 'Choisissez une image (2 Mo maximum).';
        elseif ($f['size'] > 2 * 1048576) $err = 'Image trop lourde (2 Mo maximum).';
        else {
            $mime = @getimagesize($f['tmp_name'])['mime'] ?? '';
            if (!isset($ext[$mime])) $err = 'Formats acceptés : JPG, PNG ou WebP.';
            else {
                $name = bin2hex(random_bytes(12)).'.'.$ext[$mime];
                if (move_uploaded_file($f['tmp_name'], $dir.$name)) {
                    db()->prepare('INSERT INTO offres (titre,type,image,lien,expire_le) VALUES (?,?,?,?,?)')
                        ->execute([$titre ?: null, $type, $name, $lien ?: null, $exp ?: null]);
                    $info = 'Offre publiée.';
                } else $err = 'Impossible d\'enregistrer l\'image (droits du dossier uploads/offres ?).';
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['delete'];
        $st = db()->prepare('SELECT image FROM offres WHERE id=?'); $st->execute([$id]);
        if ($img = $st->fetchColumn()) {
            @unlink($dir.basename($img));
            db()->prepare('DELETE FROM offres WHERE id=?')->execute([$id]);
            $info = 'Offre supprimée.';
        }
    }
}
$list = db()->query('SELECT * FROM offres ORDER BY created_at DESC LIMIT 100')->fetchAll();
head('Gestion des offres'); ?>
<main class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Offres d'emploi et de stage</h1>
<a href="admin.php" class="btn btn-sm btn-outline-secondary">← Admin</a></div>
<?php if ($info): ?><div class="alert alert-success py-2"><?= e($info) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<div class="card mb-4"><div class="card-body"><h2 class="h6">Publier une offre</h2>
<form method="post" enctype="multipart/form-data" class="row g-2"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<div class="col-12 col-md-6"><label class="form-label small mb-1">Image de l'offre *</label>
<input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control" required></div>
<div class="col-6 col-md-3"><label class="form-label small mb-1">Type</label>
<select name="type" class="form-select"><option value="emploi">Emploi</option><option value="stage">Stage</option></select></div>
<div class="col-6 col-md-3"><label class="form-label small mb-1">Date limite (facultatif)</label><input type="date" name="expire_le" class="form-control"></div>
<div class="col-12 col-md-6"><label class="form-label small mb-1">Titre (facultatif)</label><input name="titre" maxlength="120" class="form-control" placeholder="Ex. Comptable, Dakar"></div>
<div class="col-12 col-md-6"><label class="form-label small mb-1">Lien de l'offre (facultatif)</label><input type="url" name="lien" maxlength="500" class="form-control" placeholder="https://..."></div>
<div class="col-12"><button name="add" value="1" class="btn btn-primary">Publier</button></div></form></div></div>
<h2 class="h6"><?= count($list) ?> offre(s)</h2>
<div class="row g-3"><?php foreach ($list as $o): ?>
<div class="col-6 col-md-3"><div class="card h-100"><img src="uploads/offres/<?= e($o['image']) ?>" class="card-img-top" alt="" loading="lazy">
<div class="card-body p-2 small"><span class="badge <?= $o['type'] === 'stage' ? 'bg-info text-dark' : 'bg-primary' ?>"><?= e($o['type']) ?></span>
<div class="fw-bold"><?= e($o['titre'] ?? '') ?></div>
<div class="text-muted"><?= $o['lien'] ? 'Avec lien' : 'Sans lien' ?><?= $o['expire_le'] ? ' · jusqu\'au '.e($o['expire_le']) : '' ?></div>
<form method="post" onsubmit="return confirm('Supprimer cette offre ?')" class="mt-1"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<button name="delete" value="<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline-danger w-100">Supprimer</button></form></div></div></div>
<?php endforeach; ?></div></main>
<?php foot();