<?php
class ep_contenu extends ep_tableau
{
	const CONTENU_CHEMIN = 'contenu';
	
	
	public function __construct($contenu_type,$contenu_id,$params=array())
	{
		parent::__construct($params);
		try
		{
			$dossier_nom = ep_contenu::CONTENU_CHEMIN . DIRECTORY_SEPARATOR . $contenu_type . DIRECTORY_SEPARATOR . EP_LANGUE_ID;
			$xml_parser = new ep_xml($contenu_id,$dossier_nom);
			$this->fichier_nom = $xml_parser->fichier_nom;
			if($xml_parser->statut()):
				$this->trace
				(	
					__FUNCTION__,
					__LINE__,
					'[xml_parser.statut] [TRUE]'
				);
				$this->propriete_liste_initialiser($xml_parser->contenu->propriete_liste_copie());
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
	public function dossier_contenu($contenu_id)
	{
		
	}
}
?>