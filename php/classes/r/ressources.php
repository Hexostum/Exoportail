<?php
class ep_ressources extends ep_tableau
{
	const RESSOURCES_CHEMIN = 'ressources';
	
	public $fichier_nom;
	public $categorie;
	public $auteur;
	
	public function __construct($ressource_id,$params=array())
	{
		parent::__construct($params);
		try
		{
			$dossier_nom = ep_ressources::RESSOURCES_CHEMIN . DIRECTORY_SEPARATOR . EP_LANGUE_ID;
			$xml_parser = new ep_xml($ressource_id,$dossier_nom);
			$this->fichier_nom = $xml_parser->fichier_nom;
			if($xml_parser->statut()):
				$this->trace
				(	
					__FUNCTION__,
					__LINE__,
					'[xml_parser.statut] [TRUE]'
				);
				$this->categorie = $xml_parser->ressources->attribut_valeur('categorie');
				$this->auteur = $xml_parser->ressources->attribut_valeur('auteur');
				$ressources_tableau = $xml_parser->ressources->html_enfants('ressource');
				foreach($ressources_tableau as $clef => $valeur):
					$this->variable_exporter(NULL,$clef,$valeur);
				endforeach;
				$this->statut = true;
				//$this->variables_exporter();
			else:
				$this->trace
				(	
					__FUNCTION__,
					__LINE__,
					'[xml_parser.statut] [FALSE]'
				);
				$this->statut = false;
			endif;
		}
		catch (Exception $e)
		{
			$this->statut = false;
		}		
	}
	public function variable_exporter($id=NULL,$clef,$ressource)
	{
		if(!is_null($id)):
			$variable_id = '{' . 'RESSOURCES.' . strtoupper($this->categorie) . '.'. strtoupper($id) . '.' . strtoupper($clef) . '}';
		else:
			$variable_id = '{' . 'RESSOURCES.' . strtoupper($this->categorie) . '.' . strtoupper($clef) . '}';
		endif;
		
		if($ressource->attribut_valeur('analyse',false)):
			$this->propriete_initialiser($clef,$ressource->texte_enfants(''));
			ep_variables::variable_ajouter($variable_id, $ressource->texte_enfants(''),true);
		else:
			$this->propriete_initialiser($clef,$ressource->texte_enfants(''));
			ep_variables::variable_ajouter($variable_id, $ressource->texte_enfants(''));
		endif;
	}
	
	/**
	 * Enter description here...
	 * @deprecated 
	 * @param unknown_type $id
	 */
	public function variables_exporter($id=NULL)
	{
		if(!is_null($id)):
			foreach($this->propriete_liste as $clef => $ressource):
				$variable_id = '{' . 'RESSOURCES.' . strtoupper($this->categorie) . '.'. strtoupper($id) . '.' . strtoupper($clef) . '}';
				ep_variables::variable_ajouter($variable_id, $ressource);
			endforeach;
		else:
			foreach($this->propriete_liste as $clef => $ressource):
				$variable_id = '{' . 'RESSOURCES.' . strtoupper($this->categorie) . '.' . strtoupper($clef) . '}';
				ep_variables::variable_ajouter($variable_id, $ressource);
			endforeach;
		endif;
	}
	
	public function variables_exporter_debug()
	{
		if(EP_DEBUG):
			foreach($this->propriete_liste as $clef => $ressource):
				$id = '{' . 'RESSOURCES.' . strtoupper($this->categorie) . '.' . strtoupper($clef) . '}';
				echo $id;
			endforeach;
		endif;
	}
}
?>