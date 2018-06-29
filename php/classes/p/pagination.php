<?php

class ep_pagination extends ep_objet 
{
	private $page_nom;
	public $page_nombre;
	
	public $enregistrements_par_page;
	public $enregistrements_nombre;
	
	public $clause_limit_texte;
	public $pagination_texte;
	public $sql_texte;
	
	
	public function __construct($params)
	{
		
		$l_sql = &$GLOBALS['exoportail']->lr_sql;
	
		$params = new ep_params($params);
		$sql_texte = '';
		if($params->propriete_statut('enregistrements_nombre')):
			$enregistrements_nombre = $params->enregistrements_nombre;
		else:
			if($params->where_condition):
				$where = '';
				foreach($params->where_condition as $valeur):
					if(isset($valeur['conjonction'])):
						$where .= ' ' . $valeur['conjonction'] . ' ';
					endif;
					switch($valeur['valeur_type']):
						case 'texte':
							$where .= $valeur['champ'] . $valeur['condition'] . "'" . $valeur['valeur'] . "'";
						break;
					
						case 'nombre':
							$where .= $valeur['champ'] . $valeur['condition'] . $valeur['valeur'];
						break;
					endswitch;
				endforeach;
				/**
				$sql_texte = 'SELECT count('.$params->table_compteur.') FROM '.$params->table . ' WHERE ' . $where;
				$enregistrements_nombre = $l_sql->resultat( $sql_texte );
				/**/
			else:
				/**
				$sql_texte = 'SELECT count('.$params->table_compteur.') FROM '.$params->table;
				$enregistrements_nombre = $l_sql->resultat( $sql_texte );
				/**/
			endif;
			$requete_texte = 
				new ep_sql_requete
				(
					array
					(
						'mode' => 'SELECT',
						'requete' => 
							array
							(
								'clause_select' => array( 'count('.$params->table_compteur.')' => 'enregistrement_nombre'),
								'clause_from' => array($params->table_nom),
			//					'clause_where' => array($params->clause_where)
							)
					)
				)
			;
			$enregistrements_nombre = $l_sql->resultat( $requete_texte );
		endif;
		
		$this->enregistrements_par_page = $params->propriete_lire('enregistrements_page',2);
		$this->enregistrements_nombre = $enregistrements_nombre;
		
		$this->page_nom = $params->propriete_lire('page_nom','page');
		$this->page_nombre = ceil( $this->enregistrements_nombre / $this->enregistrements_par_page );
		
		$this->sql_texte = $sql_texte;
		$this->pagination_texte();
		
	}

	function limit_texte()
	{
		exoportail::deprecated('ep_pagination::limit_texte','sql_requete::s_limit_texte');
		/**
		if(isset($_GET[$this->page_nom])):
			$page = intval($_GET[$this->page_nom]);
		else:
			$page = 0;
		endif;
		$limit_1 = $page * $this->enregistrements_par_page; 
		$limit_2 = $this->enregistrements_par_page;
		$this->clause_limit_texte =  $limit_1 .' , '. $limit_2;
		/**/
	}
	
	function pagination_texte()
	{
		if($this->page_nombre > 1):
			
			parse_str($_SERVER['QUERY_STRING'],$params);
			unset($params[$this->page_nom]);	
			
			$page_courante = isset($_GET[$this->page_nom]) ? $_GET[$this->page_nom] : 0 ;
			
			$pages = array();
			// Si on a moins de 11 pages on les affiches toutes
			if($this->page_nombre < 11):
				for
					(
						$i = 1 ; 
						$i <= $this->page_nombre ; 
						$i++
					)
				:
					
					if
						( 
							($i-1) != 
							$page_courante
						)
					:
						if
							(
								1 ==
								$i
							)
						:
							// Si c'est la première page on ne met pas de paramètre "page"
							$ces_params = $params;
						else:
							$ces_params = array_merge($params,array($this->page_nom => $i-1));
						endif;
						
						$pages[] = ep_html::html_tag
							(
								'li',
								array
								(
									ep_html::html_tag
									(
										'a',
										array($i),
										array('href'=>$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params))
									)
								)
							)
						; 
					else:
						// Si la page en cours est la page courante, on n'affiche pas de liens
						$pages[] = 
						ep_html::html_tag 
							(
								'li' , 
								array($i)
							)
						;
					endif;
				endfor;
				
			else:
				
				if
					( 
						0 != 
						$page_courante 
					)
				:
					$ces_params = $params;
					$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								ep_html::html_tag
								(
									'a',
									array(1),
									array('href'=>$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params))
								)
							)
						)
					; 
				else:
					$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								1
							)
						)
					; 
				endif;
				
				if
					(
						$page_courante > 
						5
					)
				:
					$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								'...'
							)
						)
					; 
					$start = $page_courante-4;
				else:
					$start = 2;
				endif;
				
				for
					(
						$i = $start ; 
						( $i <= $page_courante + 5 ) && ( $i <= $this->page_nombre ) ; 
						$i++ 
					)
				:
				
					if
						( 
							($i-1) != 
							$page_courante
						)
					:
						$ces_params = array_merge($params,array($this->page_nom => $i-1));
						$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								ep_html::html_tag
								(
									'a',
									array($i),
									array('href'=>$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params))
								)
							)
						)
					; 
						//$str[]= FP_TAB .FP_TAB .'<li><a href="'.$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params) .'">' . $i . '</a></li>';
					else:
						// Si la page en cours est la page courante, on n'affiche pas de liens
						$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								$i
							)
						)
					; 
						//$str[]= FP_TAB .FP_TAB .'<li>'. $i .'</li>';
					endif;
				endfor;
				
				if
					(
						$page_courante < 
						($this->page_nombre-5)
					)
				:
					$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								'...'
							)
						)
					; 
					if( $page_courante != $this->page_nombre ):
						$ces_params = array_merge($params,array($this->page_nom => $this->page_nombre-1));
						$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								ep_html::html_tag
								(
									'a',
									array($this->page_nombre),
									array('href'=>$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params))
								)
							)
						)
					; 
						//$str[]= FP_TAB .FP_TAB .'<li><a href="'.$_SERVER['PHP_SELF'].'?'. http_build_query($ces_params) .'">' . $this->page_nombre . '</a></li>';
					else:
						$pages[] = ep_html::html_tag
						(
							'li',
							array
							(
								$this->page_nombre-1
							)
						)
					; 
						//$str[]= FP_TAB .FP_TAB .'<li>'. $this->page_nombre-1 .'</li>';
					endif;
				endif;
				
			endif;
			$ul = 
				ep_html::html_tag
				(
					'ul',
					$pages,
					array('class' => 'menu_h')
				)
			;
			$div = 
				ep_html::html_tag
				(
					'div',
					array('Page :', $ul),
					array('class' => 'pagination')
				)
			;
			$this->pagination_texte = $div;
		else:
			$this->pagination_texte = new ep_html();
		endif;
		/**/
	}
}
?>