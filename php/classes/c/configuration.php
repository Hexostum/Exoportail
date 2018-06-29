<?php
class ep_configuration extends ep_tableau
{
	const CONFIGURATIONS_CHEMIN = 'configurations';
	
	public $fichier_nom;
	
	public function __construct($configuration_id,$params=array())
	{
		parent::__construct($params);
		try
		{
			$xml_parser = new ep_xml($configuration_id,ep_configuration::CONFIGURATIONS_CHEMIN);
			$this->fichier_nom = $xml_parser->fichier_nom;
			exoportail::debug_echo($this->fichier_nom,__FILE__,__LINE__);
			if(file_exists($this->fichier_nom)):
			
				if($xml_parser->statut()):
					$this->trace
					(	
						__FUNCTION__,
						__LINE__,
						'[xml_parser.statut] [TRUE]'
					);
					if($xml_parser->propriete_statut("configurations")):
						$configuration_tableau = $xml_parser->configurations;
						if($configuration_tableau instanceof ep_html):
							foreach($configuration_tableau as $clef => $valeur):
								if($valeur->attribut_valeur('id')!=''):
									$this->propriete_initialiser($valeur->attribut_valeur('id'),$valeur);
								else:
									$this->propriete_initialiser($clef,$valeur);
								endif;
							endforeach;
							$this->statut = true;
					
						else:
							$this->statut = false;
						endif;
					else:
						exoportail::debug_echo('[configurations][KO]['.$xml_parser->fichier_nom.']',__FILE__,__LINE__);
						$this->trace
							(
								__FUNCTION__,
								__LINE__,
								'[xml_parser][configurations][XML][KO]'
							)
						;
						$this->statut=false;
					endif;
				else:
					exoportail::debug_echo('[configurations][KO][XML_PARSER]',__FILE__,__LINE__);
					$this->trace
						(	
							__FUNCTION__,
							__LINE__,
							'[xml_parser.etat] [FALSE]'
						)
					;
					$this->statut = false;
				endif;
			else:
				$this->trace
					(	
						__FUNCTION__,
						__LINE__,
						'[FILE_EXISTS]['.$this->fichier_nom.'] [FALSE]'
					)
				;
				exoportail::debug_echo('[FILE_EXISTS]['.$xml_parser->fichier_nom.'][KO]',__FILE__,__LINE__);					
			endif;
		}
		catch (Exception $e)
		{
			exoportail::debug_echo('[EXCEPTION]',__FILE__,__LINE__);
			$this->trace
				(
					__FUNCTION__,
					__LINE__,
					'[EXCEPTION]['.$e->getTraceAsString().']'
				)
			;
			$this->statut = false;
		}		
	}
	
	public function &propriete_lire($propriete_nom,$propriete_valeur_defaut=NULL)
	{
		if(is_null($propriete_valeur_defaut)):
			return parent::propriete_lire($propriete_nom, new ep_html());
		else:
			return parent::propriete_lire($propriete_nom,$propriete_valeur_defaut);
		endif;
	}
	
}
?>