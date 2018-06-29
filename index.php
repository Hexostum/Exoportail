<?php
/**
 * Activation de la tamporisation de sortie
 */

ob_start();

/**
 * @var
 * @desc : Site identifiant  
 */
define('EP_SITE_ID','exoportail');

$chemin_infos = pathinfo (__file__);
/**
 * Extension des fichiers (.php par défaut)
 *
 */
define('EP_FICHIER_EXT' , '.' . $chemin_infos['extension']);
unset($chemin_infos);
	
/**
* Chemin complet vers la base d'Exoportail
*
*/
// chemin complet en mode de production
/**
define('EP_CHEMIN' , dirname(__FILE__). '/../../exoportail' );
/*
*/

// chemin complet en mode développement
/**/
define('EP_CHEMIN' , '/srv/data/web/includes');
/*
*/

/**
 * Chemin vers le dossier des fichiers PHP d'Exoportail
 *
 */
define('EP_CHEMIN_PHP' , EP_CHEMIN . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR);
/**
 * Inclusion du fichier central d'EXOPORTAIL
 */
/**
if(file_exists(EP_CHEMIN_PHP . 'index' . EP_FICHIER_EXT)):
	require_once(EP_CHEMIN_PHP . 'index' . EP_FICHIER_EXT);
else:
	
	exit('<!-- [EXOSTUM] [EXOPORTAIL] [test] [file_exists] [' . EP_CHEMIN_PHP . 'index' . EP_FICHIER_EXT .']. [KO]  -->');
endif;
/*
*/

set_include_path(EP_CHEMIN_PHP);
require_once('exoportail' . EP_FICHIER_EXT);

if($exoportail instanceof exoportail ):
	$exoportail->site_nom(EP_SITE_ID);

	$exoportail->initialiser();

	$exoportail->titre_element_ajouter($exoportail->site_config_xml->site_nom->texte_enfants('exoportail'));
	
	$exoportail->rubrique($exoportail->variable_serveur_lire('get','rubrique_id',0));

	$exoportail->variable_serveur_liste_securite();
	$exoportail->envoyer_html();
else:
	exit('<!-- [EXOSTUM] [EXOPORTAIL] [instanceof (exoportail)] [KO] -->');
endif;
?>