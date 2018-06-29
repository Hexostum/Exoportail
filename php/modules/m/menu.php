<?php
class ep_module_menu_site extends ep_module
{
	public function __construct(exoportail &$my_serveur)
	{
		/**
		$this->position = ep_module::POSITION_AVANT_RUBRIQUE;
		$this->position_ordre = 1;
		/**/
		$this->module_nom='menu';
		parent::__construct($my_serveur);
		/**
		$this->liste_module_noms =
		array
		(
				'menu_exostum'
		)
		;
		$this->init_modules();
		/**/
	}
	public function contenu()
	{
		$contenu = array();
		$contenu[] = parent::contenu();
		
		$exostum_liens = 
			array
			(
				'{EXOSTUM:exostum}',
				'{EXOSTUM:codesgratis}',
				'{EXOSTUM:forum}',
				'{LIEN:dimensionrpg}',
				'{LIEN:blog}'
			)
		;
		
		$exostum_lis = array();
		foreach($exostum_liens as $lien):
			$exostum_lis[] = 
				ep_html::html_tag
				(
					'li',
					$lien
				)
			;
		endforeach;
		$menu_exostum = array();
		$menu_exostum[] = ep_html::html_tag
		(
			'h2',
			'Entreprise Exostum'
		);
		
		$menu_exostum[] = 
			ep_html::html_tag
			(
				'ul',
				$exostum_lis,
				array('id' => 'menu_exostum')
			)
		;
		
		
		$contenu[] = ep_html::html_tag
		(
			'div',
			$menu_exostum,
			array('class' => 'module' , 'id'=> $this->module_nom)
		);
		$contenu[] = parent::contenu();
		return $contenu;
	}
		/**
		$texte = array();
	
		$texte[] = '<ul>';
		$texte[] = '<h2><a href="http://www.exostum.net/">Réseau Exostum</a></h2>';
		$texte[] = '<li>Codesgratis</li>';
		$texte[] = '<li><a href="http://dimensionrpg.exostum.net/"</a>dimensionrpg</a></li>';
		$texte[] = '<li><a href="http://blog.exostum.net/">Blog du webmasteur</a></li>';
		$texte[] = '<li><a href="http://forum.exostum.net/">Forum du webmasteur</a></li>';
		$texte[] = '</ul>';
	
		$texte[] = '<ul>';
		$texte[] = '<h2>CodesGratis</h2>';
		$texte[] = FP_TAB . '<li><a href="index.php" title="Retourner  la page d\'accueil">Accueil</a></li>';
		$texte[] = FP_TAB . '<li><a href="vitrine.php" title="Venez prendre connaissance des prix à gagner">La vitrine</a></li>';
		$texte[] = FP_TAB . '<li><a href="classement.php" title="Découvrez le classement de nos joueurs inscrits !">Le classement</a></li>';
		$texte[] = FP_TAB . '<li><a href="gagnants.php" title="Qui a gagné quoi ?">Les gagnants</a></li>';
		$texte[] = FP_TAB . '<li><a href="enligne.php" title="Qui a gagné quoi ?">Les membres en ligne.</a></li>';
		$texte[] = FP_TAB . '<li><a href="http://forum.exostum.net/" title="">Le forum</a></li>';
		$texte[] = FP_TAB . '<li><a href="livreor.php" title="CodesGratis vous plait ? N\'hésitez pas à le dire !">Le livre d\'or</a></li>';
		$texte[] = FP_TAB . '<li><a href="support.php" title="Support et contact">Support et contact.</a></li>';
		$texte[] = FP_TAB . '<li><a href="reglement.php" title="Avant de jouer, prenez bien en compte le réglement !">Le réglement</a></li>';
		$texte[] = FP_TAB . '<li><a href="partenaires.php" title="Les partenaires de CodesGratis">Partenaires</a></li>';
		$texte[] = '</ul>';
		/**	
		$texte[] = '<ul>';
		$texte[] = '<h2>Statistiques</h2>';
		$texte[] = '<li>Le '. format_date(time()) . '</li>';
		$points = $l_sql->resultat('SELECT sum(membre_points) FROM codesgratis_membres where membre_id > 0 AND membre_banni=0');
		$points_plus = $l_sql->resultat('SELECT sum(membre_points_plus) FROM codesgratis_membres where membre_id > 0 AND membre_banni=0');
		$total = ($points + ($points_plus *2 ) ) / 1000;
		$texte[] = '<li> Compte joueurs : '.number_format($total,2,',',' ').' € </li>';
		$texte[] = '<li> Connectés : ' . $connectes_nombre . '</li>';
		$inscrits = $l_sql->resultat('SELECT count(membre_id) from codesgratis_membres where membre_banni=0');
		$texte[] = '<li> Membres : <a href="membre.php">'.$inscrits.'</a> </li>';
		$texte[] = '</ul>'
		/**
		return parent::__tostring() . implode(FP_LIGNE,$texte) . parent::__tostring();
		/**/
}
?>