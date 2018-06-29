<?php
class ep_sql_config extends ep_tableau
{
	
	function __construct(ep_sql $lr_sql)
	{
		$lr_sql->requete
		(
			'SELECT config_nom , config_valeur FROM ' . EP_TABLES_CONFIGURATION
		);
		while($lr_sql->prochain_enregistrement_a()):
			$enregistrement = $lr_sql->enregistrement_a;
			$this->propriete_initialiser($enregistrement['config_nom'],$enregistrement['config_valeur']);
		endwhile;
	}
	/**
	public function valeur_champ($nom,$fonction=null,$parametres=null,$position=0)
	{
		if(array_key_exists($nom,$this->_champs)):
			if($fonction==null):
				return $this->_champs[$nom];
			else:
				if($parametres==null):
					return $fonction($this->_champs[$nom]);
				else:
					$parametres[$position] = $this->_champs[$nom];
					return call_user_func_array($fonction,$parametres);
				endif;
			endif;
		else:
			return null;
		endif;		
	}
	/**/
	
	/**
	public function mise_a_jour_champ($nom,$valeur)
	{
		if(array_key_exists($nom,$this->_champs)):
			$sql = "UPDATE codesgratis_config SET config_valeur='".mysql_real_escape_string($valeur)."' WHERE config_nom='$nom'";
			$statut = mysql_query( $sql );
			$this->trace('['.__function__.'][SQL]['.$sql.']',!$statut);
			if($statut):
				$this->trace('['.__function__.'][ this._champs['.$nom.'] = ['.$valeur.'] ]');
				$this->_champs[$nom] = $valeur;
				return true;	
			else:
				return false;
			endif;
		else:
			$this->trace('['.__function__.']','array_key_exists('.$nom.')');
			trigger_error("Champ {$nom} inconnu dans la table de configuration !", E_USER_ERROR);
			return false;
		endif;
	}
	/**/
	
	/**
	public function __get($nom)
	{
		if(array_key_exists($nom,$this->_champs)):
			return $this->_champs[$nom];
		else:
			trigger_error("Champ {$nom} inconnu dans la table de configuration !", E_USER_ERROR);
			return null;
		endif;
	}
	/**/
	
	/**
	public function __set($nom,$valeur)
	{	
		return $this->mise_a_jour_champ($nom,$valeur);
	}
	/**/
	
	/**
	public function __toString()
	{
		$texte = '';
		$texte .= '<div class="debug_infos">' . FP_LIGNE;
		$texte .=  '<h1>'.get_class($this).'</h1>' . FP_LIGNE;
		$texte .= '<table border="1">' . FP_LIGNE;
		$texte .= '<tr><th>Propriété</th><th>Valeur</th></tr>' . FP_LIGNE;
		$texte .= '<tr><td>Parent</td><td>'.	parent::__toString().'</td></tr>'.FP_LIGNE;
		
		$texte .= '<tr><td> this._champs </td><td>' .FP_LIGNE;
		$texte .= '<table border="1" style="width:100%">' . FP_LIGNE;
		foreach($this->_champs as $clef => $valeur):
			$texte .= '<tr><td> ['.$clef.'] </td><td>'.$valeur.'</td></tr>'.FP_LIGNE;
		endforeach;
		$texte .= '</table>' . FP_LIGNE;	
		$texte .= '</td></tr>' .FP_LIGNE;
		
		$texte .= '</table>' . FP_LIGNE;
		$texte .= '</div>' . FP_LIGNE;
		return $texte;
	}
	/**/
}
?>