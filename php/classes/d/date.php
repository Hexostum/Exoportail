<?php
$GLOBALS['date_ressources'] = new ep_ressources('date');
class ep_date extends ep_objet 
{
	
	static function format($date)
	{
		$format = new ep_texte((string)$GLOBALS['date_ressources']->date_format);
		$matches = array();
		
		if(preg_match("#([0-9]{4})\-([0-9]{2})\-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})#",$date,$matches)):
		
			$format->texte_var('DATE.JOUR',intval($matches[3]));
			$format->texte_var('DATE.MOIS',ep_date::mois_nom(intval($matches[2])));
			$format->texte_var('DATE.AN',intval($matches[1]));
			$format->texte_var('DATE.HEURES',intval($matches[4]));
			$format->texte_var('DATE.MINUTES',intval($matches[5]));
			$format->texte_var('DATE.SECONDES',intval($matches[6]));
		else:
			$format->texte_var('DATE.JOUR',1);
			$format->texte_var('DATE.MOIS',ep_date::mois_nom(1));
			$format->texte_var('DATE.AN',0);
			$format->texte_var('DATE.HEURES',0);
			$format->texte_var('DATE.MINUTES',0);
			$format->texte_var('DATE.SECONDES',0);	
		endif;
		return $format;
	}
	
	static function mois_nom($mois_id)
	{
		$clef = 'mois_' . $mois_id;
		return (string)$GLOBALS['date_ressources']->$clef;
	}
}
?>