<?php
// OUTIL LOCAL : à lancer sur votre ordinateur uniquement, puis à SUPPRIMER. Ne jamais l'envoyer en ligne.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(403); exit('Réservé au poste local.'); }
$line = ''; $err = '';
if (isset($_POST['p'])) {
    if (mb_strlen($_POST['p']) < 8) $err = 'Utilisez au moins 8 caractères.';
    else $line = "const ADMIN_PASSWORD_HASH = '".password_hash($_POST['p'], PASSWORD_DEFAULT)."';";
}
?><!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<body style="font-family:sans-serif;max-width:560px;margin:2rem auto;padding:0 1rem">
<h1>Générer le hash du mot de passe admin</h1>
<form method="post"><input type="text" name="p" placeholder="Nouveau mot de passe (8 caractères min.)" style="width:100%;padding:.6rem" autocomplete="off">
<button style="margin-top:.6rem;padding:.6rem 1rem">Générer</button></form>
<?php if ($err): ?><p style="color:#b00"><?= htmlspecialchars($err) ?></p><?php endif; ?>
<?php if ($line): ?><p>Remplacez la ligne <code>ADMIN_PASSWORD_HASH</code> dans <code>config.php</code> par :</p>
<textarea rows="4" style="width:100%" readonly onclick="this.select()"><?= htmlspecialchars($line) ?></textarea>
<p>Puis supprimez ce fichier.</p><?php endif; ?>