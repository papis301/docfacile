<?php require 'config.php';
session_start();
$services = require 'services.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$sel = isset($services[$_GET['service'] ?? '']) ? $_GET['service'] : '';
$err = ''; $wa = ''; $v = ['nom'=>'', 'contact'=>'', 'service'=>$sel, 'message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0';
    $q = db()->prepare('SELECT COUNT(*) FROM demandes WHERE ip=? AND created_at > NOW() - INTERVAL 1 HOUR');
    $q->execute([$ip]);
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $err = 'Session expirée, rechargez la page.';
    elseif (!empty($_POST['site'])) $err = 'Demande refusée.'; // champ piège pour les robots
    elseif ((int)$q->fetchColumn() >= 3) $err = 'Trop de demandes envoyées. Réessayez plus tard.';
    elseif (mb_strlen($v['nom']) < 2 || mb_strlen($v['nom']) > 80) $err = 'Indiquez votre nom.';
    elseif (mb_strlen($v['contact']) < 6 || mb_strlen($v['contact']) > 60) $err = 'Indiquez un numéro WhatsApp ou un e-mail valide.';
    elseif (!isset($services[$v['service']])) $err = 'Choisissez un service.';
    elseif (mb_strlen($v['message']) < 5 || mb_strlen($v['message']) > 1000) $err = 'Décrivez votre besoin (5 à 1000 caractères).';
    else {
        db()->prepare('INSERT INTO demandes (nom,contact,service,message,ip) VALUES (?,?,?,?,?)')
            ->execute([$v['nom'], $v['contact'], $services[$v['service']][0], $v['message'], $ip]);
        $txt = "Bonjour, je suis {$v['nom']}. Service : {$services[$v['service']][0]}. {$v['message']}";
        $wa = 'https://wa.me/'.WHATSAPP.'?text='.rawurlencode($txt);
    }
}
head('Demander un service'); ?>
<main class="container mt-4" style="max-width:560px">
<div class="card shadow-sm"><div class="card-body">
<?php if ($wa): ?>
<h1 class="h5">Demande enregistrée ✔</h1>
<p>Merci ! Nous vous répondrons rapidement. Pour aller plus vite, envoyez aussi votre demande sur WhatsApp :</p>
<a class="btn btn-success w-100 mb-2" href="<?= e($wa) ?>">💬 Envoyer sur WhatsApp</a>
<a class="btn btn-link w-100" href="index.php">← Retour à l'accueil</a>
<?php else: ?>
<h1 class="h4">Demander un service</h1>
<p class="text-muted small">Décrivez votre besoin, nous vous répondons avec un devis.</p>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
<input type="text" name="site" class="d-none" tabindex="-1" autocomplete="off">
<label class="form-label small mb-1">Votre nom</label>
<input name="nom" class="form-control mb-2" value="<?= e($v['nom']) ?>" required maxlength="80">
<label class="form-label small mb-1">WhatsApp ou e-mail</label>
<input name="contact" class="form-control mb-2" value="<?= e($v['contact']) ?>" required maxlength="60" placeholder="+221 77 000 00 00">
<label class="form-label small mb-1">Service</label>
<select name="service" class="form-select mb-2" required><option value="">Choisir…</option>
<?php foreach ($services as $s => $sv): ?><option value="<?= e($s) ?>" <?= $v['service'] === $s ? 'selected' : '' ?>><?= e($sv[0]) ?></option><?php endforeach; ?></select>
<label class="form-label small mb-1">Votre demande</label>
<textarea name="message" rows="4" class="form-control mb-3" required maxlength="1000"><?= e($v['message']) ?></textarea>
<button class="btn btn-primary w-100">Envoyer la demande</button></form>
<?php endif; ?>
</div></div></main>
<?php foot();