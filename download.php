<?php require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM templates WHERE id=?'); $st->execute([$id]);
$t = $st->fetch();
if (!$t) { http_response_code(404); exit('Modèle introuvable'); }
if (FREE_MODE) $t['is_premium'] = 0;
$path = __DIR__.'/storage/templates/'.basename($t['file']);
$error = '';

function send($t, $path) {
    db()->prepare('UPDATE templates SET downloads=downloads+1 WHERE id=?')->execute([$t['id']]);
    try { db()->prepare('INSERT INTO telechargements (template_id) VALUES (?)')->execute([$t['id']]); } catch (Throwable $e) {} // journal pour les statistiques
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="CV-'.preg_replace('/\W+/','-',$t['name']).'.docx"');
    header('Content-Length: '.filesize($path));
    readfile($path); exit;
}

if (!$t['is_premium']) send($t, $path);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $u = db()->prepare('UPDATE codes SET used=1, used_at=NOW() WHERE code=? AND used=0');
    $u->execute([$code]);
    if ($u->rowCount() === 1) send($t, $path);
    $error = 'Code invalide ou déjà utilisé.';
}
$msg = urlencode("Bonjour, je veux un code pour le modèle « {$t['name']} » (".PRICE.").");
head('Modèle premium'); ?>
<main class="container mt-4" style="max-width:480px">
<div class="card shadow-sm"><div class="card-body">
<h1 class="h5">Modèle premium : <?= e($t['name']) ?></h1>
<p class="small text-muted">Payez <?= PRICE ?> par Wave ou Orange Money, recevez votre code sur WhatsApp, puis saisissez-le ci-dessous.</p>
<a class="btn btn-success w-100 mb-3" href="https://wa.me/<?= WHATSAPP ?>?text=<?= $msg ?>">💬 Obtenir mon code sur WhatsApp</a>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input name="code" class="form-control mb-2" placeholder="CV-XXXXXX" required>
<button class="btn btn-primary w-100">Télécharger</button></form>
<a href="index.php" class="d-block text-center mt-3 small">← Retour aux modèles</a>
</div></div></main>
<?php foot();