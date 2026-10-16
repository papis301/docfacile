<?php
/**
 * Créateur de factures PDF sans stockage.
 * Tout le traitement des champs se fait dans le navigateur (JavaScript).
 * Aucune donnée n'est envoyée ni conservée côté serveur.
 */
require_once __DIR__ . '/config.php';

head('Créer une facture PDF — DocsFacile');
?>
<style>
:root{--cv-blue:#1f4e79;--cv-green:#2e7d32;--cv-ink:#203044;--cv-muted:#6b7787;--cv-line:#e2e8ef;--cv-bg:#f4f7fb}
body{background:var(--cv-bg);color:var(--cv-ink)}
.builder-hero{background:linear-gradient(125deg,#173d62,#2e7d32);color:#fff;padding:34px 0 30px}
.builder-wrap{max-width:1180px}
.builder-card{border:1px solid var(--cv-line);border-radius:18px;background:#fff;box-shadow:0 12px 32px #1d35570b;overflow:hidden}
.panel-head{padding:17px 22px;border-bottom:1px solid var(--cv-line);background:#fbfdff}
.panel-head h2{font-size:18px;margin:0;font-weight:750;color:var(--cv-blue)}
.panel-head p{margin:4px 0 0;color:var(--cv-muted);font-size:13px}
.body{padding:20px}
.privacy-note{background:#eff8f0;border:1px solid #d1e8d3;border-radius:12px;color:#285b2d}
.sect{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#426078;font-weight:800;margin:3px 0 12px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.field{min-width:0}
.full{grid-column:1/-1}
label{display:block;font-weight:650;font-size:13px;margin-bottom:5px;color:#344054}
input,textarea,select{width:100%;font:inherit;color:var(--cv-ink);border:1px solid #d9e1ea;border-radius:10px;padding:10px 11px;background:white;outline:none;transition:border-color .15s,box-shadow .15s}
input:focus,textarea:focus,select:focus{border-color:#5d8ab8;box-shadow:0 0 0 .2rem #1f4e7917}
textarea{resize:vertical;min-height:65px}
hr{border:0;border-top:1px solid var(--cv-line);margin:19px 0}
.itemshead{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.itemshead h3{font-size:15px;margin:0;font-weight:750;color:var(--cv-blue)}
.item{border:1px solid var(--cv-line);border-radius:10px;padding:11px;margin-bottom:9px;background:#fcfdff}
.itemgrid{display:grid;grid-template-columns:minmax(0,2fr) 72px 100px 32px;gap:7px;align-items:end}
.item label{font-size:11px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:0;border-radius:10px;padding:11px 14px;font-weight:750;font-size:14px;cursor:pointer;text-decoration:none;transition:filter .15s}
.btn:hover{filter:brightness(1.06)}
.primary{background:var(--cv-green);color:white}
.primary:hover{background:#256b28}
.secondary{background:#edf2f7;color:#25384b}
.danger{background:#fff0f0;color:#b42318}
.outline{background:#ffffff18;color:white;border:1px solid #ffffff66}
.remove{height:39px;border:0;border-radius:8px;background:#fff0f0;color:#b42318;font-size:20px;cursor:pointer}
.taxgrid{display:grid;grid-template-columns:1fr 130px;gap:12px;align-items:end}
.check{display:flex;align-items:center;gap:8px;padding:10px 0}
.check input{width:auto}
.actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:17px}
.status{min-height:20px;color:var(--cv-muted);font-size:12px;margin-top:8px}
.note{font-size:12px;color:var(--cv-muted)}
.preview{padding:17px;background:#eaf0f6}
.labelpreview{font-size:11px;color:var(--cv-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin:0 0 9px}
.paper{background:#fff;min-height:520px;padding:26px 23px;box-shadow:0 4px 18px #17324d12;color:#202b37;font-size:12px;display:flex;flex-direction:column}
.top{display:flex;justify-content:space-between;gap:15px;border-bottom:3px solid var(--cv-green);padding-bottom:17px;margin-bottom:19px}
.seller{font-size:22px;font-weight:850;color:var(--cv-green);overflow-wrap:anywhere}
.facture{font-size:26px;font-weight:850;text-align:right;color:var(--cv-blue)}
.muted{color:#69798a;white-space:pre-line;overflow-wrap:anywhere}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:17px}
.ptitle{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--cv-green);margin-bottom:5px}
.meta{display:flex;flex-wrap:wrap;gap:7px 17px;background:#f3f7f6;padding:10px;border-radius:7px;margin-bottom:17px}
.ptable{width:100%;border-collapse:collapse;table-layout:fixed;margin:10px 0 15px}
.ptable th{background:var(--cv-blue);color:white;text-align:left;padding:8px 5px;font-size:10px}
.ptable td{padding:8px 5px;border-bottom:1px solid #e3e9ee;overflow-wrap:anywhere;vertical-align:top}
.num{text-align:right;white-space:nowrap}
.totals{margin-left:auto;width:min(100%,240px)}
.totalrow{display:flex;justify-content:space-between;gap:8px;padding:5px 0}
.grand{border-top:2px solid var(--cv-green);margin-top:5px;padding-top:9px;font-size:15px;font-weight:850;color:var(--cv-green)}
.sumwords{margin:14px 0 0;padding:9px 11px;background:#f3f7f6;border-left:3px solid var(--cv-green);border-radius:6px;font-size:11px;color:#202b37;line-height:1.5}
.sumwords span{font-weight:700;color:var(--cv-green);text-transform:capitalize}
.notes{border-top:1px solid #e3e9ee;padding-top:12px;margin-top:20px;white-space:pre-line;overflow-wrap:anywhere}
.paperfoot{margin-top:auto;border-top:1px solid #e3e9ee;padding-top:9px;color:#7a8793;font-size:10px;text-align:center}
.paperfoot .fl{color:#202b37;font-weight:600}
.paperfoot .fp{margin-top:6px;color:#7a8793;font-weight:400}
@media(max-width:900px){.layout{grid-template-columns:1fr}}
@media(max-width:560px){.grid{grid-template-columns:1fr}.full{grid-column:auto}.body{padding:14px}.itemgrid{grid-template-columns:minmax(0,1fr) 62px 85px 30px}.paper{padding:18px 13px}.seller{font-size:18px}.facture{font-size:21px}.actions .btn{flex:1}}
@media print{body{background:white}.noprint,header,footer,.formpanel,.notice{display:none!important}main{padding:0;margin:0}.layout{display:block}.previewpanel{border:0;box-shadow:none}.preview{padding:0;background:white}.paper{box-shadow:none;padding:0}.labelpreview{display:none}}
</style>

<section class="builder-hero"><div class="container builder-wrap"><div class="small fw-semibold mb-2">DOCSFACILE · CRÉATEUR DE FACTURE</div><h1 class="h2 fw-bold mb-2">Créez votre facture PDF, simplement.</h1><p class="mb-0 text-white-50">Remplissez le formulaire puis téléchargez votre facture professionnelle en PDF.</p></div></section>

<main class="container builder-wrap my-4 my-lg-5">

  <div class="privacy-note p-3 mb-4 d-flex gap-3 align-items-start">
    <span class="fs-4">🔒</span>
    <div>
      <strong>Vos informations restent privées.</strong>
      <div class="small mt-1">Les champs sont traités directement dans votre navigateur. Cette page ne les envoie pas à votre serveur et ne les enregistre pas dans une base de données.</div>
    </div>
  </div>

  <div class="row g-4 align-items-start layout">

    <!-- ================= FORMULAIRE ================= -->
    <div class="col-lg-7 formpanel">
      <div class="builder-card">
        <div class="panel-head"><h2>Informations de la facture</h2><p>Complétez les champs puis téléchargez le PDF.</p></div>
        <div class="body">
          <form id="form" autocomplete="off">
            <div class="sect">Votre entreprise</div>
            <div class="grid">
              <div class="field full"><label for="seller">Nom de l'entreprise / prestataire *</label><input id="seller" maxlength="140" required placeholder="Ex. : Ndiaye Services"></div>
              <div class="field"><label for="phone">Téléphone</label><input id="phone" maxlength="60" placeholder="+221 ..."></div>
              <div class="field"><label for="email">E-mail</label><input id="email" type="email" maxlength="140" placeholder="contact@exemple.com"></div>
              <div class="field full"><label for="address">Adresse</label><textarea id="address" maxlength="400" rows="2" placeholder="Adresse, ville, pays"></textarea></div>
              <div class="field"><label for="taxid">NINEA / identifiant fiscal</label><input id="taxid" maxlength="100" placeholder="Facultatif"></div>
              <div class="field"><label for="currency">Devise</label><select id="currency"><option value="XOF">FCFA (XOF)</option><option value="XAF">FCFA (XAF)</option><option value="EUR">Euro (EUR)</option><option value="USD">Dollar américain (USD)</option><option value="GBP">Livre sterling (GBP)</option><option value="MAD">Dirham marocain (MAD)</option><option value="GMD">Dalasi gambien (GMD)</option><option value="GNF">Franc guinéen (GNF)</option><option value="CAD">Dollar canadien (CAD)</option><option value="CHF">Franc suisse (CHF)</option><option value="NGN">Naira nigérian (NGN)</option></select></div>
              <div class="field full"><label for="footerline1">Pied de page — Ligne 1 (facultatif)</label><input id="footerline1" maxlength="300" placeholder="Ex. : AFRICA BUSINESS VISION – Cité Magistrat Damel, 10200 – Agrément n° 006060644"></div>
              <div class="field full"><label for="footerline2">Pied de page — Ligne 2 (facultatif)</label><input id="footerline2" maxlength="300" placeholder="Ex. : SERVICE COMMERCIAL : 77 766 61 26 / SERVICE TECHNIQUE : 33 860 67 46"></div>
            </div>
            <hr>
            <div class="sect">Client</div>
            <div class="grid">
              <div class="field full"><label for="client">Nom du client *</label><input id="client" maxlength="140" required placeholder="Nom du client ou entreprise"></div>
              <div class="field"><label for="clientemail">E-mail du client</label><input id="clientemail" type="email" maxlength="140"></div>
              <div class="field"><label for="clientphone">Téléphone du client</label><input id="clientphone" maxlength="60"></div>
              <div class="field full"><label for="clientaddress">Adresse du client</label><textarea id="clientaddress" maxlength="400" rows="2"></textarea></div>
            </div>
            <hr>
            <div class="sect">Détails de la facture</div>
            <div class="grid">
              <div class="field"><label for="number">Numéro de facture</label><input id="number" maxlength="60"></div>
              <div class="field"><label for="date">Date d'émission *</label><input id="date" type="date" required></div>
              <div class="field"><label for="due">Date d'échéance</label><input id="due" type="date"></div>
              <div class="field"><label for="reference">Référence / commande</label><input id="reference" maxlength="100"></div>
            </div>
            <hr>
            <div class="itemshead"><h3>Produits et services</h3><button class="btn secondary" id="add" type="button">＋ Ajouter une ligne</button></div>
            <div id="items"></div>
            <div class="taxgrid">
              <div class="field"><label for="discount">Remise globale (%)</label><input id="discount" type="number" min="0" max="100" step="0.01" value="0"></div>
              <div class="field"><label for="taxrate">Taxe (%)</label><input id="taxrate" type="number" min="0" max="100" step="0.01" value="0" disabled></div>
            </div>
            <label class="check"><input id="taxon" type="checkbox"> Appliquer une taxe (selon la réglementation applicable)</label>
            <div class="field"><label for="notes">Notes / conditions de paiement</label><textarea id="notes" maxlength="1200" placeholder="Ex. : Paiement à réception. Merci pour votre confiance."></textarea></div>
            <div class="actions">
              <button class="btn primary" id="pdf" type="button">↓ Télécharger la facture PDF</button>
              <button class="btn secondary" id="print" type="button">Imprimer</button>
              <button class="btn danger" id="clear" type="button">Effacer la facture</button>
            </div>
            <div class="status" id="status" role="status" aria-live="polite"></div>
            <p class="note">Les taux de taxe et mentions légales dépendent de votre pays et de votre situation. Vérifiez-les avant d'émettre la facture.</p>
          </form>
        </div>
      </div>
    </div>

    <!-- ================= APERÇU ================= -->
    <div class="col-lg-5 previewpanel">
      <div class="builder-card">
        <div class="panel-head"><h2>Aperçu en direct</h2><p>Le document se met à jour pendant votre saisie.</p></div>
        <div class="preview">
          <p class="labelpreview">Aperçu de la facture</p>
          <article class="paper" id="paper">
            <div class="top">
              <div><div class="seller" id="pseller">Votre entreprise</div><div class="muted" id="psellerdetails">Coordonnées de votre entreprise</div></div>
              <div><div class="facture">FACTURE</div><div class="muted" style="text-align:right" id="pnumber">N° —</div></div>
            </div>
            <div class="parties">
              <div><div class="ptitle">Facturé par</div><div id="psellerblock">Votre entreprise</div><div class="muted" id="paddress"></div><div class="muted" id="ptaxid"></div></div>
              <div><div class="ptitle">Facturé à</div><div id="pclient">Nom du client</div><div class="muted" id="pclientaddress"></div><div class="muted" id="pclientdetails"></div></div>
            </div>
            <div class="meta"><span><strong>Date :</strong> <span id="pdate">—</span></span><span><strong>Échéance :</strong> <span id="pdue">—</span></span><span id="prefwrap"><strong>Référence :</strong> <span id="pref">—</span></span></div>
            <table class="ptable"><thead><tr><th style="width:46%">Description</th><th style="width:14%" class="num">Qté</th><th style="width:20%" class="num">Prix unit.</th><th style="width:20%" class="num">Total</th></tr></thead><tbody id="pitems"><tr><td>Votre prestation</td><td class="num">1</td><td class="num">0</td><td class="num">0</td></tr></tbody></table>
            <div class="totals">
              <div class="totalrow"><span>Sous-total</span><strong id="psub">0</strong></div>
              <div class="totalrow" id="pdiscountrow" style="display:none"><span>Remise</span><strong id="pdiscount">0</strong></div>
              <div class="totalrow" id="ptaxrow" style="display:none"><span id="ptaxlabel">Taxe</span><strong id="ptax">0</strong></div>
              <div class="totalrow grand"><span>Total à payer</span><span id="ptotal">0</span></div>
            </div>
            <div class="sumwords" id="sumwordswrap" style="display:none"><strong>Arrêtée la présente facture à la somme de :</strong> <span id="psumwords"></span></div>
            <div class="notes"><div class="ptitle">Notes et conditions</div><div class="muted" id="pnotes">Merci pour votre confiance.</div></div>
            <div class="paperfoot"><div class="fl" id="pfooter1" style="display:none"></div><div class="fl" id="pfooter2" style="display:none"></div><div class="fp">Facture créée avec DocsFacile · Vérifiez les mentions légales applicables avant émission.</div></div>
          </article>
        </div>
      </div>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.4/dist/jspdf.plugin.autotable.min.js"></script>
<script>
(()=>{'use strict';const $=id=>document.getElementById(id),form=$('form'),items=$('items'),status=$('status');const now=new Date(),iso=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;$('date').value=iso(now);$('number').value='FAC-'+iso(now).replaceAll('-','')+'-001';

const curr=()=>['XOF','XAF'].includes($('currency').value)?'FCFA':$('currency').value;
const deviseLongue=()=>['XOF','XAF'].includes($('currency').value)?'francs CFA':({EUR:'euros',USD:'dollars américains',GBP:'livres sterling',MAD:'dirhams marocains',GMD:'dalasis gambiens',GNF:'francs guinéens',CAD:'dollars canadiens',CHF:'francs suisses',NGN:'nairas nigérians'})[$('currency').value]||curr();

const round2=n=>Math.round((Number(n)||0)*100)/100;
const fmtNumber=n=>{const num=round2(n);return num.toLocaleString('fr-FR',{minimumFractionDigits:0,maximumFractionDigits:2}).replace(/\u202f|\u00a0/g,' ')};
const money=n=>fmtNumber(n)+' '+curr();

/* ===== CONVERSION MONTANT EN LETTRES (français) ===== */
const unites=['zéro','un','deux','trois','quatre','cinq','six','sept','huit','neuf','dix','onze','douze','treize','quatorze','quinze','seize','dix-sept','dix-huit','dix-neuf'];
const dizaines=['','','vingt','trente','quarante','cinquante','soixante','soixante','quatre-vingt','quatre-vingt'];
function centaineEnLettres(n){n=Math.floor(n);if(n===0)return'';if(n<20)return unites[n];if(n<100){const d=Math.floor(n/10),u=n%10;if(d===7||d===9){if(u===0)return d===7?'soixante-dix':'quatre-vingt-dix';return (d===7?'soixante-':'quatre-vingt-')+unites[10+u]}if(u===0)return dizaines[d]+(d===8?'s':'');if(u===1&&d!==8)return dizaines[d]+'-et-un';return dizaines[d]+'-'+unites[u]}const c=Math.floor(n/100),r=n%100;const ctxt=c===1?'cent':unites[c]+' cent';if(r===0)return ctxt+(c>1?'s':'');return ctxt+' '+centaineEnLettres(r)}
function nombreEnLettres(n){n=Math.round(Number(n)||0);if(n===0)return'zéro';if(n<0)return'moins '+nombreEnLettres(-n);const milliards=Math.floor(n/1e9),millions=Math.floor((n%1e9)/1e6),milliers=Math.floor((n%1e6)/1e3),reste=n%1e3;const parts=[];if(milliards)parts.push(centaineEnLettres(milliards)+(milliards>1?' milliards':' milliard'));if(millions)parts.push(centaineEnLettres(millions)+(millions>1?' millions':' million'));if(milliers)parts.push((milliers===1?'mille':centaineEnLettres(milliers)+' mille'));if(reste)parts.push(centaineEnLettres(reste));return parts.join(' ')}
function montantEnLettres(n,devise){const entier=Math.floor(round2(n));const centimes=Math.round((round2(n)-entier)*100);let txt=nombreEnLettres(entier)+' '+devise;if(centimes>0)txt+=' et '+nombreEnLettres(centimes)+' centimes';return txt}

const datefmt=s=>s?s.split('-').reverse().join('/'):'—';const val=id=>$(id).value.trim();const txt=(id,s,def='')=>$(id).textContent=s||def;const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function addItem(desc='',qty='1',price='0'){const row=document.createElement('div');row.className='item';row.innerHTML=`<div class="itemgrid"><div><label>Description</label><input class="desc" maxlength="180" placeholder="Produit ou service" value="${esc(desc)}"></div><div><label>Qté</label><input class="qty" type="number" min="0.01" step="0.01" value="${esc(qty)}"></div><div><label>Prix unitaire</label><input class="price" type="number" min="0" step="0.01" value="${esc(price)}"></div><button class="remove" type="button" aria-label="Supprimer la ligne">×</button></div>`;row.querySelectorAll('input').forEach(e=>e.addEventListener('input',update));row.querySelector('.remove').addEventListener('click',()=>{if(items.children.length>1)row.remove();else{row.querySelector('.desc').value='';row.querySelector('.qty').value='1';row.querySelector('.price').value='0'}update()});items.appendChild(row);update()}

function calc(){const rows=[...items.querySelectorAll('.item')].map(r=>{const desc=r.querySelector('.desc').value.trim(),q=Math.max(0,Number(r.querySelector('.qty').value)||0),price=Math.max(0,Number(r.querySelector('.price').value)||0);return{desc,q,price,total:round2(q*price)}}).filter(x=>x.desc||x.total>0);const sub=round2(rows.reduce((s,x)=>s+x.total,0)),dr=Math.min(100,Math.max(0,Number($('discount').value)||0)),discount=round2(sub*dr/100),rate=$('taxon').checked?Math.min(100,Math.max(0,Number($('taxrate').value)||0)):0,tax=round2(Math.max(0,sub-discount)*rate/100),total=round2(sub-discount+tax);return{rows,sub,discount,rate,tax,total}}

function update(){const t=calc();
txt('pseller',val('seller'),'Votre entreprise');txt('psellerblock',val('seller'),'Votre entreprise');
txt('psellerdetails',[val('phone'),val('email')].filter(Boolean).join(' · '),'Coordonnées de votre entreprise');
txt('paddress',val('address'));txt('ptaxid',val('taxid')?'Identifiant : '+val('taxid'):'');
txt('pclient',val('client'),'Nom du client');txt('pclientaddress',val('clientaddress'));
txt('pclientdetails',[val('clientphone'),val('clientemail')].filter(Boolean).join(' · '));
txt('pnumber','N° '+(val('number')||'—'));txt('pdate',datefmt(val('date')));txt('pdue',datefmt(val('due')));
txt('pref',val('reference'),'—');$('prefwrap').style.display=val('reference')?'':'none';
const tbody=$('pitems');tbody.replaceChildren();
(t.rows.length?t.rows:[{desc:'Votre prestation',q:1,price:0,total:0}]).forEach(i=>{const tr=document.createElement('tr');[i.desc||'Prestation',String(i.q),money(i.price),money(i.total)].forEach((v,n)=>{const td=document.createElement('td');td.textContent=v;if(n)td.className='num';tr.appendChild(td)});tbody.appendChild(tr)});
txt('psub',money(t.sub));txt('pdiscount','− '+money(t.discount));$('pdiscountrow').style.display=t.discount?'':'none';
$('ptaxrow').style.display=$('taxon').checked?'':'none';txt('ptaxlabel','Taxe ('+t.rate+' %)');txt('ptax',money(t.tax));txt('ptotal',money(t.total));
txt('pnotes',val('notes'),'Merci pour votre confiance.');
if(t.total>0){$('sumwordswrap').style.display='';$('psumwords').textContent=montantEnLettres(t.total,deviseLongue())+'.'}else{$('sumwordswrap').style.display='none'}
txt('pfooter1',val('footerline1'));$('pfooter1').style.display=val('footerline1')?'':'none';
txt('pfooter2',val('footerline2'));$('pfooter2').style.display=val('footerline2')?'':'none';
status.textContent=''}

$('add').addEventListener('click',()=>addItem());$('taxon').addEventListener('change',()=>{$('taxrate').disabled=!$('taxon').checked;update()});form.querySelectorAll('input,textarea,select').forEach(e=>e.addEventListener('input',update));$('print').addEventListener('click',()=>window.print());
$('clear').addEventListener('click',()=>{if(!confirm('Effacer toutes les informations de cette facture ? Cette action est irréversible.'))return;form.reset();items.replaceChildren();$('date').value=iso(new Date());$('number').value='FAC-'+iso(new Date()).replaceAll('-','')+'-001';$('taxrate').disabled=true;addItem();update();status.textContent='Toutes les informations saisies ont été effacées.'});

$('pdf').addEventListener('click',()=>{if(!form.reportValidity())return;if(!window.jspdf?.jsPDF){status.textContent='La bibliothèque PDF ne s’est pas chargée. Vérifiez votre connexion Internet et réessayez.';return}const t=calc();if(!t.rows.length){status.textContent='Ajoutez au moins un produit ou service.';return}const btn=$('pdf');btn.disabled=true;btn.textContent='Création du PDF…';try{
const{jsPDF}=window.jspdf,doc=new jsPDF({unit:'mm',format:'a4',compress:true}),green=[46,125,50],navy=[31,78,121],muted=[92,108,124],w=doc.internal.pageSize.getWidth(),m=16;
const fmt=n=>fmtNumber(n)+' '+curr();
let y=20;doc.setFillColor(...green);doc.rect(0,0,w,4,'F');
doc.setFont('helvetica','bold');doc.setTextColor(...green);doc.setFontSize(20);doc.text(val('seller')||'Entreprise',m,y);
doc.setTextColor(...navy);doc.setFontSize(24);doc.text('FACTURE',w-m,y,{align:'right'});y+=9;
doc.setFont('helvetica','normal');doc.setFontSize(9);doc.setTextColor(...muted);
const seller=[val('address'),val('phone'),val('email'),val('taxid')?'Identifiant : '+val('taxid'):''].filter(Boolean).join('\n'),sl=doc.splitTextToSize(seller||' ',w/2-m-3);doc.text(sl,m,y);
doc.setTextColor(...navy);doc.setFont('helvetica','bold');doc.text('N° '+(val('number')||'—'),w-m,y,{align:'right'});
let py=y+Math.max(sl.length*4,8)+8;doc.setDrawColor(...green);doc.line(m,py,w-m,py);py+=8;
doc.setFontSize(8);doc.setTextColor(...green);doc.text('FACTURÉ À',w/2,py);
doc.setFontSize(11);doc.setTextColor(...navy);doc.text(val('client'),w/2,py+6);
doc.setFont('helvetica','normal');doc.setFontSize(9);doc.setTextColor(...muted);
const cl=[val('clientaddress'),val('clientphone'),val('clientemail')].filter(Boolean).join('\n');doc.text(doc.splitTextToSize(cl||' ',w/2-m-3),w/2,py+11);
doc.setTextColor(...navy);doc.text('Date : '+datefmt(val('date'))+'    Échéance : '+datefmt(val('due')),m,py+6);
if(val('reference'))doc.text('Référence : '+val('reference'),m,py+12);
doc.autoTable({startY:py+25,head:[['Description','Qté','Prix unitaire','Total']],body:t.rows.map(i=>[i.desc||'Prestation',String(i.q),fmt(i.price),fmt(i.total)]),margin:{left:m,right:m},styles:{font:'helvetica',fontSize:8,cellPadding:3,overflow:'linebreak',textColor:navy},headStyles:{fillColor:navy,textColor:[255,255,255]},alternateRowStyles:{fillColor:[246,249,251]},columnStyles:{1:{halign:'right',cellWidth:18},2:{halign:'right',cellWidth:32},3:{halign:'right',cellWidth:32}},didDrawPage:()=>{doc.setFillColor(...green);doc.rect(0,0,w,3,'F')}});
let ty=doc.lastAutoTable.finalY+9;const lines=[['Sous-total',fmt(t.sub)]];if(t.discount)lines.push(['Remise ('+Number($('discount').value||0)+' %)','− '+fmt(t.discount)]);if($('taxon').checked)lines.push(['Taxe ('+t.rate+' %)',fmt(t.tax)]);lines.push(['TOTAL À PAYER',fmt(t.total)]);const tw=76,x=w-m-tw;
if(ty+lines.length*7>245){doc.addPage();ty=20}
lines.forEach((r,i)=>{if(i===lines.length-1){doc.setFillColor(...green);doc.rect(x,ty-5,tw,10,'F');doc.setTextColor(255,255,255);doc.setFont('helvetica','bold')}else{doc.setTextColor(...navy);doc.setFont('helvetica','normal')}doc.setFontSize(i===lines.length-1?10:9);doc.text(r[0],x+2,ty);doc.text(r[1],w-m-2,ty,{align:'right'});ty+=7});
if(t.total>0){ty+=7;if(ty>245){doc.addPage();ty=20}const phrase='Arrêtée la présente facture à la somme de : '+montantEnLettres(t.total,deviseLongue())+'.';doc.setFont('helvetica','bold');doc.setFontSize(9);const wrapped=doc.splitTextToSize(phrase,w-2*m-8);const boxH=wrapped.length*4.5+6;doc.setFillColor(243,247,246);doc.rect(m,ty-4,w-2*m,boxH,'F');doc.setDrawColor(...green);doc.setLineWidth(0.8);doc.line(m,ty-4,m,ty-4+boxH);doc.setTextColor(31,78,121);doc.text(wrapped,m+4,ty+1);ty+=boxH}
if(val('notes')){ty+=8;if(ty>250){doc.addPage();ty=20}doc.setTextColor(...green);doc.setFont('helvetica','bold');doc.setFontSize(9);doc.text('NOTES ET CONDITIONS',m,ty);doc.setTextColor(...muted);doc.setFont('helvetica','normal');doc.text(doc.splitTextToSize(val('notes'),w-2*m),m,ty+6)}
const f1=val('footerline1'),f2=val('footerline2');const footerLines=[];if(f1)footerLines.push(f1);if(f2)footerLines.push(f2);const totalPages=doc.getNumberOfPages();const pageH=doc.internal.pageSize.getHeight();
for(let p=1;p<=totalPages;p++){doc.setPage(p);let fy=pageH-8;doc.setFontSize(7);doc.setTextColor(...muted);doc.setFont('helvetica','normal');doc.text('Créée avec DocsFacile · '+p+'/'+totalPages,w/2,fy,{align:'center'});if(footerLines.length){fy-=7;doc.setDrawColor(200,210,220);doc.setLineWidth(0.2);doc.line(m,fy,w-m,fy);fy-=5;doc.setFontSize(8);for(let i=footerLines.length-1;i>=0;i--){doc.setTextColor(40,50,60);doc.setFont('helvetica','bold');doc.text(footerLines[i],w/2,fy,{align:'center',maxWidth:w-2*m});fy-=5}}}
const name=(val('number')||'facture').replace(/[^A-Za-z0-9_-]+/g,'-').replace(/^-+|-+$/g,'').slice(0,60)||'facture';doc.save(name+'.pdf');status.textContent='Facture PDF générée. Cette page n’a enregistré aucune donnée de facture.'
}catch(e){console.error(e);status.textContent='Une erreur est survenue pendant la création du PDF. Réessayez.'}finally{btn.disabled=false;btn.textContent='↓ Télécharger la facture PDF'}});
addItem('Prestation ou produit','1','0');update()})();
</script>
<?php foot(); ?>