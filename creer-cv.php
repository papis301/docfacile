<?php
/**
 * Créateur de CV Word sans sauvegarde applicative.
 * Les données du formulaire ne sont ni écrites en base ni conservées dans un fichier permanent.
 * Prérequis serveur : PHP avec l'extension ZipArchive activée.
 */
require_once __DIR__ . '/config.php';

function cv_text($value, int $max = 3000): string {
    if (is_array($value) || is_object($value)) return '';
    $value = trim((string)$value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}
function cv_xml(string $value): string {
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}
function cv_paragraph(string $text = '', string $style = '', bool $bold = false): string {
    $pPr = $style !== '' ? '<w:pPr><w:pStyle w:val="'.cv_xml($style).'"/></w:pPr>' : '';
    $rPr = $bold ? '<w:rPr><w:b/></w:rPr>' : '';
    return '<w:p>'.$pPr.'<w:r>'.$rPr.'<w:t xml:space="preserve">'.cv_xml($text).'</w:t></w:r></w:p>';
}
function cv_section(string $title, array $lines): string {
    $xml = cv_paragraph($title, 'Heading1');
    foreach ($lines as $line) {
        $line = cv_text($line, 1200);
        if ($line !== '') $xml .= cv_paragraph($line);
    }
    return $xml;
}
function cv_build_docx(array $data): string {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException("L'extension PHP ZipArchive n'est pas activée sur le serveur. Activez l'extension ZIP pour générer les fichiers Word.");
    }
    $body = '';
    $fullName = trim($data['prenom'].' '.$data['nom']);
    $body .= cv_paragraph($fullName !== '' ? $fullName : 'Curriculum Vitae', 'Title');
    $contact = array_filter([$data['telephone'], $data['email'], $data['ville'], $data['linkedin']]);
    if ($contact) $body .= cv_paragraph(implode(' | ', $contact), 'Subtitle');
    if ($data['profil'] !== '') $body .= cv_section('PROFIL PROFESSIONNEL', preg_split('/\R/u', $data['profil']) ?: []);

    $experiences = [];
    for ($i=0; $i<3; $i++) {
        $title = $data['exp_poste'][$i] ?? '';
        $company = $data['exp_entreprise'][$i] ?? '';
        $dates = $data['exp_dates'][$i] ?? '';
        $details = $data['exp_details'][$i] ?? '';
        if (trim($title.$company.$dates.$details) !== '') {
            $experiences[] = trim($title . ($company ? ' — '.$company : '') . ($dates ? ' | '.$dates : ''));
            if ($details !== '') $experiences = array_merge($experiences, preg_split('/\R/u', $details) ?: []);
            $experiences[] = '';
        }
    }
    if ($experiences) $body .= cv_section('EXPÉRIENCES PROFESSIONNELLES', $experiences);

    $formations = [];
    for ($i=0; $i<3; $i++) {
        $diplome = $data['formation_diplome'][$i] ?? '';
        $etablissement = $data['formation_etablissement'][$i] ?? '';
        $annee = $data['formation_annee'][$i] ?? '';
        $line = trim($diplome . ($etablissement ? ' — '.$etablissement : '') . ($annee ? ' | '.$annee : ''));
        if ($line !== '') $formations[] = $line;
    }
    if ($formations) $body .= cv_section('FORMATION', $formations);
    if ($data['competences'] !== '') $body .= cv_section('COMPÉTENCES', preg_split('/\R/u', $data['competences']) ?: []);
    if ($data['langues'] !== '') $body .= cv_section('LANGUES', preg_split('/\R/u', $data['langues']) ?: []);
    if ($data['interets'] !== '') $body .= cv_section('CENTRES D’INTÉRÊT', preg_split('/\R/u', $data['interets']) ?: []);

    $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
      .$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134"/></w:sectPr>'
      .'</w:body></w:document>';
    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
      .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="21"/><w:color w:val="263445"/></w:rPr><w:pPr><w:spacing w:after="120"/></w:pPr></w:style>'
      .'<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:rPr><w:b/><w:color w:val="1F4E79"/><w:sz w:val="36"/></w:rPr><w:pPr><w:spacing w:after="100"/></w:pPr></w:style>'
      .'<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:rPr><w:color w:val="526579"/><w:sz w:val="18"/></w:rPr><w:pPr><w:spacing w:after="220"/></w:pPr></w:style>'
      .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rPr><w:b/><w:color w:val="2E7D32"/><w:sz w:val="24"/></w:rPr><w:pPr><w:keepNext/><w:spacing w:before="240" w:after="100"/><w:pBdr><w:bottom w:val="single" w:sz="8" w:space="4" w:color="D8E4DD"/></w:pBdr></w:pPr></w:style>'
      .'</w:styles>';
    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>';
    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
    $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    $tmp = tempnam(sys_get_temp_dir(), 'cvf_');
    if ($tmp === false) throw new RuntimeException('Impossible de créer le document temporaire.');
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) { @unlink($tmp); throw new RuntimeException('Impossible de préparer le document Word.'); }
    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('word/document.xml', $document);
    $zip->addFromString('word/styles.xml', $styles);
    $zip->addFromString('word/_rels/document.xml.rels', $docRels);
    if (!$zip->close()) {
    @unlink($tmp);
    throw new RuntimeException(
        'Impossible de finaliser le fichier Word.'
    );
}

if (!is_file($tmp) || filesize($tmp) === 0) {
    @unlink($tmp);
    throw new RuntimeException(
        'Le fichier Word est vide ou absent.'
    );
}

return $tmp;
    return $tmp;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_cv'])) {
    $data = [
        'prenom'=>cv_text($_POST['prenom'] ?? '',100), 'nom'=>cv_text($_POST['nom'] ?? '',100),
        'telephone'=>cv_text($_POST['telephone'] ?? '',80), 'email'=>cv_text($_POST['email'] ?? '',180),
        'ville'=>cv_text($_POST['ville'] ?? '',120), 'linkedin'=>cv_text($_POST['linkedin'] ?? '',250),
        'profil'=>cv_text($_POST['profil'] ?? '',2500), 'competences'=>cv_text($_POST['competences'] ?? '',1800),
        'langues'=>cv_text($_POST['langues'] ?? '',1000), 'interets'=>cv_text($_POST['interets'] ?? '',1000),
        'exp_poste'=>[], 'exp_entreprise'=>[], 'exp_dates'=>[], 'exp_details'=>[],
        'formation_diplome'=>[], 'formation_etablissement'=>[], 'formation_annee'=>[]
    ];
    foreach (['exp_poste','exp_entreprise','exp_dates','exp_details','formation_diplome','formation_etablissement','formation_annee'] as $key) {
        $raw = $_POST[$key] ?? [];
        for ($i=0; $i<3; $i++) $data[$key][$i] = cv_text(is_array($raw) ? ($raw[$i] ?? '') : '', 1500);
    }
    if ($data['prenom'] === '' || $data['nom'] === '') {
        $error = 'Veuillez renseigner au minimum votre prénom et votre nom.';
    } else {
        $tmp = null;
        try {
            $tmp = cv_build_docx($data);
            $filename = 'CV-'.preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($data['prenom'].'-'.$data['nom']));
            $filename = trim($filename, '-') ?: 'CV';
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="'.substr($filename,0,90).'.docx"');
            header('Content-Length: '.filesize($tmp));
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('X-Content-Type-Options: nosniff');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (Throwable $e) {
            if ($tmp && is_file($tmp)) @unlink($tmp);
            error_log('DocsFacile DOCX generation error: '.$e->getMessage());
            $error = $e instanceof RuntimeException && strpos($e->getMessage(), 'ZipArchive') !== false
                ? $e->getMessage()
                : 'La génération du document a échoué. Réessayez plus tard.';
        }
    }
}

head('Créer mon CV Word');
?>
<style>
:root{--cv-blue:#1f4e79;--cv-green:#2e7d32;--cv-ink:#203044;--cv-muted:#6b7787;--cv-line:#e2e8ef}
body{background:#f4f7fb;color:var(--cv-ink)}.builder-hero{background:linear-gradient(125deg,#173d62,#2e7d32);color:#fff;padding:34px 0 30px}.builder-wrap{max-width:1040px}.builder-card{border:1px solid var(--cv-line);border-radius:18px;background:#fff;box-shadow:0 12px 32px #1d35570b}.stepper{display:flex;gap:8px;overflow:auto;padding-bottom:4px}.step-tab{border:1px solid var(--cv-line);background:#f8fafc;color:#667085;border-radius:12px;padding:10px 14px;white-space:nowrap;font-size:.9rem;font-weight:600}.step-tab.active{background:#eaf2fb;border-color:#9ab9d9;color:var(--cv-blue)}.step-tab.done{background:#edf7ee;border-color:#b6d9ba;color:#27632b}.step-panel{display:none}.step-panel.active{display:block}.form-label{font-weight:600;font-size:.9rem}.form-control,.form-select{border-color:#d9e1ea;border-radius:10px;padding:.7rem .8rem}.form-control:focus{border-color:#5d8ab8;box-shadow:0 0 0 .2rem #1f4e7917}.step-title{font-weight:750;letter-spacing:-.02em}.btn-primary{background:var(--cv-blue);border-color:var(--cv-blue)}.privacy-note{background:#eff8f0;border:1px solid #d1e8d3;border-radius:12px;color:#285b2d}.required-note{font-size:.83rem;color:var(--cv-muted)}.nav-actions{display:flex;justify-content:space-between;gap:12px;margin-top:26px}.mini-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700;color:#718096}.cv-preview{position:sticky;top:20px}.preview-paper{min-height:460px;background:#fff;border:1px solid #dfe5ec;box-shadow:0 8px 24px #25364b12;padding:24px 20px}.preview-name{font-size:1.45rem;font-weight:800;color:var(--cv-blue);overflow-wrap:anywhere}.preview-rule{height:3px;background:var(--cv-green);margin:12px 0 18px}.preview-section{font-size:.78rem;color:var(--cv-green);font-weight:800;border-bottom:1px solid #dce7df;padding-bottom:4px;margin:16px 0 7px}.preview-copy{font-size:.77rem;color:#5c6878;white-space:pre-line;overflow-wrap:anywhere}.section-box{border:1px solid var(--cv-line);border-radius:12px;padding:14px;margin-top:12px;background:#fcfdff}@media(max-width:767px){.builder-hero{padding:25px 0}.preview-col{display:none}.builder-card{border-radius:14px}.step-tab{padding:9px 11px}.nav-actions .btn{flex:1}}
</style>
<section class="builder-hero"><div class="container builder-wrap"><div class="small fw-semibold mb-2">DOCSFACILE · CRÉATEUR DE CV</div><h1 class="h2 fw-bold mb-2">Créez votre CV, simplement.</h1><p class="mb-0 text-white-50">Remplissez les étapes puis téléchargez votre document Word modifiable.</p></div></section>
<main class="container builder-wrap my-4 my-lg-5">
  <div class="privacy-note p-3 mb-4 d-flex gap-3 align-items-start"><span class="fs-4">🔒</span><div><strong>Vos informations restent privées.</strong><div class="small mt-1">Ce formulaire ne sauvegarde pas vos données dans la base de données DocsFacile. Elles servent à générer le document Word puis sont supprimées du fichier temporaire du serveur après son envoi.</div></div></div>
  <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
  <div class="row g-4 align-items-start">
    <div class="col-lg-8"><form method="post" class="builder-card p-3 p-md-4" id="cvForm" autocomplete="on">
      <div class="stepper mb-4" aria-label="Étapes du formulaire"><button type="button" class="step-tab active" data-step="0">1. Profil</button><button type="button" class="step-tab" data-step="1">2. Expérience</button><button type="button" class="step-tab" data-step="2">3. Formation</button><button type="button" class="step-tab" data-step="3">4. Compétences</button></div>
      <section class="step-panel active" data-panel="0"><div class="mini-label mb-2">Étape 1 sur 4</div><h2 class="h4 step-title mb-1">Vos informations personnelles</h2><p class="text-secondary small mb-4">Commencez par les informations qui apparaîtront en haut de votre CV.</p>
        <div class="row g-3"><div class="col-sm-6"><label class="form-label" for="prenom">Prénom *</label><input class="form-control live" id="prenom" name="prenom" maxlength="100" required autocomplete="given-name" value="<?= e($_POST['prenom'] ?? '') ?>" placeholder="Ex. : Aïssatou"></div><div class="col-sm-6"><label class="form-label" for="nom">Nom *</label><input class="form-control live" id="nom" name="nom" maxlength="100" required autocomplete="family-name" value="<?= e($_POST['nom'] ?? '') ?>" placeholder="Ex. : Ndiaye"></div>
        <div class="col-sm-6"><label class="form-label" for="telephone">Téléphone</label><input class="form-control live" id="telephone" name="telephone" maxlength="80" autocomplete="tel" value="<?= e($_POST['telephone'] ?? '') ?>" placeholder="+221 77 000 00 00"></div><div class="col-sm-6"><label class="form-label" for="email">Adresse e-mail</label><input class="form-control live" id="email" name="email" type="email" maxlength="180" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="vous@exemple.com"></div>
        <div class="col-sm-6"><label class="form-label" for="ville">Ville / Pays</label><input class="form-control live" id="ville" name="ville" maxlength="120" autocomplete="address-level2" value="<?= e($_POST['ville'] ?? '') ?>" placeholder="Dakar, Sénégal"></div><div class="col-sm-6"><label class="form-label" for="linkedin">LinkedIn ou portfolio</label><input class="form-control live" id="linkedin" name="linkedin" maxlength="250" value="<?= e($_POST['linkedin'] ?? '') ?>" placeholder="linkedin.com/in/votre-profil"></div>
        <div class="col-12"><label class="form-label" for="profil">Profil professionnel</label><textarea class="form-control live" id="profil" name="profil" rows="4" maxlength="2500" placeholder="Présentez-vous en quelques phrases, vos points forts et votre objectif professionnel."><?= e($_POST['profil'] ?? '') ?></textarea></div></div>
      </section>
      <section class="step-panel" data-panel="1"><div class="mini-label mb-2">Étape 2 sur 4</div><h2 class="h4 step-title mb-1">Expériences professionnelles</h2><p class="text-secondary small mb-3">Ajoutez jusqu’à trois expériences, de la plus récente à la plus ancienne.</p>
        <?php for ($i=0;$i<3;$i++): ?><div class="section-box"><div class="fw-bold mb-3">Expérience <?= $i+1 ?></div><div class="row g-3"><div class="col-sm-6"><label class="form-label">Poste occupé</label><input class="form-control live" name="exp_poste[]" maxlength="250" value="<?= e($_POST['exp_poste'][$i] ?? '') ?>" placeholder="Développeur web"></div><div class="col-sm-6"><label class="form-label">Entreprise</label><input class="form-control live" name="exp_entreprise[]" maxlength="250" value="<?= e($_POST['exp_entreprise'][$i] ?? '') ?>" placeholder="Nom de l'entreprise"></div><div class="col-12"><label class="form-label">Période</label><input class="form-control live" name="exp_dates[]" maxlength="120" value="<?= e($_POST['exp_dates'][$i] ?? '') ?>" placeholder="Janvier 2023 – Aujourd'hui"></div><div class="col-12"><label class="form-label">Missions et réalisations</label><textarea class="form-control live" name="exp_details[]" rows="3" maxlength="1500" placeholder="Une mission ou réalisation par ligne."><?= e($_POST['exp_details'][$i] ?? '') ?></textarea></div></div></div><?php endfor; ?>
      </section>
      <section class="step-panel" data-panel="2"><div class="mini-label mb-2">Étape 3 sur 4</div><h2 class="h4 step-title mb-1">Formation et diplômes</h2><p class="text-secondary small mb-3">Indiquez vos diplômes, formations ou certifications.</p>
        <?php for ($i=0;$i<3;$i++): ?><div class="section-box"><div class="fw-bold mb-3">Formation <?= $i+1 ?></div><div class="row g-3"><div class="col-sm-6"><label class="form-label">Diplôme / formation</label><input class="form-control live" name="formation_diplome[]" maxlength="250" value="<?= e($_POST['formation_diplome'][$i] ?? '') ?>" placeholder="Licence en informatique"></div><div class="col-sm-6"><label class="form-label">Établissement</label><input class="form-control live" name="formation_etablissement[]" maxlength="250" value="<?= e($_POST['formation_etablissement'][$i] ?? '') ?>" placeholder="Université / école"></div><div class="col-sm-6"><label class="form-label">Année / période</label><input class="form-control live" name="formation_annee[]" maxlength="100" value="<?= e($_POST['formation_annee'][$i] ?? '') ?>" placeholder="2020 – 2023"></div></div></div><?php endfor; ?>
      </section>
      <section class="step-panel" data-panel="3"><div class="mini-label mb-2">Étape 4 sur 4</div><h2 class="h4 step-title mb-1">Compétences et autres informations</h2><p class="text-secondary small mb-4">Terminez votre CV avec les informations utiles au poste recherché.</p><div class="mb-3"><label class="form-label" for="competences">Compétences</label><textarea class="form-control live" id="competences" name="competences" rows="4" maxlength="1800" placeholder="Gestion de projet&#10;Microsoft Office&#10;Développement Android"><?= e($_POST['competences'] ?? '') ?></textarea><div class="form-text">Vous pouvez mettre une compétence par ligne.</div></div><div class="mb-3"><label class="form-label" for="langues">Langues</label><textarea class="form-control live" id="langues" name="langues" rows="2" maxlength="1000" placeholder="Français : courant&#10;Anglais : intermédiaire"><?= e($_POST['langues'] ?? '') ?></textarea></div><div class="mb-3"><label class="form-label" for="interets">Centres d’intérêt</label><textarea class="form-control live" id="interets" name="interets" rows="2" maxlength="1000" placeholder="Lecture, bénévolat, sport..."><?= e($_POST['interets'] ?? '') ?></textarea></div><div class="alert alert-light border small mb-0"><strong>Prêt à générer ?</strong> Le fichier Word est modifiable. Vérifiez vos informations dans l'aperçu puis cliquez sur « Générer mon CV Word ».</div></section>
      <div class="nav-actions"><button type="button" class="btn btn-outline-secondary" id="prevBtn" disabled>← Précédent</button><button type="button" class="btn btn-primary" id="nextBtn">Étape suivante →</button><button type="submit" name="generate_cv" value="1" class="btn btn-success d-none" id="generateBtn">Générer mon CV Word ↓</button></div>
      <p class="required-note mt-3 mb-0">* Champs obligatoires. Aucune création de compte n’est nécessaire.</p>
    </form></div>
    <aside class="col-lg-4 preview-col"><div class="cv-preview"><div class="d-flex justify-content-between align-items-center mb-2"><strong>Aperçu de votre CV</strong><span class="badge text-bg-light">A4</span></div><div class="preview-paper"><div class="preview-name" id="p-name">Votre nom</div><div class="preview-copy" id="p-contact">Téléphone · E-mail · Ville</div><div class="preview-rule"></div><div id="p-profil-wrap"><div class="preview-section">PROFIL PROFESSIONNEL</div><div class="preview-copy" id="p-profil">Votre résumé professionnel apparaîtra ici.</div></div><div class="preview-section">EXPÉRIENCES</div><div class="preview-copy" id="p-exp">Vos expériences apparaîtront ici.</div><div class="preview-section">FORMATION</div><div class="preview-copy" id="p-formation">Vos formations apparaîtront ici.</div><div id="p-competences-wrap"><div class="preview-section">COMPÉTENCES</div><div class="preview-copy" id="p-competences">Vos compétences apparaîtront ici.</div></div><div id="p-langues-wrap"><div class="preview-section">LANGUES</div><div class="preview-copy" id="p-langues">Vos langues apparaîtront ici.</div></div></div><p class="small text-secondary mt-2 mb-0">Aperçu indicatif. La mise en page finale peut varier légèrement selon votre logiciel Word.</p></div></aside>
  </div>
</main>
<script>
(function(){
 const panels=[...document.querySelectorAll('[data-panel]')], tabs=[...document.querySelectorAll('[data-step]')];let current=0;
 const prev=document.getElementById('prevBtn'),next=document.getElementById('nextBtn'),generate=document.getElementById('generateBtn');
 function showStep(n){current=Math.max(0,Math.min(panels.length-1,n));panels.forEach((p,i)=>p.classList.toggle('active',i===current));tabs.forEach((t,i)=>{t.classList.toggle('active',i===current);t.classList.toggle('done',i<current)});prev.disabled=current===0;next.classList.toggle('d-none',current===panels.length-1);generate.classList.toggle('d-none',current!==panels.length-1);if(current===0){document.getElementById('prenom').focus({preventScroll:true})} }
 next.addEventListener('click',()=>{if(current===0){const form=document.getElementById('cvForm');for(const id of ['prenom','nom']){const el=document.getElementById(id);if(!el.reportValidity())return}}showStep(current+1)});prev.addEventListener('click',()=>showStep(current-1));tabs.forEach(t=>t.addEventListener('click',()=>{if(Number(t.dataset.step)<=current)showStep(Number(t.dataset.step));else if(current===0){if(document.getElementById('prenom').reportValidity()&&document.getElementById('nom').reportValidity())showStep(Number(t.dataset.step))}}));
 const val=(selector)=>{const el=document.querySelector(selector);return el?el.value.trim():''};const put=(id,text,placeholder)=>{document.getElementById(id).textContent=text||placeholder};
 function update(){const name=[val('#prenom'),val('#nom')].filter(Boolean).join(' ');put('p-name',name,'Votre nom');put('p-contact',[val('#telephone'),val('#email'),val('#ville'),val('#linkedin')].filter(Boolean).join(' · '),'Téléphone · E-mail · Ville');put('p-profil',val('#profil'),'Votre résumé professionnel apparaîtra ici.');document.getElementById('p-profil-wrap').style.display=val('#profil')?'':'none';
 const exp=[];for(let i=0;i<3;i++){const a=document.querySelectorAll('[name="exp_poste[]"]')[i]?.value.trim(),b=document.querySelectorAll('[name="exp_entreprise[]"]')[i]?.value.trim(),c=document.querySelectorAll('[name="exp_dates[]"]')[i]?.value.trim(),d=document.querySelectorAll('[name="exp_details[]"]')[i]?.value.trim();if(a||b||c||d)exp.push([a,b,c].filter(Boolean).join(' — ')+(d?'\n'+d:''))}put('p-exp',exp.join('\n\n'),'Vos expériences apparaîtront ici.');
 const edu=[];for(let i=0;i<3;i++){const a=document.querySelectorAll('[name="formation_diplome[]"]')[i]?.value.trim(),b=document.querySelectorAll('[name="formation_etablissement[]"]')[i]?.value.trim(),c=document.querySelectorAll('[name="formation_annee[]"]')[i]?.value.trim();if(a||b||c)edu.push([a,b,c].filter(Boolean).join(' — '))}put('p-formation',edu.join('\n'),'Vos formations apparaîtront ici.');put('p-competences',val('#competences'),'Vos compétences apparaîtront ici.');put('p-langues',val('#langues'),'Vos langues apparaîtront ici.');document.getElementById('p-competences-wrap').style.display=val('#competences')?'':'none';document.getElementById('p-langues-wrap').style.display=val('#langues')?'':'none';}
 document.querySelectorAll('.live').forEach(el=>el.addEventListener('input',update));update();
 document.getElementById('cvForm').addEventListener('submit',function(e){if(!document.getElementById('prenom').reportValidity()||!document.getElementById('nom').reportValidity()){e.preventDefault();showStep(0);return}generate.disabled=true;generate.textContent='Génération en cours…';});
})();
</script>
<?php foot(); ?>
