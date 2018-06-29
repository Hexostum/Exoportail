<?php
$vip_codes = array
(
	0 => 0,
	1600 => 1,
	4000 => 2,
	8000 => 3,
	20000 => 4,
	40000 => 5,
	80000 => 6,
	160000 => 7,
	240000 => 8,
	320000 => 9,
	400000 => 10
);
$vip_codes2 = array
(
	0 => 0,
	1 => 1600,
	2 => 4000,
	3 => 8000,
	4 => 20000,
	5 => 40000,
	6 => 80000,
	7 => 160000,
	8 => 240000,
	9 => 320000,
	10 => 400000
);

$vip_pages_points = array
(
	10 => 2.00,
	9 => 1.90,
	8 => 1.80,
	7 => 1.70,
	6 => 1.60,
	5 => 1.50,
	4 => 1.40,
	3 => 1.30,
	2 => 1.20,
	1 => 1,
	0 => 1
);
$vip_hasard_points = array
(
	0 => 0,
	1 => 0,
	2 => 0,
	3 => 0,
	4 => 0,
	5 => 0,
	6 => 0,
	7 => 0.05,
	8 => 0.07,
	9 => 0.10,
	10 => 0.15
);
$vip_tombola_points = array
(
	0 => 0,
	1 => 0,
	2 => 0,
	3 => 0,
	4 => 0,
	5 => 0,
	6 => 0,
	7 => 0.03,
	8 => 0.05,
	9 => 0.07,
	10 => 0.1
);
$vip_clans_points_parrain = array
(
	0 => 0,
	1 => 0,
	2 => 0,
	3 => 0,
	4 => 0,
	5 => 0,
	6 => 0,
	7 => 0,
	8 => 0.02,
	9 => 0.04,
	10 => 0.07
);
//$vip_nouveau = 500000;

class ep_vip
{
	const vip_nouveau = 500000;
	const cg_vip_points = 80; //80 points pour un cg code;
	const cgplus_vip_points = 200; // 200 points pour un cg plus:
	
	public static function vip_points($vip_niveau)
	{
		return  (ep_vip::vip_nouveau)*(1-(0.1*(10-$vip_niveau)));
	}
	public static function determination_vip2($nb_points_vip)
	{ 	
		for($i=10;$i>0;$i--):
			if($nb_points_vip > ep_vip::vip_points($i)):
				return $i;
			endif;
		endfor;
		return $i;
	}
}
?>