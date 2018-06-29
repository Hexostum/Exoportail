<?php
class ep_enregistrement extends ep_tableau
{
	protected $table_nom;
	protected $id_colonne_nom;
	protected $id_colonne_valeur;
	
	function __construct($array)
	{
		$l_sql = &$GLOBALS['exoportail']->lr_sql;
		
		/**
		 * @var ep_params
		 */
		$params = new ep_params($array);
		$params->propriete_erreur_mode=ep_tableau::PROPRIETE_ERREUR_EXCEPTION;
		$params->propriete_alias_liste_initialiser
		(
			array
			(
				'table_nom' => 'table',
				'id_colonne_nom' => 'champ_id',
				'id_colonne_valeur' => 'valeur_id'
			)
		);
		
		$this->debug_initialiser($params->propriete_lire('debug_statut',EP_DEBUG));
		
		//listes des noms de colonnes pour le select
		$colonne_nom_liste = $params->propriete_lire('colonne_nom_liste', array('*'));
		
		$this->table_nom = $params->propriete_lire('table_nom');

		$this->id_colonne_nom = $params->propriete_lire ('id_colonne_nom' , substr(str_replace($l_sql->table_prefix,'',$this->table_nom),0,-1) . '_id');
		
		$this->id_colonne_valeur = $params->id_colonne_valeur;
		
		if
			(
				''==$this->id_colonne_nom && 
				''==$this->id_colonne_valeur
			)
		:
			$sql_texte = new ep_sql_requete
			(
				array
				(
					'mode'=> ep_sql_requete::MODE_SELECT,
					'requete' => array
					(
						'select_clause' => $colonne_nom_liste,
						'from_clause' => array ($this->table_nom)
					)
				)
			); //"SELECT $champs FROM {$this->table} "; 
			$this->objet_id_init($this->table_nom);
		else:
			$sql_texte = new ep_sql_requete
			(
				array
				(
					'mode'=> ep_sql_requete::MODE_SELECT,
					'requete' => array
					(
						'clause_select' => $colonne_nom_liste,
						'clause_from' => array ($this->table_nom),
						'clause_where' => array ( 0 => array('champ'=>$this->id_colonne_nom,'condition'=>'=','valeur'=>$this->id_colonne_valeur,'valeur_type'=>$params->propriete_lire('id_colonne_valeur_type','nombre')))
					)
				)
			); //"SELECT $champs FROM {$this->table} where {$this->champ_id}=$valeur";
			$this->objet_id_init($this->table_nom , $this->id_colonne_nom ,$this->id_colonne_valeur );
		endif;
		
		$this->trace(__function__, __LINE__, '[REQUETE] ' . (string)$sql_texte);
		
		if( ( $l_sql instanceof ep_sql) ):
			$l_sql->requete($sql_texte,false);
			if($l_sql->prochain_enregistrement_a()):
				$champs = $l_sql->enregistrement_a;
				if(is_array($champs)):				
					$this->trace(__function__, __LINE__ , '[ this.statut = [true] ]');
					$this->statut = true;
				else:
					$this->trace(__function__, __LINE__ , '[ this.statut = [false] ]','is_array');
					$this->statut = false;
				endif;
			else:
				$champs = array();
				$this->trace(__function__, __LINE__ , '[ this.statut = [false] ]', $l_sql->sql_erreur );
				$this->statut=false;
			endif;
		else:
			if(EP_DEBUG):
				throw ( new exception (' [EXOSTUM] [EXOPORTAIL] [Le lance-requête SQL est KO. Veuillez le recharger ou en acheter un nouveau]') );
			endif;
		endif;
		$this->propriete_liste_initialiser($champs);
	}
	public function objet_id_init($table_id,$champ_id='CHAMP_ID',$valeur_id='VALEUR_ID')
	{
		if($champ_id=='champ_id'):
			$this->objet_id = get_class($this)  . ' table = [' . $table_id . ']';
		else:
			$this->objet_id = get_class($this)  . ' table = [' . $table_id . '] ' . $champ_id . ' = [' . $valeur_id . ']';
		endif;
	}
	
	public function valeur_champ($nom,$fonction=null,$parametres=null,$position=0)
	{
		if($this->statut()):
			if(array_key_exists($nom,$this->champs)):
				if($fonction==null):
					return $this->champs[$nom];
				else:
					if($parametres==null):
						return $fonction($this->champs[$nom]);
					else:
						$parametres[$position] = $this->champs[$nom];
						return call_user_func_array($fonction,$parametres);
					endif;
				endif;
			else:
				return null;
			endif;
		else:
			return null;
		endif;
	}
	/**/
	public function incremente_champ($champ_nom,$valeur=1)
	{
		if
			(
				$this->statut()
			)
		:
			if
				(
					$this->champ_statut($champ_nom)
				)
			:
				$sql = "UPDATE {$this->table} SET $champ_nom=$champ_nom+$valeur WHERE {$this->champ_id}=".$this->valeur_id;
				$statut = mysql_query( $sql );
				$this->trace(__function__,'[SQL]['.$sql.']',!$statut);
				if($statut):
					$this->champs[$champ_nom] += $valeur;
					$this->trace(__function__, '[this.champs['.$champ_nom.'] = ['.$this->champs[$champ_nom].'] ]');
					return true;	
				else:
					return false;
				endif;
			else:
				$this->trace(__function__,'array_key_exists('.$champ_nom.')');
				trigger_error("Champ {$champ_nom} inconnu pour {$this->table} !", E_USER_ERROR);
				return false;
			endif;
		else:
			$this->trace(__function__,'[false]==[this.statut]','this.statut');
			return false;
		endif;
	}
	public function mise_a_jour_champ($nom,$valeur,$string=true)
	{
		if($this->statut):
			if(array_key_exists($nom,$this->champs)):
				if($string):
					$sql = "UPDATE {$this->table} SET $nom='".mysql_real_escape_string($valeur)."' WHERE {$this->champ_id}=".$this->valeur_id;
				else:
					$sql = "UPDATE {$this->table} SET $nom=$valeur WHERE {$this->champ_id}=".$this->valeur_id;
				endif;
				$statut = mysql_query( $sql );
				$this->trace(__function__,'[SQL]['.$sql.']',!$statut);
				if($statut):
					$this->trace(__function__,'[ this._champs['.$nom.'] = ['.$valeur.'] ]');
					$this->champs[$nom] = $valeur;
					return true;	
				else:
					return false;
				endif;
			else:
				$this->trace(__function__,'array_key_exists('.$nom.')');
				trigger_error("Champ {$nom} inconnu pour {$this->table} !", E_USER_ERROR);
				return false;
			endif;
		else:
			$this->trace(__function__,'this.statut');
			return false;
		endif;
	}
}
?>