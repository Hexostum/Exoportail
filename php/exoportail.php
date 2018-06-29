<?php
/**
 * Vérification de sécurité
 */
/**
if(!defined('EP_CHEMIN')):
	exit('tentative de hack');
endif;
/**/

require_once(EP_CHEMIN_PHP . 'miseajour.php');

/**
 * Constantes nécessaires
 */

/**
 * Chemin vers les classes d'Exoportail
 *
 */
define('EP_CHEMIN_CLASSES' , EP_CHEMIN_PHP . 'classes' . DIRECTORY_SEPARATOR);

define('EP_CHEMIN_RUBRIQUES' , EP_CHEMIN_PHP . 'rubriques' . DIRECTORY_SEPARATOR );
define('EP_CHEMIN_MODULES' , EP_CHEMIN_PHP . 'modules' . DIRECTORY_SEPARATOR);
/**
 * Chemin vers le dossier de fichiers XML
 *
 */
define('EP_CHEMIN_XML' , EP_CHEMIN . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR);
/**
 * Chemin vers le dossier de fonctions
 *
 */
define('EP_CHEMIN_FONCTIONS', EP_CHEMIN_PHP . 'fonctions' . DIRECTORY_SEPARATOR );
/**
 * chemin vers le dossier des constantes
 *
 */
define('EP_CHEMIN_CONSTANTS', EP_CHEMIN_PHP . 'constants' . DIRECTORY_SEPARATOR );
/**
 * Chemin vers le dossier cache
 *
 */
define('EP_CHEMIN_CACHE' , EP_CHEMIN_PHP . 'cache' . DIRECTORY_SEPARATOR);
/**
 * chemin vers le dossier de preuves
 *
 */
define('EP_CHEMIN_PREUVES' , EP_CHEMIN . 'preuves' . DIRECTORY_SEPARATOR);
define('EP_LIGNE' , "\r\n");
define('EP_TAB' , "\t");
define('EP_PAGE' , basename($_SERVER['PHP_SELF']) );
define('EP_ARG' , $_SERVER['QUERY_STRING']);
define('EP_SQL_ERROR_MODE',ep_sql::ERROR_MODE_EXCEPTION);

/**
 * Charge une classe automatiquement
 *
 * @param string $classe_nom
 * 
**/
function __autoload($classe_nom)
{
	// classe mode
	if(strstr($classe_nom,'ep_module_')):
		$classe_mode = 'module';
		$classe_prefix = 'ep_module_';
		$classe_chemin = EP_CHEMIN_MODULES ;
	elseif(strstr($classe_nom,'ep_rubrique_')):
		$classe_mode = 'rubrique';
		$classe_prefix = 'ep_rubrique_';
		$classe_chemin = EP_CHEMIN_RUBRIQUES ;
	else:
		$classe_mode = 'classe';
		$classe_prefix = 'ep_';
		$classe_chemin = EP_CHEMIN_CLASSES ;
	endif;
	
	$fichier_nom = str_replace($classe_prefix,'',$classe_nom);
	$lettre = $fichier_nom[0];
	$fichier_chemin = $classe_chemin . $lettre . DIRECTORY_SEPARATOR . $fichier_nom . EP_FICHIER_EXT;
	if
		(
			file_exists($fichier_chemin)
		)
	:
		require_once($fichier_chemin);
	else:
		if(EP_DEBUG):
			exit(" [EXOPORTAIL] [CLASSE] [$classe_nom] [$fichier_chemin] [KO] ");
		else:
			exit('<!-- [EXOPORTAIL] [KO] -->');
		endif;
	endif;
}


class exoportail extends ep_objet
{
	const chemin = EP_CHEMIN;
	const chemin_php = EP_CHEMIN_PHP;
	const chemin_classe = EP_CHEMIN_CLASSES;
	const chemin_xml = EP_CHEMIN_XML;
	const chemin_fonctions = EP_CHEMIN_FONCTIONS;
	const chemin_constants = EP_CHEMIN_CONSTANTS;
	const chemin_cache = EP_CHEMIN_CACHE;
	const chemin_preuve = EP_CHEMIN_PREUVES;
	
	const fichier_ext = EP_FICHIER_EXT;
	
	private $variables_liste = array();
	
	private $variable_serveur_liste ;
	private $variable_serveur_liste_autorise = array();
	
	private $modules_liste = array();
	private $modules = array();
	private $modules_position = array();
	
	private $titre_elements = array('Exostum');
	
	private $rubrique_contenu;
	private $debug_contenu;
	
	private $ressources;
	
	public $langue_id;
	
	public $liens_generique;
	public $site_nom;
	public $site_config_xml;
	public $site_config_sql;
	public $lr_sql;
	
	function __construct()
	{
		/**
		 * Vérification de sécurité
		 */
	    /**
		if(!defined('EP_CHEMIN')):
			exoportail::debug_exit(__FILE__,__LINE__);
		endif;
	    /**/
		if
			(
				$_SERVER['HTTP_HOST']=='localhost' || 
				$_SERVER['HTTP_HOST']=='127.0.0.1' 
			)
		:
			error_reporting(E_ALL);
			define('EP_DEBUG',true);
			define('EP_DEBUG_TRACE',true);
			define('EP_MODE','local');
		else:
			if(EP_MISE_A_JOUR):
				error_reporting(E_ALL);
				define('EP_DEBUG',false);
				define('EP_DEBUG_TRACE',false);
				define('EP_MODE','miseajour');	
			else:
				error_reporting(E_ERROR);
				define('EP_DEBUG',false);
				define('EP_MODE','distant');
				define('EP_DEBUG_TRACE',false);
			endif;	
		endif;
		
		parent::__construct();
		
		$this->variable_serveur_liste = new ep_tableau(array('get' => new ep_tableau(array()),'post' => new ep_tableau(array()),'cookie'=> new ep_tableau(array())));
		
		
		if(isset($_GET)):
			foreach($_GET as $clef => $valeur):
				$this->variable_serveur_liste->get->propriete_initialiser($clef,$valeur);
				$this->trace
				(
					__FUNCTION__,
					__LINE__,
					"[this.variables_get[$clef]] <= [$valeur]"
				);
			endforeach;			
		endif;
		
		if(isset($_POST)):
			foreach($_POST as $clef => $valeur):
				$this->variable_serveur_liste->post->propriete_initialiser($clef,$valeur);
				$this->trace
				(
					__FUNCTION__,
					__LINE__,
					"[this.variables_post[$clef]] <= [$valeur]"
				);
			endforeach;
		endif;
		
		if(isset($_COOKIE)):
			foreach($_COOKIE as $clef => $valeur):
				$this->variable_serveur_liste->cookie->propriete_initialiser($clef,$valeur);
				$this->trace
				(
					__FUNCTION__,
					__LINE__,
					"[this.variables_cookies[$clef]] <= [$valeur]"
				);
			endforeach;
		endif;
		
		$this->langue_id = $this->variable_serveur_liste->get->propriete_lire('langue_id','fr');
		define('EP_LANGUE_ID',$this->langue_id);
	}
	
	function initialiser()
	{
		$this->variables_init
		(
				array
				(
						'{HTML:BR}'=>'<br>',
						'{SERVER:HTTP_HOST}' => $_SERVER['HTTP_HOST']
				)
		);
		
		$this->liens_generique = new ep_liens_generiques();
		
		$this->lr_sql = new ep_sql();
		
		$this->lr_sql->tables_init();
		
		$this->lr_sql->set_encoding('utf8');
		$this->lr_sql->erreur_mode = EP_SQL_ERROR_MODE;
		
		//$this->site_config_sql = new ep_sql_config();
		
		
		$this->my_session = new ep_session($this->lr_sql);
		
		$this->my_session->maj_statut();
		
		$this->my_session->purger(60*5);
		/**
		$this->my_session->membre_overdrive();
		
		$this->my_session->courriel_statut();
		$this->my_session->messagerie_statut();
		
		$this->my_session->membre_actualiser();
		/**/
		
	}
	
	function site_nom($site_nom)
	{
		$this->site_nom = $site_nom;
		define('EP_SITE_NOM',$this->site_nom);
		$this->ressources = new ep_ressources('site');
		define('EP_SITE_NOM_2',$this->ressources->site_nom);
		$this->site_config_xml = new ep_configuration('site');
		exoportail::debug_echo((string)$this->site_config_xml);
	}
	
	public function variable_serveur_liste_initialiser(array $l_parametes_get)
	{
		foreach($l_parametes_get as $valeur):
			$this->variable_serveur_liste_autorise[$valeur] = $valeur;
		endforeach;
	}
	
	public function variable_serveur_lire($type,$valeur,$defaut=NULL)
	{
		if( $this->variable_serveur_statut($type,$valeur)):
			return $this->variable_serveur_liste->$type->propriete_lire($valeur,$defaut);
		else:
			return $defaut;
		endif;
	}
	
	public function variable_serveur_statut($type,$valeur)
	{
		if ($this->variable_serveur_liste->$type instanceof ep_tableau ):
			return $this->variable_serveur_liste->$type->propriete_statut($valeur);
		endif;
		return false;
	}
	
	
	public function variable_serveur_liste_joindre(array $liste)
	{
		$this->variable_serveur_liste_autorise = array_merge($this->variable_serveur_liste_autorise,$liste);
	}
	
	public function variable_serveur_liste_securite()
	{
		/**
		$flag = false;
		parse_str(EP_ARG,$data);
		$data2 = array();
		foreach ($data as $clef => $valeur):
			$this->variable_serveur_liste_autorise
			if(in_array($clef,$this->parametres_get,true)):
				$data2[$clef] = $valeur;
			endif;
		endforeach;
		if(count($data)!=count($data2)):
			if(count($data2)>0):
				$this->redirect( EP_PAGE . '?' . http_build_query($data2) );
			else:
				$this->redirect( EP_PAGE );
			endif;
		endif;
		/**/
	}
	
	
	/**/
	function rubrique_texte($contenu_texte)
	{
		if(is_array($contenu_texte)):
			$this->rubrique_texte = new ep_texte(implode(ep_html::br() . ep_texte::ligne, $contenu_texte));
		else:
			$this->rubrique_texte = new ep_texte($contenu_texte);
		endif;
	}
	/**/
	
	function rubrique($rubrique_id)
	{
		$rubrique_nom = 'ep_rubrique_' . $this->rubrique_nom($rubrique_id);
		$rubrique = new $rubrique_nom($this);
		if(($rubrique instanceof ep_rubrique)):
			$this->variable_serveur_liste_joindre($rubrique->variable_serveur_liste_copie());
			$this->rubrique_contenu = $rubrique;
		else:
			exit('<!-- [EXOSTUM] [EXOPORTAIL] TENTATIVE DE HACK -->');
		endif;
	}
	
	private function rubrique_nom($rubrique_id)
	{
		// retourne le nom de la rubrique selon son id
		$rubrique_xml = new ep_xml('rubriques');
		return $rubrique_xml->rubriques[$rubrique_id]->texte_enfants('erreur_404') ;
		
	}
	
	function serveur_erreur($erreur_code)
	{
		exoportail::a_faire('Ajouter Header pour erreur 404',__FILE__,__LINE__);
		if(!headers_sent()):
			switch ($erreur_code):
				case 404:
					header('HTTP/1.0 404 Not Found');
					$this->redirect(exoportail::url_fabriquer(array('rubrique_id'=>'erreur404')));
				break;
			endswitch;
			exit();
		else:
			exoportail::erreur_generale("Impossible de générer les entêtes HTML car ils sont déjà générés.");
		endif;
	}
	
	
	private function page_titre_construire()
	{
		return new ep_texte(implode (' - ' , $this->titre_elements));
	}
	
	public function titre_element_ajouter($element,$position=NULL)
	{
		if(is_null($position)):
			$this->titre_elements[] = $element;
		else:
			$this->titre_elements[$position] = $element;
		endif;
	}
	
	private function body_construire()
	{
		$contenu = array();
		
		if(is_null($this->rubrique_contenu)):
			//debug mode
			/**
			$this->debug_contenu[] = 
					ep_html::html_tag
					(
						'div',
						'{debug_infos}', 
						array ('class'=>'debug_infos_echo')
					)
			;
			/**/
			$debug_div = ep_html::html_tag
				(
					'div',
					$this->debug_contenu,
					array('class' => 'rubrique' , 'id'=>'debug_informations')
				)
			;
			/**/
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			$contenu[] = $debug_div;
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			
			$tag = 
				ep_html::html_tag
				(
					'body',
					$contenu
				)
			;
					
			return $tag;
		else:
			if(EP_DEBUG):
				$this->debug_contenu[] = 
					ep_html::html_tag
					(
						'div',
						'{debug_infos}', 
						array ('class'=>'debug_infos_echo')
					)
				;
				$debug_div = ep_html::html_tag
				(
					'div',
					$this->debug_contenu,
					array('class' => 'rubrique' , 'id'=>'debug_informations')
				)
				;
				$debug_contenu_statut = true;
			else:
				$debug_contenu_statut = false;
			endif;
			
			if($debug_contenu_statut):
				$this->rubrique_contenu->debug_contenu($debug_div);
			endif;
			
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			$contenu = array_merge($contenu,$this->rubrique_contenu->contenu());
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			/**
			 * Fin du contenu
			 */
			$tag = 
				ep_html::html_tag
				(
					'body',
					$contenu
				)
			;
					
			return $tag;
		endif;
	}
	
	
	
	function head_construire()
	{
		$contenu = array();
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		
		// Balise meta <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		$contenu[] =  
			ep_html::html_tag
			(
				'meta',
				array(),
				array
				(
					'http-equiv' => 'Content-Type',
					'content' => 'text/html; charset='.  $this->site_config_xml->site_encodage->texte_enfants('utf-8')
				)
			)
		;
		
		// Balise title
		$contenu[] =  
			ep_html::html_tag
			(
				'title',
				$this->page_titre_construire()
			)
		;
		
		// Balise base
		if('local'==EP_MODE):
			$base_href = $this->site_config_xml->url_locale->texte_enfants();
			ep_variables::variable_ajouter('{EXOSTUM:STATIC_HTML}',$this->site_config_xml->base_static_html_locale->texte_enfants(),true);
		else:
			$base_href = $this->site_config_xml->url_distante->texte_enfants();
			ep_variables::variable_ajouter('{EXOSTUM:STATIC_HTML}',$this->site_config_xml->base_static_html_distante->texte_enfants(),true);
		endif;
		$contenu[] = 
			ep_html::html_tag
			(
				'base',
				array(),
				array('href'=>$base_href)
			)
		;
		
		// balise stylesheet
		//link type="text/css" media="screen" rel="stylesheet" title="Design" href="html/style/design3.css">
		$contenu[] = 
			ep_html::html_tag
			(
				'link',
				array(),
				array
				(
					'type'=> 'text/css',
					'media'=>'screen',
					'rel' => 'stylesheet',
					'title' => $this->site_config_xml->site_nom->texte_enfants('exoportail'),
					'href' => '{EXOSTUM:STATIC_HTML}style/' . $this->site_config_xml->site_nom->texte_enfants('exoportail') . '.css'
				)
			)
		;
		
		
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		
		$tag = ep_html::html_tag
		(
			'head',
			$contenu
		);
		return $tag;
	}
	
	function variables_init(array $liste)
	{
		foreach ($liste as $clef => $valeur):
			$this->variable_ajouter($clef,$valeur);
		endforeach;
	}
	
	function variable_ajouter($clef,$valeur)
	{
		$this->variables_liste[$clef] = $valeur;
	}
	
	function variables_analyse()
	{
		foreach ($this->variables_liste as $clef => $valeur):
			if(is_array($valeur)):
				ep_variables::variable_ajouter($clef,$valeur['variable'],$valeur['analyse_statut']);
			else:
				ep_variables::variable_ajouter($clef,$valeur);
			endif;
		endforeach;
	}
	
	function envoyer_html()
	{
		$this->variables_analyse();
			
		$head_contenu = $this->head_construire();
		$body_contenu = $this->body_construire();
		
		$html = ep_html::html_tag
		(
			'html',
			array
			(
				$head_contenu,
				$body_contenu
			)
		);
		
		$contenu = ob_get_contents();
		
		if
			(
				(
					strlen($contenu) > 3 
				)
				&&
				(
					ord($contenu[0])==0xEF && 
					ord($contenu[1])==0xBB && 
					ord($contenu[2])==0xBF
				)
			)
		:
			$contenu = substr($contenu,3);
			$utf8_bom = chr(0xEF) . chr(0xBB)  . chr(0xBF);
			$utf8_bom_statut = 'OK';
		else:
			$utf8_bom_statut = 'KO';
			$utf8_bom = '';
		endif;
		
		if(EP_DEBUG):
			ep_variables::variable_ajouter('{debug_infos}',$contenu);
			ob_clean();
		else:
		    if(strlen($contenu)>0):
		        error_log($contenu);
	       		ep_variables::variable_ajouter('{debug_infos}','');
    			ob_clean();
    		    endif;
		endif;
		
		ob_end_flush();
		echo $utf8_bom . ep_html::doctype_html4_strict  .  ep_variables::analyse((string) $html) . '<!-- [UTF8_BOM_STATUT] ['.$utf8_bom_statut.'] -->';
		exit();
	}
	
	
	function redirect($destination)
	{
		header('Status: 301 Moved Permanently', false, 301);
		header('Location: ' . $destination);
		exit();
	}
	
	public static function page_courante($arg=array(),$replace=false)
	{
		if(!$replace):
			parse_str(EP_ARG,$data);
			$data = array_merge($data,$arg);
		else:
			$data = $arg;
		endif;
		if(count($data)>0):
			return EP_PAGE . '?' . http_build_query($data);
		else:
			return EP_PAGE;
		endif;
	}
	
	public static function url_fabriquer($arguments=array(),$page='index.php')
	{
		if(count($arguments)>0):
			return $page . '?' . http_build_query($arguments);
		else:
			return $page;
		endif;
	}
	
	public static function erreur_generale($message,$fichier=__FILE__,$ligne=__LINE__)
	{
		$debug_statut = EP_DEBUG;
		if(isset($GLOBALS['my_session'])):
			if($GLOBALS['my_session']->my_membre()->is_admin()):
				$debug_statut = true;
			endif;
		endif;
		$erreur_title = ep_html::html_tag('title','Exostum - Exo Portail - Erreur générale - Informations');
		
		$erreur_message_titre = 
			ep_html::html_tag
			(
				'h1',
				array
				(
					'html_attributs' => array('class'=> 'erreur_message_titre') ,
					'html_enfants' => 'Exostum - Exo Portail - Erreur générale - Informations'
				)
			)
		;
		
		if($debug_statut):
			$erreur_message = '[EXOPORTAIL] ['.$fichier.'] ['.$ligne.'] ' . $message;	
		else:
			$erreur_message = '[EXOPORTAIL] Une erreur générale est survenue. Le site est indisponible.';
		endif;
		
		$erreur_message_contenu = 
			ep_html::html_tag
			(
				'p', 
				array
				(
					'html_attributs' => array('class' => 'erreur_message_contenu') ,
					'html_enfants' => $erreur_message 
				)
			)
		;
		
		$erreur_div = 
			ep_html::html_tag
			(
				'div',
				array
				(
					'html_attributs' => array('class' => 'erreur_informations'), 
					'html_enfants' => array($erreur_message_titre,$erreur_message_contenu)
				)
			)
		;
		$auteur_div = exoportail::auteur_contenu();
		$erreur_body = 
			ep_html::html_tag
			(
				'body',
				array
				(
					'html_enfants' => array($erreur_div, exoportail::auteur_contenu())
				)
			)
		; 
		
		$erreur_head  = 
			ep_html::html_tag
			(
				'head',
				array
				(
					'html_enfants' => $erreur_title
				)
			)
		;
		
		$erreur_html = 
			ep_html::html_tag
			(
				'html',
				array
				(
					'html_enfants' => array($erreur_head,$erreur_body)
				)
			)
		;
		exit( ep_html::doctype_html4_strict . (string)$erreur_html);
	}
	
	static function deprecated($fonction_nom_deprecie,$fonction_nom_nouveau,$fichier=__FILE__,$ligne=__LINE__)
	{
		$message_div =  
			ep_html::html_tag
			(
				'div',
				array
				(
					'html_attributs' => array('class'=>'debug_infos'),
					'html_enfants' => '[EXOPORTAIL] ['.$fichier.'] ['.$ligne.'] ['.$fonction_nom_deprecie.'] [=>] ['.$fonction_nom_nouveau.']'
				)
			)
		;
		if(isset($GLOBALS['exoportail'])):
			$GLOBALS['exoportail']->debug_ajouter($message_div,$fichier,$ligne);
		else:
			echo (string)$message_div;	
		endif;
	}
	
	static function a_faire($message,$fichier=__FILE__,$ligne=__LINE__,$afficher_statut=false)
	{
		$message_div = 
			ep_html::html_tag
			(
				'div',
				'[EXOPORTAIL] ['.$fichier.'] ['.$ligne.'] [A FAIRE] [' . $message . ']',
				array('class'=>'debug_infos')
			)
		;
		if(isset($GLOBALS['exoportail'])):
			if($afficher_statut):
				echo (string)$message_div;
			else:
				$GLOBALS['exoportail']->debug_ajouter($message_div,$fichier,$ligne);
			endif;
		else:
			echo (string)$message_div;	
		endif;
	}
	
	function debug_ajouter($debug_contenu,$fichier=__FILE__,$ligne=__LINE__)
	{
		$this->debug_contenu[] = $debug_contenu;
		$this->trace
		(
				__FUNCTION__,
				__LINE__,
				"[$fichier][$ligne][this.debug_contenu][NEXT][$debug_contenu]"
		);
	}
	
	static function debug_exit($fichier,$ligne)
	{
		ob_end_flush();
		if
			(
				isset($GLOBALS['exoportail'])
			)
		:
			echo $GLOBALS['exoportail']->debug();
		endif;
		$exit_message = (string) ep_html::html_tag
			(
				'div',
				'[EXOPORTAIL] [DEBUG] [EXIT] ['. $fichier . '] [' . $ligne . ']',
				array('class'=>'debug_infos')
			)
		;
		exit($exit_message);
	}
	
	static function debug_echo($message,$fichier=__FILE__,$ligne=__LINE__,$action='echo')
	{
		if(EP_DEBUG):
			switch ($action):
				case 'echo':
					echo "<div class=\"echo_debug\">[EXOSTUM] [EXOPORTAIL] [$fichier] [$ligne] [$message] </div>" . EP_LIGNE; 
				break;
				default:
					return "[EXOSTUM] [EXOPORTAIL] [$fichier] [$ligne] [$message] " . EP_LIGNE;
				break;
			endswitch;
		endif;
	
	}
	
	static function debug_todo($fichier,$ligne,$function)
	{
		echo "<div class=\"echo_debug\">[EXOSTUM] [EXOPORTAIL] [TODO] [$fichier] [$ligne] [$function]</div>" . EP_LIGNE; 
	}
}

$exoportail = new exoportail();

?>