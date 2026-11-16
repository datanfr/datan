// Régénère le CSS critique : assets/styles/critical/<page>[-mobile].css.
//
//   BASE_URL=http://127.0.0.1:8000 npm run critique
//
// Remplace la tâche `critical` du Gruntfile du legacy (grunt-critical, donc
// Penthouse), dont il reprend les pages, les adresses témoins et les
// dimensions de fenêtre à l'identique. Il faut un site qui tourne, avec une
// base garnie : l'extrait se mesure sur une vraie page, rendue dans un vrai
// navigateur (Playwright).
//
// À relancer quand le haut de l'une de ces trois pages change de mise en page,
// ou que main.scss touche à ce qui s'y affiche. Un extrait périmé ne casse
// rien — main.css arrive juste après et a le dernier mot —, mais la page
// « saute » au moment où elle s'applique.
//
// Pourquoi pas le paquet `critical` d'aujourd'hui (v9) : son moteur de rendu ne
// garde une règle que si l'un de ses éléments a une boîte dans la fenêtre. Un
// élément masqué (`.d-none`, une modale `.fade:not(.show)`, un menu replié)
// n'a pas de boîte : la règle qui le masque sortait de l'extrait, et
// l'élément s'affichait jusqu'à l'arrivée de main.css. Penthouse les gardait ;
// ici, un élément non rendu compte s'il se trouve DANS un ancêtre affiché au
// premier écran.

import { chromium } from 'playwright';
import { transform } from 'lightningcss';
import { writeFile } from 'node:fs/promises';

// Sans cibles, lightningcss réécrit les media queries en syntaxe d'intervalle
// (`width<=575.98px`), qu'iOS ne lit qu'à partir de Safari 16.4 : l'extrait y
// perdrait toutes ses règles conditionnelles. Safari 15 suffit à garder
// l'ancienne écriture ; viser plus bas ajouterait des préfixes -webkit-
// (+30 % de poids) pour des navigateurs qui ne voient plus le site.
const CIBLES = { safari: 15 << 16, ios_saf: 15 << 16, chrome: 100 << 16, firefox: 100 << 16, edge: 100 << 16 };

const BASE_URL = (process.env.BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const DOSSIER = new URL('../assets/styles/critical/', import.meta.url);

// Nom de l'extrait (le bloc `css_critique` du gabarit), adresse témoin, et
// fenêtres : la grande, puis la mobile (375 px), comme le Gruntfile.
const PAGES = [
  { nom: 'index', chemin: '/', bureau: [1200, 1800], mobile: [375, 1800] },
  { nom: 'city', chemin: '/deputes/ille-et-vilaine-35/ville_rennes', bureau: [1200, 1350], mobile: [375, 812] },
  { nom: 'groupe_individual', chemin: '/groupes/legislature-17/rn', bureau: [1200, 1350], mobile: [375, 1700] },
];

async function lire(url) {
  const reponse = await fetch(url);
  if (!reponse.ok) {
    throw new Error(`${url} : HTTP ${reponse.status}`);
  }
  return reponse.text();
}

// S'exécute DANS le navigateur. Rend le texte des règles de la feuille
// `#css-critique-source` qui touchent au premier écran.
function extraire({ largeur, hauteur }) {
  const auPremierEcran = (element) => {
    // Un élément sans boîte (display: none) se juge par son plus proche
    // ancêtre affiché : c'est là qu'il apparaîtrait si la règle manquait.
    let e = element;
    while (e && e.getClientRects().length === 0) {
      e = e.parentElement;
    }
    if (!e) {
      return false;
    }
    const r = e.getBoundingClientRect();
    return r.top < hauteur && r.bottom >= 0 && r.left < largeur && r.right >= 0;
  };

  // Pseudo-classes et pseudo-éléments retirés : `:hover`, `::before` ou
  // `:not(.show)` ne disent rien de la position de l'élément.
  const nu = (selecteur) => selecteur.replace(/::?[\w-]+(\([^)]*\))?/g, '').trim() || '*';

  const selecteurUtile = (selecteur) => {
    let elements;
    try {
      elements = document.querySelectorAll(nu(selecteur));
    } catch {
      return true; // sélecteur que le retrait a rendu invalide (`.a>`) : gardé
    }
    for (const el of elements) {
      if (auPremierEcran(el)) {
        return true;
      }
    }
    return false;
  };

  const presentDansLaPage = (selecteur) => {
    try {
      return document.querySelector(nu(selecteur)) !== null;
    } catch {
      return false;
    }
  };

  // Les déclarations se relisent propriété détaillée par propriété détaillée,
  // et non par `style.cssText` : Chromium y recompose les raccourcis, et se
  // trompe sur ceux à plusieurs calques — `background: dégradé, url(…)` suivi
  // de `background-size: cover` ressortait `dégradé 50% / cover, url(…)`, le
  // cover appliqué au seul dégradé. L'hémicycle de la page ville perdait son
  // cadrage jusqu'à l'arrivée de main.css.
  const declarations = (style) => {
    const liste = [];
    for (let i = 0; i < style.length; i++) {
      const nom = style[i];
      const important = style.getPropertyPriority(nom) ? ' !important' : '';
      liste.push(`${nom}:${style.getPropertyValue(nom)}${important}`);
    }
    return liste.join(';');
  };

  const parcourir = (regles) => {
    const gardees = [];
    for (const regle of regles) {
      if (regle instanceof CSSStyleRule) {
        const selecteurs = regle.selectorText.split(',').map((s) => s.trim());
        const utiles = selecteurs.filter(selecteurUtile);
        if (utiles.length) {
          gardees.push(`${utiles.join(',')}{${declarations(regle.style)}}`);
        }

        // Un repère de positionnement se garde où qu'il soit dans la page.
        // Une règle retenue pour un élément du premier écran s'applique à
        // TOUS ceux qu'elle désigne, plus bas compris : sans conséquence, sauf
        // pour un élément en `position: absolute` dont l'ancêtre a perdu son
        // `position: relative` — il s'ancre alors sur <body> et remonte couvrir
        // le premier écran. C'est arrivé sur l'accueil : le `::after` de
        // `.stretched-link` (inset: 0), gardé pour les cartes de vote, valait
        // aussi pour les trois articles de blog, dont le `.bloc-post
        // {position: relative}` était écarté. Trois calques invisibles de la
        // taille de la page, qui regagnaient leur carte à l'arrivée de
        // main.css : rien ne bougeait à l'œil, et Lighthouse comptait un
        // décalage de mise en page de 1,0 — le maximum, 25 points de note.
        const position = regle.style.getPropertyValue('position');
        if (['relative', 'absolute', 'fixed', 'sticky'].includes(position)) {
          const reperes = selecteurs.filter((s) => !utiles.includes(s) && presentDansLaPage(s));
          if (reperes.length) {
            const important = regle.style.getPropertyPriority('position') ? ' !important' : '';
            gardees.push(`${reperes.join(',')}{position:${position}${important}}`);
          }
        }
      } else if (regle instanceof CSSFontFaceRule || regle instanceof CSSKeyframesRule) {
        gardees.push(regle.cssText);
      } else if (regle.cssRules) {
        // @media, @supports : gardés entiers quelle que soit la largeur, dès
        // qu'une règle y sert. L'extrait « grand écran » couvre aussi les
        // tablettes (media min-width: 481px), qu'aucune mesure ne voit.
        const interieur = parcourir(regle.cssRules);
        if (interieur.length) {
          gardees.push(`${regle.cssText.slice(0, regle.cssText.indexOf('{'))}{${interieur.join('')}}`);
        }
      }
    }
    return gardees;
  };

  return parcourir(document.getElementById('css-critique-source').sheet.cssRules).join('\n');
}

const navigateur = await chromium.launch();
try {
  for (const page of PAGES) {
    const adresse = BASE_URL + page.chemin;
    const html = await lire(adresse);

    // L'extrait ne se tire que de main.css, comme dans le Gruntfile
    // (`css: ['assets/css/main.css']`) : les feuilles propres à une page
    // (flickity, circle) restent chargées à part, et l'extrait déjà en place ne
    // doit pas se recopier lui-même. Toutes les feuilles liées sont donc
    // retirées, et main.css, telle que le serveur la compile, mise à leur place.
    //
    // CSS_URL permet de prendre main.css ailleurs que sur le site mesuré : le
    // HTML d'un site aux données complètes (la production), avec la feuille
    // compilée sur le poste.
    const lienMain = html.match(/href="([^"]*\/styles\/main-[^"]+\.css)"/);
    if (!process.env.CSS_URL && !lienMain) {
      throw new Error(`${page.chemin} : aucun lien vers main.css dans la page (passer CSS_URL)`);
    }
    const css = await lire(process.env.CSS_URL ?? new URL(lienMain[1], BASE_URL).href);
    const nue = html
      .replace(/<noscript>\s*<link[^>]*rel="stylesheet"[^>]*>\s*<\/noscript>/g, '')
      .replace(/<link[^>]*rel="stylesheet"[^>]*>/g, '')
      .replace('</head>', `<style id="css-critique-source">${css}</style></head>`);

    for (const [suffixe, [largeur, hauteur]] of [['', page.bureau], ['-mobile', page.mobile]]) {
      const onglet = await navigateur.newPage({ viewport: { width: largeur, height: hauteur } });
      // La page est servie à sa vraie adresse — images et polices se
      // résolvent contre le site —, mais avec le HTML retouché. Les scripts
      // externes sont bloqués, comme le faisait Penthouse : l'extrait s'applique
      // avant qu'aucun n'ait tourné, c'est ce DOM-là qu'il doit couvrir.
      await onglet.route(adresse, (requete) => requete.fulfill({ body: nue, contentType: 'text/html; charset=utf-8' }));
      await onglet.route('**/*', (requete) => (requete.request().resourceType() === 'script' ? requete.abort() : requete.fallback()));
      await onglet.goto(adresse, { waitUntil: 'load' });

      const brut = await onglet.evaluate(extraire, { largeur, hauteur });
      await onglet.close();

      const { code } = transform({ filename: `${page.nom}.css`, code: Buffer.from(brut), minify: true, targets: CIBLES });
      await writeFile(new URL(`${page.nom}${suffixe}.css`, DOSSIER), code);
      console.log(`${page.nom}${suffixe}.css : ${code.length} octets (${largeur}×${hauteur})`);
    }
  }
} finally {
  await navigateur.close();
}
