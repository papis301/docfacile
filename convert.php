<?php require 'config.php';
if (!CONVERTER_ENABLED) { http_response_code(404); exit('Page introuvable.'); }
session_start();
$tmp = __DIR__.'/storage/tmp/';
// Nettoyage : tout fichier de plus de 30 minutes est supprimé (confidentialité)
foreach (glob($tmp.'*') as $f) if (is_file($f) && filemtime($f) < time() - 1800) @unlink($f);

// --- Téléchargement du résultat ---
if (isset($_GET['get'])) {
    $tok = $_GET['get'];
    $f = $tmp.$tok.'.docx';
    if (!preg_match('/^[a-f0-9]{32}$/', $tok) || !is_file($f)) { http_response_code(404); exit('Fichier expiré. Veuillez reconvertir votre PDF.'); }
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="document-converti.docx"');
    header('Content-Length: '.filesize($f));
    readfile($f); exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$err = ''; $done = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0';
    $fi = $_FILES['pdf'] ?? null;
    $q = db()->prepare('SELECT COUNT(*) FROM conversions WHERE ip=? AND created_at > NOW() - INTERVAL 1 DAY');
    $q->execute([$ip]);
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $err = 'Session expirée, rechargez la page.';
    elseif ((int)$q->fetchColumn() >= DAILY_LIMIT) $err = 'Limite de '.DAILY_LIMIT.' conversions gratuites par jour atteinte. Revenez demain ou contactez-nous sur WhatsApp.';
    elseif (!$fi || $fi['error'] !== UPLOAD_ERR_OK) $err = 'Échec de l\'envoi du fichier (taille maximale : '.MAX_MB.' Mo).';
    elseif ($fi['size'] > MAX_MB * 1048576) $err = 'Fichier trop volumineux (maximum '.MAX_MB.' Mo).';
    elseif (file_get_contents($fi['tmp_name'], false, null, 0, 5) !== '%PDF-') $err = 'Ce fichier n\'est pas un PDF valide.';
    else {
        $tok = bin2hex(random_bytes(16));
        $in = $tmp.$tok.'.pdf'; $out = $tmp.$tok.'.docx';
        move_uploaded_file($fi['tmp_name'], $in);
        $p = proc_open(['timeout', '90', PYTHON_BIN, __DIR__.'/converter/convert.py', $in, $out],
            [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        if (is_resource($p)) { stream_get_contents($pipes[1]); stream_get_contents($pipes[2]); proc_close($p); }
        @unlink($in); // le PDF original est supprimé immédiatement
        if (is_file($out) && filesize($out) > 0) {
            db()->prepare('INSERT INTO conversions (ip) VALUES (?)')->execute([$ip]);
            $done = $tok;
        } else { @unlink($out); $err = 'La conversion a échoué. Vérifiez que le PDF n\'est pas protégé par mot de passe.'; }
    }
}
head('Convertir un PDF en Word gratuit'); ?>
<main class="container mt-4" style="max-width:560px">
<div class="card shadow-sm"><div class="card-body">
<h1 class="h4">Convertir un PDF en Word</h1>
<p class="text-muted small">Gratuit : <?= DAILY_LIMIT ?> conversions par jour, <?= MAX_MB ?> Mo maximum. Vos fichiers sont supprimés automatiquement.</p>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<?php if ($done): ?>
<div class="alert alert-success">Conversion terminée ✔</div>
<a class="btn btn-success w-100 mb-2" href="convert.php?get=<?= $done ?>">⬇ Télécharger le fichier Word</a>
<a class="btn btn-link w-100" href="convert.php">Convertir un autre PDF</a>
<?php else: ?>
<form method="post" enctype="multipart/form-data" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='Conversion en cours…'">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
<input type="file" name="pdf" accept="application/pdf,.pdf" class="form-control mb-3" required>
<button class="btn btn-primary w-100">Convertir en Word</button></form>
<p class="small text-muted mt-3 mb-0">Les PDF scannés (photos de documents) ne sont pas pris en charge : le texte doit être sélectionnable.</p>
<?php endif; ?>
</div></div>
<p class="text-center mt-3 small">Besoin d'un CV ? <a href="index.php">Voir nos 15 modèles Word</a></p>
</main>
<?php foot();