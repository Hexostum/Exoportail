<?php
class ep_membre extends ep_tableau
{
	public $membre_id;	
	public $enregistrement;
	
	public function __construct($params)
	{
		// colonne_nom colonne_valeur
		if(is_array($params)):
			$_params = new ep_params($params);
			$mode = $_params->propriete_liste('mode','sql');
			$colonne_nom = $_params->propriete_liste('colonne_nom','membre_id');
			$colonne_valeur = $_params->propriete_liste('colonne_valeur');
			$this->debug_initialiser($_params->propriete_liste('debug_statut',EP_DEBUG));
		else:
			$colonne_valeur = $params;
			$colonne_nom = 'membre_id';
			$mode = 'sql';
			$this->debug_initialiser(EP_DEBUG);
		endif; 
		switch($mode):
			case 'sql':
				
				$this->enregistrement = new ep_enregistrement
				(
					array
					(
						'table_nom' => EP_TABLE_MEMBRES,
						'id_colonne_nom' => $colonne_nom,
						'id_colonne_valeur'=>  $colonne_valeur
					)
				);
				
			break;
			
			case 'array':
				$this->enregistrement = new ep_enregistrement_sql
				(
					array
					(
						'table_nom' => EP_TABLE_MEMBRES,
						'id_colonne_valeur' => $colonne_valeur,
						'champs' =>  $params->propriete_liste('champs',array()) 
					)
				);
			break;
			
		endswitch;
		$this->propriete_liste_initialiser($this->enregistrement->propriete_liste_reference());	
		$this->statut = $this->enregistrement->statut();
		$this->membre_id = $this->enregistrement->membre_id;
		$this->objet_id_initialiser($this->membre_id);
	}
	
	public function membre_existe()
	{
		ep_deprecated('ep_membre.membre_existe()','ep_membre.statut()');
		return $this->statut;
	}
	
	function credits_actualiser()
	{
		$l_sql = &$GLOBALS['lr_sql'];
		$my_membre = &$this->enregistrement;
		
		$credits_total = $l_sql->resultat('SELECT SUM(credits_montant) FROM cg_credits WHERE membre_id='.$my_membre->membre_id);
		
		$credits_depenses = $l_sql->resultat('SELECT SUM(ticket_credits) FROM cg_ig_tickets WHERE membre_id='.$my_membre->membre_id);
		
		$credits_restants = $credits_total - $credits_depenses;
		
		$credits_depenses = $l_sql->resultat('SELECT SUM(jeu_credits) FROM cg_jeu_hasard WHERE membre_id='.$my_membre->membre_id);
		
		$credits_restants -= $credits_depenses;
		
		$credits_depenses = $l_sql->resultat('SELECT SUM(ticket_credits) FROM cg_tombolas_tickets WHERE membre_id='.$my_membre->membre_id);
		
		$credits_restants -= $credits_depenses;
		
		$credits_depenses = $l_sql->resultat('SELECT SUM(recharge_credits) FROM cg_recharges WHERE membre_id='.$my_membre->membre_id);
		
		$credits_restants -= $credits_depenses;
		
		$my_membre->membre_credits = $credits_restants;
	}
	
	function argent_actualiser()
	{
		$l_sql = &$GLOBALS['lr_sql'];
		$my_membre = &$this->enregistrement;

		$argent_transaction = $l_sql->resultat('SELECT SUM(transaction_montant) FROM cg_argent WHERE membre_id='.$my_membre->membre_id);
		
		$argent_commande = $l_sql->resultat('SELECT SUM(commande_total) FROM codesgratis_commande WHERE membre_id='.$my_membre->membre_id);
		
		$argent_ig = $l_sql->resultat('SELECT SUM(ticket_gain) FROM cg_ig_tickets WHERE membre_id='.$my_membre->membre_id);
		$argent_ig += $l_sql->resultat('SELECT SUM(ig_argent) FROM codesgratis_igs WHERE membre_id='.$my_membre->membre_id);
		$argent_hasard = $l_sql->resultat('SELECT SUM(jeu_gain) FROM cg_jeu_hasard WHERE membre_id='.$my_membre->membre_id);
		$argent_tombola = $l_sql->resultat('SELECT SUM(ticket_gains) FROM cg_tombolas_tickets WHERE membre_id='.$my_membre->membre_id);
		$argent_depense = $l_sql->resultat('SELECT SUM(recharge_argent) FROM cg_recharges WHERE membre_id='.$my_membre->membre_id);
		$argent_clic = $l_sql->resultat('SELECT SUM(clic_gain) FROM cg_clics WHERE membre_id='.$my_membre->membre_id);
		
		$argent_restant = $argent_transaction + $argent_tombola + $argent_hasard + $argent_ig - $argent_commande - $argent_depense + $argent_clic;
		
		$my_membre->membre_argent = $argent_restant;
	}
	
	public function actualiser_vip()
	{
		$l_sql = &$GLOBALS['lr_sql'];
		$membre = &$this->enregistrement;
		$nombre_cg = $l_sql->resultat('SELECT count(code_id) FROM codesgratis_codes WHERE code_type=-2 AND membre_id='.$membre->membre_id.' AND code_validation is not null');
		$nombre_cgplus = $l_sql->resultat('SELECT count(code_id) FROM codesgratis_codes WHERE code_type=-3 AND membre_id='.$membre->membre_id.' AND code_validation is not null'); 
		$points_vip = (ep_vip::cg_vip_points * $nombre_cg);
		$points_vip += (ep_vip::cgplus_vip_points * $nombre_cgplus); 	
		$membre->membre_points_vip = $points_vip;
		$membre->membre_vip = ep_vip::determination_vip2($points_vip);
	
	}
	
	public function is_admin()
	{
		if($this->enregistrement->statut()):
			if($this->enregistrement->membre_id==0):
				return true;
			else:
				return false;
			endif;
		else:
			return false;
		endif;
	}
	
}
?>