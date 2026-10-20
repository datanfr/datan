<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('Errormanager::error404');

$routes->get('/', 'Home::index');

// Keep the temporary CI4 database smoke-test route.
$routes->get('ping', 'Ping::index');

// ADMIN
$routes->add('admin', 'Admin::index');
$routes->add('admin/amendements', 'Admin::amendements');
$routes->add('admin/amendements/review', 'Admin::amendements_review');
$routes->add('admin/votes', 'Admin::votes');
$routes->add('admin/elections/modifications-mps', 'Admin::election_modifications_mps');
$routes->add('admin/elections/(:segment)', 'Admin::election_candidates/$1');
$routes->add('admin/elections/(:segment)/non-renseignes', 'Admin::election_candidates_not_done/$1');
$routes->add('admin/elections/candidat/create', 'Admin::create_candidat');
$routes->add('admin/elections/candidat/modify/(:segment)', 'Admin::modify_candidat/$1');
$routes->add('admin/elections/candidat/delete/(:segment)', 'Admin::delete_candidat/$1');
$routes->add('admin/votes/create', 'Admin::create_vote');
$routes->add('admin/votes/modify/(:segment)', 'Admin::modify_vote/$1');
$routes->add('admin/votes/delete/(:segment)', 'Admin::delete_vote/$1');
$routes->add('admin/socialmedia/historique/(:segment)', 'Admin::socialmedia/historique/$1');
$routes->add('admin/socialmedia/(:segment)', 'Admin::socialmedia/$1/NULL');
$routes->add('admin/faq', 'Admin::faq_list');
$routes->add('admin/faq/create', 'Admin::create_faq');
$routes->add('admin/faq/modify/(:segment)', 'Admin::modify_faq/$1');
$routes->add('admin/faq/delete/(:segment)', 'Admin::delete_faq/$1');
$routes->add('admin/quizz', 'Admin::quizz_list');
$routes->add('admin/quizz/create', 'Admin::create_quizz');
$routes->add('admin/quizz/modify/(:segment)', 'Admin::modify_quizz/$1');
$routes->add('admin/quizz/delete/(:segment)', 'Admin::delete_quizz/$1');
$routes->add('admin/parrainages', 'Admin::parrainages');
$routes->add('admin/parrainages/modify/(:segment)', 'Admin::modify_parrainage/$1');
$routes->add('admin/exposes', 'Admin::exposes');
$routes->add('admin/exposes/modify/(:segment)', 'Admin::exposes_modify/$1');
$routes->add('admin/campagnes', 'Admin::campaigns_list');
$routes->add('admin/campagnes/create', 'Admin::create_campaign');
$routes->add('admin/campagnes/delete/(:segment)', 'Admin::delete_campaign/$1');
$routes->add('admin/campagnes/edit/(:segment)', 'Admin::edit_campaign/$1');
$routes->add('admin/campagnes/toggle', 'Admin::toggle_campaign_active');
$routes->add('admin/api-keys', 'Admin::api_keys');
$routes->add('admin/api-keys/create', 'Admin::api_keys_create');
$routes->add('admin/api-keys/revoke/(:num)', 'Admin::api_keys_revoke/$1');

// MpDashboard
$routes->add('dashboard', 'DashboardMP::index');
$routes->add('dashboard/elections/(:segment)', 'DashboardMP::elections/$1');
$routes->add('dashboard/elections/(:segment)/modifier', 'DashboardMP::elections_modify/$1');
$routes->add('dashboard/explications', 'DashboardMP::explications');
$routes->add('dashboard/explications/liste', 'DashboardMP::explications_liste');
$routes->add('dashboard/explications/create/l(:segment)v(:segment)', 'DashboardMP::explications_create/$1/$2');
$routes->add('dashboard/explications/modify/l(:segment)v(:segment)', 'DashboardMP::explications_modify/$1/$2');
$routes->add('dashboard/explications/delete/l(:segment)v(:segment)', 'DashboardMP::explications_delete/$1/$2');
$routes->add('dashboard/iframe', 'DashboardMP::generate_iframe');

// USERS
$routes->add('mon-compte', 'Users::compte');
$routes->add('mon-compte/modifier-donnees-personnelles', 'Users::modify_personal_data');
$routes->add('mon-compte/modifier-password', 'Users::modify_password');
$routes->add('mon-compte/supprimer-compte', 'Users::delete_account');
$routes->add('mon-compte/supprimer-compte/confirmed', 'Users::delete_account_confirmed');

// SEO
$routes->add('sitemap.xml', 'Sitemap::index');
$routes->add('sitemap-deputes-1.xml', 'Sitemap::deputes');
$routes->add('sitemap-deputes-inactifs-1.xml', 'Sitemap::deputes_inactifs');
$routes->add('sitemap-groupes-1.xml', 'Sitemap::groupes');
$routes->add('sitemap-groupes-inactifs-1.xml', 'Sitemap::groupes_inactifs');
$routes->add('sitemap-votes-1.xml', 'Sitemap::votes');
$routes->add('sitemap-localites-d-1.xml', 'Sitemap::departements');
$routes->add('sitemap-localites-v-1.xml', 'Sitemap::communes');
$routes->add('sitemap-elections-1.xml', 'Sitemap::elections');
$routes->add('sitemap-elections-d-1.xml', 'Sitemap::electionsDepartements');
$routes->add('sitemap-elections-v-1.xml', 'Sitemap::electionsCommunes');
$routes->add('sitemap-structure-1.xml', 'Sitemap::structure');
$routes->add('sitemap-categories-1.xml', 'Sitemap::categories');
$routes->add('sitemap-posts-1.xml', 'Sitemap::posts');
$routes->add('sitemap-partis-politiques-1.xml', 'Sitemap::parties');

// Outils
$routes->add('outils/coalition-simulateur', 'Outils::coalition');

// Iframe
$routes->add('iframe', 'Iframe::index');
$routes->add('iframe/depute/(:segment)', 'Iframe::show_depute_iframe/$1');

// COMMISSION
$routes->add('commissions', 'Commissions::index');

// GROUPES
$routes->add('groupes/legislature-(:segment)/(:segment)/statistiques', 'Groupes::individual_stats/$1/$2');
$routes->add('groupes/legislature-(:segment)/(:segment)/membres', 'Groupes::individual_membres/$1/$2');
$routes->add('groupes/legislature-(:segment)/(:segment)/votes', 'Groupes::individual_votes_datan/$1/$2');
$routes->add('groupes/legislature-(:segment)/(:segment)/votes/all', 'Groupes::individual_votes_all/$1/$2');
$routes->add('groupes/legislature-(:segment)/(:segment)', 'Groupes::individual/$1/$2');
$routes->add('groupes/inactifs', 'Groupes::inactifs');
$routes->add('groupes/legislature-(:segment)', 'Groupes::index/$1');
$routes->add('groupes', 'Groupes::index');

// PARTIS POLITIQUES
$routes->add('partis-politiques/(:segment)', 'Parties::individual/$1');
$routes->add('partis-politiques', 'Parties::index');

// DEPUTES
// The CI3 deputes/?commune=... entry matched a query string, not a URI route.
$routes->add('deputes/inactifs', 'Deputes::inactifs');
$routes->add('deputes/legislature-(:segment)', 'Deputes::index/$1');
$routes->add('deputes/(:segment)', 'Departement::view/$1');
$routes->add('deputes/(:segment)/depute_(:segment)/legislature-(:segment)', 'Deputes::historique/$2/$1/$3');
$routes->add('deputes/(:segment)/depute_(:segment)/votes', 'Deputes::votes/$2/$1');
$routes->add('deputes/(:segment)/depute_(:segment)', 'Deputes::individual/$2/$1');
$routes->add('deputes/(:segment)/ville_(:segment)', 'City::index/$2/$1');
$routes->add('deputes', 'Deputes::index');

// DEPARTEMENTS
$routes->add('index_departements', 'Departement::liste');

// VOTES
$routes->add('votes/legislature-(:segment)/vote_c(:segment)', 'Votes::individual/$1/$2/cong');
$routes->add('votes/legislature-(:segment)/vote_(:segment)', 'Votes::individual/$1/$2');
$routes->add('votes/legislature-(:segment)/vote_c(:segment)/explication_(:segment)', 'Votes::individual/$1/$2/cong/$3');
$routes->add('votes/legislature-(:segment)/vote_(:segment)/explication_(:segment)', 'Votes::individual/$1/$2/false/$3');
$routes->add('votes/legislature-(:segment)', 'Votes::all/$1');
$routes->add('votes/decryptes/(:segment)', 'Votes::field/$1');
$routes->add('votes/decryptes', 'Votes::decryptes//');
$routes->add('votes/legislature-(:segment)/(:segment)/(:segment)', 'Votes::all/$1/$2/$3');
$routes->add('votes/legislature-(:segment)/(:segment)', 'Votes::all/$1/$2');
$routes->add('votes', 'Votes::index');

// UPLOAD
$routes->add('upload/image', 'Upload::image');

// POSTS
$routes->add('posts/update', 'Posts::update');
$routes->add('posts/create', 'Posts::create');
$routes->add('posts/edit/(:segment)', 'Posts::edit/$1');
$routes->add('posts/delete/(:segment)', 'Posts::delete/$1');
$routes->add('blog/categorie/(:segment)', 'Posts::category/$1');
$routes->add('blog/(:segment)/(:segment)', 'Posts::view/$2/$1');
$routes->add('blog', 'Posts::index');

// STATISTIQUES
$routes->add('statistiques', 'Stats::index');
$routes->add('statistiques/aide', 'Pages::view/statistiques');
$routes->add('statistiques/(:segment)', 'Stats::individual/$1');

// QUESTIONNAIRE
$routes->add('questionnaire', 'Quiz::index');
$routes->post('questionnaire/resultat', 'Quiz::result');

// ELECTIONS
$routes->add('elections', 'Elections::index');
$routes->add('elections/(:segment)', 'Elections::individual/$1');
$routes->add('elections/resultats/(:segment)/ville_(:segment)', 'Elections::results_city/$1/$2');
$routes->add('elections/resultats/(:segment)', 'Elections::results_dpt/$1');

// NEWSLETTER
$routes->add('newsletter/edit/(:segment)', 'Newsletter::edit/$1');
$routes->add('newsletter/update', 'Newsletter::update');
$routes->add('newsletter/delete/(:segment)', 'Newsletter::delete/$1');
$routes->add('newsletter/votes', 'Newsletter::votes/$1');
$routes->add('newsletter/votes/(:segment)', 'Newsletter::votes/$1');
$routes->add('newsletter', 'Newsletter::register');

// PARRAINAGES
$routes->add('parrainages-2022', 'Parrainages::index');

// FAQ
$routes->add('faq', 'Faq::index');

// API (secured with API key)
// Tables SQL autorisees
$routes->add('api/tables', 'api\Tables::index');
$routes->add('api/tables/meta', 'api\Tables::meta');
$routes->add('api/tables/(:segment)', 'api\Tables::index/$1');
// Votes bruts (votes_info)
$routes->add('api/votes', 'api\Votes::index');
$routes->add('api/votes/meta', 'api\Votes::meta');
$routes->add('api/votes/(:segment)', 'api\Votes::index/$1');
// Votes décryptés (votes_datan)
$routes->add('api/decrypted_votes', 'api\Decrypted_votes::index');
$routes->add('api/decrypted_votes/meta', 'api\Decrypted_votes::meta');
$routes->add('api/decrypted_votes/(:num)', 'api\Decrypted_votes::index/$1');
// Votes non décryptés
$routes->add('api/non_decrypted_votes', 'api\Non_decrypted_votes::index');
$routes->add('api/non_decrypted_votes/meta', 'api\Non_decrypted_votes::meta');
$routes->add('api/non_decrypted_votes/(:segment)', 'api\Non_decrypted_votes::index/$1');
// Résumés IA des amendements (écriture depuis PoliticAnalysis)
$routes->add('api/amendements_ia', 'api\Amendements_ia::index');
// Exposés des motifs
$routes->add('api/exposes', 'api\Exposes::index');
$routes->add('api/exposes/meta', 'api\Exposes::meta');
$routes->add('api/exposes/stats', 'api\Exposes::stats');
$routes->add('api/exposes/by_vote/(:num)/(:num)', 'api\Exposes::by_vote/$1/$2');
$routes->add('api/exposes/(:num)', 'api\Exposes::index/$1');

// API (public)
$routes->add('api/newsletter/create_newsletter', 'Newsletter::create');

// LOGIN & REGISTER
$routes->add('login', 'Users::login');
$routes->add('register/(:segment)', 'Users::register/$1');
$routes->add('register', 'Users::register');
$routes->add('logout', 'Users::logout');
$routes->add('password', 'Users::password_lost_request');
$routes->add('password/(:segment)', 'Users::password_lost_change/$1');
$routes->add('demande-compte-depute', 'Users::demande_mp');

// REDIRECTION
$routes->add('classements', 'Redirection::redir/statistiques');

// CACHE
$routes->add('cache/delete_all', 'Cache::delete_all');
$routes->add('cache/clear_cli', 'Cache::clear_cli');

// LOGS
$routes->add('admin/logs', 'LogViewerController::index');
$routes->add('admin/logs-scripts/(:segment)', 'Logs::index/$1');

// EXPORT
$routes->add('export/set_session/(:segment)', 'Export::set_session/$1');

// SEARCH
$routes->add('search_api', 'Search::index_api');
$routes->add('recherche/(:segment)', 'Search::index/$1');

// PAGES
$routes->add('(:segment)', 'Pages::view/$1');

// HOMEPAGE
$routes->add('campaign/current_active_campaigns', 'Campaign::current_active_campaigns');

// 404 PAGE
$routes->add('(:any)', 'Errormanager::error404');
