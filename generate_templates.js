const {Document,Packer,Paragraph,TextRun,AlignmentType,BorderStyle,ShadingType}=require('docx');
const fs=require('fs');
// [couleur, police, alignement en-tête, style des titres]
const V=[["1F4E79","Calibri","left","line"],["2E7D32","Arial","left","band"],["B71C1C","Georgia","center","line"],["000000","Times New Roman","center","plain"],["6A1B9A","Verdana","left","band"],["00695C","Calibri","center","line"],["E65100","Arial","left","line"],["263238","Cambria","left","band"],["0277BD","Tahoma","center","band"],["880E4F","Georgia","left","plain"],["37474F","Calibri","left","line"],["F9A825","Arial","center","band"],["1B5E20","Cambria","center","line"],["4527A0","Verdana","left","line"],["212121","Arial","center","band"]];
const S={"Profil":["[2 à 3 lignes présentant votre parcours et votre objectif professionnel.]"],
"Expérience professionnelle":["[Poste] – [Entreprise], [Ville] ([Début] – [Fin])","[Mission ou réalisation chiffrée]","[Poste] – [Entreprise], [Ville] ([Début] – [Fin])","[Mission ou réalisation chiffrée]"],
"Formation":["[Diplôme] – [École / Université], [Ville] ([Année])","[Diplôme] – [Établissement] ([Année])"],
"Compétences":["[Compétence 1] • [Compétence 2] • [Compétence 3]"],
"Langues":["Français : [niveau] • Anglais : [niveau] • [Autre] : [niveau]"],
"Centres d'intérêt":["[Sport, bénévolat, lecture…]"]};
V.forEach(([c,f,al,st],i)=>{
 const A=al==="center"?AlignmentType.CENTER:AlignmentType.LEFT;
 const t=(x,o={})=>new TextRun({text:x,font:f,...o});
 const k=[new Paragraph({alignment:A,children:[t("[PRÉNOM NOM]",{bold:true,size:44,color:c})]}),
  new Paragraph({alignment:A,children:[t("[Intitulé du poste recherché]",{size:26,color:"555555"})]}),
  new Paragraph({alignment:A,spacing:{after:200},children:[t("[Téléphone] | [Email] | [Ville, Pays]",{size:20})]})];
 for(const [h,l] of Object.entries(S)){
  const o={spacing:{before:240,after:100},children:[t(h.toUpperCase(),{bold:true,size:24,color:st==="band"?"FFFFFF":c})]};
  if(st==="line")o.border={bottom:{style:BorderStyle.SINGLE,size:8,color:c,space:2}};
  if(st==="band")o.shading={type:ShadingType.CLEAR,fill:c,color:"auto"};
  k.push(new Paragraph(o));
  l.forEach(x=>k.push(new Paragraph({spacing:{after:60},children:[t(x,{size:22})]})));
 }
 const d=new Document({sections:[{properties:{page:{size:{width:11906,height:16838},margin:{top:900,bottom:900,left:1000,right:1000}}},children:k}]});
 Packer.toBuffer(d).then(b=>fs.writeFileSync(`storage/templates/modele-${String(i+1).padStart(2,"0")}.docx`,b));
});
