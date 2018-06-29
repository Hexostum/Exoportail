<?php

class ep_xml extends ep_tableau 
{
	const EXCEPTION_SITE_NOM=1;
	const EXCEPTION_FICHIER_XML=2;
	
	
	private $rootname;
	private $dom;
	
	private $encoding;
	protected static $xml_uid = 0;
	public $fichier_nom;
	
	public function __construct($s_fichier,$xml_dossier=NULL,$site_nom='')
	{
		//parent::__construct();
		$this->objet_id_initialiser(++self::$xml_uid);
		$this->fichier_nom = ep_xml::fichier_nom($s_fichier,$xml_dossier,$site_nom);
		$this->statut = $this->lire_fichier();
		
	}
	static function fichier_nom($s_fichier,$xml_dossier=NULL)
	{
		if(defined('EP_SITE_NOM')):	
			$site_nom = EP_SITE_NOM;
		else:
			$site_nom = 'exoportail';
		endif;
		if(is_string($xml_dossier)):
			return EP_CHEMIN_XML . $site_nom . DIRECTORY_SEPARATOR . $xml_dossier . DIRECTORY_SEPARATOR . $s_fichier[0] . DIRECTORY_SEPARATOR . $s_fichier . '.xml';
		else:
			return EP_CHEMIN_XML  . $site_nom . DIRECTORY_SEPARATOR . 'configurations' . DIRECTORY_SEPARATOR . $s_fichier[0] . DIRECTORY_SEPARATOR . $s_fichier . '.xml';
		endif;
	}
	public function lire_fichier()
	{
		if(file_exists($this->fichier_nom)):
			$this->dom['R'] = new DOMDocument();
			try 
			{
				$this->dom['R']->load($this->fichier_nom);
				if($this->dom['R']->hasChildNodes()):
					$this->encoding = $this->dom['R']->encoding;
					if($this->encoding==null):
						$this->encoding = 'UTF-8';
					endif;
					$array = stat($this->fichier_nom);
					$this->propriete_initialiser('datep',$array['ctime']);
					$this->propriete_initialiser('datem',$array['mtime']);
					$tag_liste = $this->xml_to_ep_html($this->dom['R']->childNodes);
					$this->propriete_initialiser($tag_liste->tag_nom(),$tag_liste);
					return true;
				else:
					return false;
				endif;
			}
			catch(DOMException $e)
			{
				$this->trace
				(
					__FUNCTION__,
					__LINE__,	
					'[DOMEXCEPTION] ['.$e.']',
					$e
				);
				return false;
			}
		else:
			return false;
		endif;
	}
	
	public function ecrire_fichier()
	{
		if($this->statut):
			$this->dom['W'] = new DOMDocument('1.0',$this->encoding);
			$this->dom['W']->formatOutput = true;
			$root = $this->dom['W']->createElement($this->rootname);
			$this->array_to_tag($this->champs[$this->rootname],$root);
			$this->dom['W']->appendChild($root);
			$this->dom['W']->save($this->fichier);
		endif;			
	}
	
	private function xml_to_ep_html($xml)
	{
		if ( ($xml instanceof DOMNodeList)):
			$tag_liste = $this->DOMNodeListe_to_ep_html($xml);
			if(count($tag_liste)==1):
				return $tag_liste[0];
			else:
				return $tag_liste;
			endif;
		endif;
	}
	
	private function DOMNodeListe_to_ep_html(DOMNodeList $nodes_liste)
	{
		$contenu = array();
		foreach ($nodes_liste as $clef => $valeur):
			if( ($valeur instanceof DOMElement ) ):
				$contenu[] = $this->DOMElement_to_ep_html( $valeur );
			else:
				if($valeur instanceof DOMText):
					if(trim($valeur->wholeText)!=''):			
						$contenu[] = trim($valeur->wholeText);
					endif;
				endif;
			endif;
		endforeach;
		return $contenu;
	}
	
	private function DOMElement_to_ep_html(DOMElement $dom_element)
	{
		if($dom_element->hasAttributes()):
			foreach($dom_element->attributes as $attribut):
				$html_attributs[$attribut->name] = $attribut->value;
			endforeach;
		else:
			$html_attributs = array();
		endif;
		
		if($dom_element->hasChildNodes()):
			if( $dom_element->childNodes instanceof DOMNodeList ):
				$html_enfants = $this->DOMNodeListe_to_ep_html($dom_element->childNodes);
			else:
				echo get_class($dom_element->childNodes);
			endif;
		else:
			$html_enfants = array();
		endif;
		
		return 
			ep_html::html_tag
			(
				$dom_element->tagName,				
				$html_enfants,
				$html_attributs
			)
		;
	}
	
	public function &propriete_lire($propriete_nom,$propriete_valeur_defaut=null)
	{
		if(is_null($propriete_valeur_defaut)):
			return parent::propriete_lire($propriete_nom,new ep_html());
		else:
			return parent::propriete_lire($propriete_nom,$propriete_valeur_defaut);
		endif;
	}
	
	/**
	private function xml_to_array(DOMNodeList $nodes)
	{
		$tab = array();
		foreach ($nodes as $valeur):
			if(get_class($valeur)=='DOMElement'):
				$champ_actuel = $valeur->nodeName;
				$champ_id=NULL;
				$attributs = array();
				if($valeur->hasAttributes()):					
					foreach($valeur->attributes as $attribut):
						$attributs[$attribut->name] = $attribut->value;
						if($attribut->name=='id'):
							$champ_id=$attribut->value;
						endif;						
					endforeach;
				endif;
				if($valeur->childNodes->length == 0 ):
					if(count($attributs)==0):
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array();
						else:
							$tab[$champ_actuel][] = array();
						endif;
					else:
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array('attributs' => $attributs, 'texte'=>'');
						else:
							$tab[$champ_actuel][] = array('attributs' => $attributs, 'texte'=>'');
						endif;
					endif;
				elseif($valeur->childNodes->length == 1):
					if(count($attributs)==0):
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array('texte' => $valeur->nodeValue) ;
						else:
							$tab[$champ_actuel][] = array('texte' => $valeur->nodeValue) ;
						endif;
						
					else:
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array('attributs' => $attributs , 'texte' => $valeur->nodeValue) ;
						else:
							$tab[$champ_actuel][] = array('attributs' => $attributs , 'texte' => $valeur->nodeValue) ;
						endif;
					endif;
				else:
					if(count($attributs)==0):
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array_merge(array('enfants' => $this->xml_to_array($valeur->childNodes)));
						else:
							$tab[$champ_actuel][] = array_merge(array('enfants' => $this->xml_to_array($valeur->childNodes)));
						endif;
					else:
						if(!is_null($champ_id)):
							$tab[$champ_actuel][$champ_id] = array_merge(array('attributs' => $attributs) , array('enfants' => $this->xml_to_array($valeur->childNodes)));
						else:
							$tab[$champ_actuel][] = array_merge(array('attributs' => $attributs) , array('enfants' => $this->xml_to_array($valeur->childNodes)));
						endif;
					endif;
				endif;
			endif;
		endforeach;
		return $tab;
	}
	
	private function array_to_tag(array $tab, DOMNode $node)
	{
		foreach ($tab as $tag => $tab_node):
			foreach ($tab_node as $t_node):
				
				if(array_key_exists('texte',$t_node)):
					$current_tag = $this->dom['W']->createElement($tag , $t_node['texte']);
				else:
					$current_tag = $this->dom['W']->createElement($tag);
				endif;	
						
				if(array_key_exists('attributs',$t_node)):
					foreach($t_node['attributs'] as $att_name => $att_val):					
						$current_tag->setAttribute($att_name,$att_val);
					endforeach;
				endif;
				
				if(array_key_exists('enfants',$t_node)):
					$this->array_to_tag($t_node['enfants'],$current_tag);
				endif;
				
				$node->appendChild($current_tag);				
			endforeach;			
		endforeach;
	}
	/**
	public function &champ_valeur_g($config_id,$config_defaut=NULL)
	{
		$config_nom = substr($this->rootname,0,-1);
		if
			(
				isset($this->champs[$this->rootname][$config_nom][$config_id])
			)
		:
			return $this->champs[$this->rootname][$config_nom][$config_id]['texte'];
		else:
			return $config_defaut;
		endif;
	}
	/**
	public function get_config($config_id, $defaut='')
	{
		ep_serveur::deprecated('ep_xml::get_config','ep_xml::champ_valeur_g');
		return $this->champ_valeur_g($config_id,$defaut);
	}
	
	
	public function get_champ(array $clef , $os_defaut = '' )
	{
		if(count($clef)>0):
			return $this->get_recursive($clef , $this->champs[$this->rootname] , $os_defaut);
		else:
			return $this->champs[$this->rootname];
		endif;
	}
	
	public function set_champ(array $clef , $s_valeur)
	{
		return $this->set_recursive($clef , $this->champs[$this->rootname] , $s_valeur);
	}
	
	private function get_recursive($key,$tab,$os_defaut='')
	{
		if( count($key) > 1):
			if( array_key_exists( $key[0] , $tab ) ):
				$n_tab = $tab[$key[0]];
				array_shift($key);				
				return $this->get_recursive( $key , $n_tab , $os_defaut );
			else:
				return $os_defaut;
			endif;
		else:
			if( array_key_exists( $key[0] , $tab ) ):
				return $tab[$key[0]];
			else:
				return $os_defaut;
			endif;			
		endif;
	}
	
	private function set_recursive($key,&$tab,$s_valeur)
	{
		if( count($key) > 1):
			if( array_key_exists( $key[0] , $tab ) ):
				$n_tab =& $tab[$key[0]];
				array_shift($key);
				return $this->set_recursive( $key , $n_tab ,$s_valeur );
			else:
				return false;
			endif;
		else:
			if( array_key_exists( $key[0] , $tab ) ):
				$tab[$key[0]] = $s_valeur;
				return $tab[$key[0]];
			else:
				return false;
			endif;			
		endif;
	}		
	/**/
}
?>