<?php
class ep_params extends ep_tableau
{
	private static $params_uid = 0;
	
	public function __construct($propriete_liste=array(),$propriete_nom='')
	{
		if(is_array($propriete_liste)):
			$this->debug_initialiser(EP_DEBUG);
			$this->objet_id_initialiser(++self::$params_uid);
			$this->propriete_liste_initialiser($propriete_liste);
		elseif(($propriete_liste instanceof ep_params)):
			$this->debug_initialiser($propriete_liste->statut('debug'));
			$this->objet_id_initialiser($propriete_liste->objet_id);
			$this->propriete_liste_initialiser($propriete_liste->propriete_liste_reference());
		else:
			$this->debug_initialiser(EP_DEBUG);
			$this->objet_id_initialiser(++self::$params_uid);
			$this->propriete_initialiser($propriete_nom, $propriete_liste) ;
		endif;
	}
}
?>