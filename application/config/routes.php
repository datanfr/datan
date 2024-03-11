<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// ADMIN
$route['admin'] = 'admin/index';
$route['admin/votes'] = 'admin/votes';
$route['admin/elections/modifications-mps'] = 'admin/election_modifications_mps';
$route['admin/elections/(:any)'] = 'admin/election_candidates/$1';
$route['admin/elections/(:any)/non-renseignes'] = 'admin/election_candidates_not_done/$1';
$route['admin/elections/candidat/create'] = 'admin/create_candidat';
$route['admin/elections/candidat/modify/(:any)'] = 'admin/modify_candidat/$1';
$route['admin/elections/candidat/delete/(:any)'] = 'admin/delete_candidat/$1';
$route['admin/votes/create'] = 'admin/create_vote';
$route['admin/votes/modify/(:any)'] = 'admin/modify_vote/$1';
$route['admin/votes/delete/(:any)'] = 'admin/delete_vote/$1';
$route['admin/votes_an/position'] = 'admin/votes_an_position';
$route['admin/votes_an/cohesion'] = 'admin/votes_an_cohesion';
$route['admin/votes_an/majo_lost'] = 'admin/votes_an_majo_lost';
$route['admin/analyses/(:any)'] = 'admin/analyses/$1';
$route['admin/socialmedia/historique/(:any)'] = 'admin/socialmedia/historique/$1';
$route['admin/socialmedia/(:any)'] = 'admin/socialmedia/$1/NULL';
$route['admin/faq'] = 'admin/faq_list';
$route['admin/faq/create'] = 'admin/create_faq';
$route['admin/faq/modify/(:any)'] = 'admin/modify_faq/$1';
$route['admin/faq/delete/(:any)'] = 'admin/delete_faq/$1';
$route['admin/quizz'] = 'admin/quizz_list';
$route['admin/quizz/create'] = 'admin/create_quizz';
$route['admin/quizz/modify/(:any)'] = 'admin/modify_quizz/$1';
$route['admin/quizz/delete/(:any)'] = 'admin/delete_quizz/$1';
$route['admin/parrainages'] = 'admin/parrainages';
$route['admin/parrainages/modify/(:any)'] = 'admin/modify_parrainage/$1';
$route['admin/exposes'] = 'admin/exposes';
$route['admin/exposes/modify/(:any)'] = 'admin/exposes_modify/$1';
// MpDashboard
$route['dashboard'] = 'dashboardMP/index';
$route['dashboard/elections/(:any)'] = 'dashboardMP/elections/$1';
$route['dashboard/elections/(:any)/modifier'] = 'dashboardMP/elections_modify/$1';
$route['dashboard/explications'] = 'dashboardMP/explications';
$route['dashboard/explications/liste'] = 'dashboardMP/explications_liste';
$route['dashboard/explications/create/l(:any)v(:any)'] = 'dashboardMP/explications_create/$1/$2';
$route['dashboard/explications/modify/l(:any)v(:any)'] = 'dashboardMP/explications_modify/$1/$2';
$route['dashboard/explications/delete/l(:any)v(:any)'] = 'dashboardMP/explications_delete/$1/$2';
// USERS
$route['mon-compte'] = 'users/compte';
$route['mon-compte/modifier-donnees-personnelles'] = 'users/modify_personal_data';
$route['mon-compte/modifier-password'] = 'users/modify_password';
$route['mon-compte/supprimer-compte'] = 'users/delete_account';
$route['mon-compte/supprimer-compte/confirmed'] = 'users/delete_account_confirmed';
// SEO
$route['sitemap.xml'] = "sitemap/index";
$route['sitemap-deputes-1.xml'] = "sitemap/deputes";
$route['sitemap-deputes-inactifs-1.xml'] = "sitemap/deputes_inactifs";
$route['sitemap-groupes-1.xml'] = "sitemap/groupes";
$route['sitemap-groupes-inactifs-1.xml'] = "sitemap/groupes_inactifs";
$route['sitemap-votes-1.xml'] = "sitemap/votes";
$route['sitemap-localites-d-1.xml'] = "sitemap/departements";
$route['sitemap-localites-v-1.xml'] = "sitemap/communes";
$route['sitemap-structure-1.xml'] = "sitemap/structure";
$route['sitemap-categories-1.xml'] = "sitemap/categories";
$route['sitemap-posts-1.xml'] = "sitemap/posts";
$route['sitemap-partis-politiques-1.xml'] = "sitemap/parties";
// COMMISSION
$route['commissions'] = 'commissions/index';
// GROUPES
$route['groupes/legislature-(:any)/(:any)/statistiques'] = 'groupes/individual_stats/$1/$2';
$route['groupes/legislature-(:any)/(:any)/membres'] = 'groupes/individual_membres/$1/$2';
$route['groupes/legislature-(:any)/(:any)/votes'] = 'groupes/individual_votes_datan/$1/$2';
$route['groupes/legislature-(:any)/(:any)/votes/all'] = 'groupes/individual_votes_all/$1/$2';
$route['groupes/legislature-(:any)/(:any)'] = 'groupes/individual/$1/$2';
$route['groupes/inactifs'] = 'groupes/inactifs';
$route['groupes/legislature-(:any)'] = 'groupes/index/$1';
$route['groupes'] = 'groupes/index';
// PARTIS POLITIQUES
$route['partis-politiques/(:any)'] = 'parties/individual/$1';
$route['partis-politiques'] = 'parties/index';
// DEPUTES
$route['deputes/inactifs'] = 'deputes/inactifs';
$route['deputes/legislature-(:any)'] = 'deputes/index/$1';
$route['deputes/(:any)'] = 'departement/view/$1';
$route['deputes/?commune=(:any)'] = 'city/index';
$route['deputes/(:any)/depute_(:any)/legislature-(:any)'] = 'deputes/historique/$2/$1/$3';
$route['deputes/(:any)/depute_(:any)/votes'] = 'deputes/votes_datan/$2/$1';
$route['deputes/(:any)/depute_(:any)/votes/all'] = 'deputes/votes_all/$2/$1';
$route['deputes/(:any)/depute_(:any)'] = 'deputes/individual/$2/$1';
$route['deputes/(:any)/ville_(:any)'] = 'city/index/$2/$1';
$route['deputes'] = 'deputes/index';
// DEPARTEMENTS
$route['index_departements'] = 'departement/liste';
// VOTES
$route['votes/legislature-(:any)/vote_c(:any)'] = 'votes/individual/$1/$2/cong';
$route['votes/legislature-(:any)/vote_(:any)'] = 'votes/individual/$1/$2';
$route['votes/legislature-(:any)/vote_c(:any)/explication_(:any)'] = 'votes/individual/$1/$2/cong/$3';
$route['votes/legislature-(:any)/vote_(:any)/explication_(:any)'] = 'votes/individual/$1/$2/false/$3';
$route['votes/legislature-(:any)'] = 'votes/all/$1';
$route['votes/decryptes/(:any)'] = 'votes/field/$1';
$route['votes/decryptes'] = 'votes/decryptes//';
$route['votes/legislature-(:any)/(:any)/(:any)'] = 'votes/all/$1/$2/$3';
$route['votes/legislature-(:any)/(:any)'] = 'votes/all/$1/$2';
$route['votes'] = 'votes/index';
// POSTS
$route['posts/update'] = 'posts/update';
$route['posts/create'] = 'posts/create';
$route['posts/edit/(:any)'] = 'posts/edit/$1';
$route['blog/categorie/(:any)'] = 'posts/category/$1';
$route['blog/(:any)/(:any)'] = 'posts/view/$2/$1';
$route['blog'] = 'posts/index';
// STATISTIQUES
$route['statistiques'] = 'stats/index';
$route['statistiques/aide'] = 'pages/view/statistiques';
$route['statistiques/(:any)'] = 'stats/individual/$1';
// QUESTIONNAIRE
$route['questionnaire'] = 'quiz/index';
$route['questionnaire/resultat']['post'] = 'quiz/result';
// ELECTIONS
$route['elections'] = 'elections/index';
$route['elections/(:any)'] = 'elections/individual/$1';
// NEWSLETTER
$route['newsletter/edit/(:any)'] = 'newsletter/edit/$1';
$route['newsletter/update'] = 'newsletter/update';
$route['newsletter/delete/(:any)'] = 'newsletter/delete/$1';
$route['newsletter/votes'] = 'newsletter/votes/$1';
$route['newsletter/votes/(:any)'] = 'newsletter/votes/$1';
$route['newsletter'] = 'newsletter/register';
// PARRAINAGES
$route['parrainages-2022'] = 'parrainages/index';
// FAQ
$route['faq'] = 'faq/index';
// API
$route['api/(:any)/(:any)'] = 'api/index/$1/$2';
// LOGIN & REGISTER
$route['login'] = 'users/login';
$route['register/(:any)'] = 'users/register/$1';
$route['register'] = 'users/register';
$route['logout'] = 'users/logout';
$route['password'] = 'users/password_lost_request';
$route['password/(:any)'] = 'users/password_lost_change/$1';
$route['demande-compte-depute'] = 'users/demande_mp';
// REDIRECTION
$route['classements'] = 'redirection/redir/statistiques';
$route['search/(:any)/(:any)'] = 'redirect/cities/$1/$2';
// CACHE
$route['cache/delete_all'] = 'cache/delete_all';
// LOGS
$route['admin/logs'] = 'logViewerController/index';
$route['admin/logs-scripts/(:any)'] = 'logs/index/$1';
// EXPORT
$route['export/set_session/(:any)'] = 'export/set_session/$1';
// PAGES
$route['(:any)'] = 'pages/view/$1';
$route['translate_uri_dashes'] = FALSE;
// HOMEPAGE
$route['default_controller'] = 'home/index';
// 404 PAGE
$route['404_override'] = 'errormanager/error404';

$route['(.*)'] = "none";
