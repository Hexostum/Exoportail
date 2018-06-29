<?php
include("include/classes/common.php");

if($_GET["action"]=="st_html::auteurs" && !$_GET["nom"]):
	$dossier = "data/fanfictions/st_html::auteurs/";
	$data = new common(array(
		"lang" => "$_GET[lang]",
		"rub"=> "fanfictions"  ,
		"action" => "st_html::auteurs",
		"final" => "nom" )
		,""
		);
	$data->InitListeLettre($dossier);
	$data->LireDossier();
	$data->AfficherChoixLettre(1);
	$data->donnees();
	$data->AfficherChoixLettre(2);
	$tpl->set_var("navigation","st_html::auteurs de fan-fictions - choix " );
	$tpl->set_var("untext",$tpl->subst("fcommon"));
	$tpl->parse("uncontenu","contenus",true);
	$G_title .= "st_html::auteurs de fan-fictions - Choix";

elseif($_GET["action"]=="st_html::auteurs" && $_GET["nom"]):
	$nom = str_replace(".htm","",$_GET["nom"]);
	$lettre = strtolower($nom{0});
	$path = "data/fanfictions/st_html::auteurs/$lettre/$nom.htm";
	if(file_exists($path)):
		$fp = fopen($path,"r");
		$contenu = fread($fp,filesize($path));
		fclose($fp);
		$reg = "|<title>(.*)</title>|";
   		preg_match_all($reg, $contenu, $title);
		$title  = $title[1][0];
		$contenu = preg_replace($reg, "", $contenu);
		$reg = "|<nbfictions>(.*)</nbfictions>|";
   		preg_match_all($reg, $contenu, $nbfictions);
		$nbfictions = $nbfictions[1][0];
		$contenu = preg_replace($reg, "", $contenu);
		$reg = "|<nbchapitres>(.*)</nbchapitres>|";
   		preg_match_all($reg, $contenu, $nbchapitres);
		$nbchapitres = $nbchapitres[1][0];
		$contenu = preg_replace($reg, "", $contenu);
		$reg = "|<id>(.*)</id>|";
   		preg_match_all($reg, $contenu, $id);
		$id=$id[1][0];
		$contenu = preg_replace($reg, "", $contenu);
		$tpl->set_var(array(
		"navigation" => "<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs")."\">st_html::auteurs de fan-fictions</a> : $title",
		"untext" => $contenu . "<br /><br /><div class=\"gras\">Nombre de fictions publiés : $nbfictions </div><br /><div class=\"gras\">Nombre de chapitres au total : $nbchapitres </div> "
		));
		$tpl->parse("uncontenu","contenus",true);
		$G_title .= "st_html::auteurs de Fan-fictions : $title";
	endif;

elseif($_GET["action"]=="liste" && !$_GET["titre"]):

	$dossier = "data/fanfictions/liste/";
	$data = new common(array(
		"lang" => "$_GET[lang]",
		"rub"=> "fanfictions"  ,
		"action" => "liste",
		"final" => "titre" )
		,""
		);
	$data->InitListeLettre($dossier);
	$data->LireDossier();
	$data->AfficherChoixLettre(1);
	$data->donnees();
	$data->AfficherChoixLettre(2);
	$tpl->set_var("navigation","Fan-fictions  - Choix des fan-fictions"   );
	$tpl->set_var("untext",$tpl->subst("fcommon"));
	$tpl->parse("uncontenu","contenus",true);
	$G_title .= "Fan-fictions  - Choix des fan-fictions";

elseif($_GET["action"]=="liste" && $_GET["titre"] && ! $_GET["chapitre"]):

	$titre = str_replace(".htm","",$_GET["titre"]);
	$lettre = $titre{0};
	$data = data("data/fanfictions/liste/$lettre/$titre/");

	$reg = "|<title>(.*)</title>|";
	preg_match_all($reg, $data, $temp_title);
	$titre_fiction  = $temp_title[1][0];

	$reg= "|<resume>(.*)</resume>|";
	preg_match_all($reg, $data, $resume);
	$resume_fiction  = $resume[1][0];

	$reg= "|<st_html::auteur>(.*)</st_html::auteur>|";
	preg_match_all($reg, $data, $st_html::auteur);
	$st_html::auteur_fiction  = $st_html::auteur[1][0];

	$reg= "|<id>(.*)</id>|";
	preg_match_all($reg, $data, $id);
	$id_st_html::auteur_fiction  = $id[1][0];


	$dossier = "data/fanfictions/liste/$lettre/$titre/chapitres/";

	$data = new common(array(
		"lang" => "$_GET[lang]",
		"rub"=> "fanfictions"  ,
		"action" => "liste",
		"titre" => "$titre",
		"final" => "chapitre" )
		,""
		);
	$data->path = $dossier;
	$data->LireDossier();
	$data->donnees();
	$tpl->set_var("navigation","<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste")."\">Fan-fictions</a> - $titre_fiction ");
	$tpl->set_var("untext","Résumé de la fiction : ".$resume_fiction .'<br />'.$tpl->subst("fcommon")."<br/><div style=\"text-align:center\"<br/>Cette fiction a été écrite par <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs&nom=$id_st_html::auteur_fiction")."\">$st_html::auteur_fiction</a></div>"  );
	$tpl->parse("uncontenu","contenus",true);

	$G_title .= "Fan-fictions - $titre_fiction";

elseif($_GET["action"]=="liste" && $_GET["titre"] && $_GET["chapitre"]):
	$titre = str_replace(".htm","",$_GET["titre"]);
	$lettre = $titre{0};
	$chapitre = str_replace(".htm","",$_GET["chapitre"]);
	$path = "data/fanfictions/liste/$lettre/$titre/chapitres/$chapitre.htm";

	if(file_exists($path)):
		$data = data("data/fanfictions/liste/$lettre/$titre/");

		$reg = "|<title>(.*)</title>|";
		preg_match_all($reg, $data, $temp_title);
		$titre_fiction  = $temp_title[1][0];

		$reg= "|<st_html::auteur>(.*)</st_html::auteur>|";
		preg_match_all($reg, $data, $st_html::auteur);
		$st_html::auteur  = $st_html::auteur[1][0];

		$reg= "|<id>(.*)</id>|";
		preg_match_all($reg, $data, $id);
		$id  = $id[1][0];


		$fp = fopen($path,"r");
		$contenu = fread($fp,filesize($path));
		fclose($fp);

		$reg = "|<title>(.*)</title>|";
		preg_match_all($reg, $contenu, $title);
		$titre_chapitre  = $title[1][0];

		$contenu = preg_replace($reg, "", $contenu);

		$reg = "|<pass>(.*)</pass>|";
		preg_match_all($reg, $contenu, $pass);
		$pass  = $pass[1][0];

		$contenu = preg_replace($reg, "", $contenu);

		if($pass==""):
			$tpl->set_var("navigation","<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste")."\">Fan-fictions</a> - <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste&amp;titre=$titre")."\">$titre_fiction</a> - $titre_chapitre");
			$tpl->set_var("untext",$contenu . "<br/><div style=\"text-align:center\"<br/>Cette fiction a été écrite par <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs&nom=$id")."\">$st_html::auteur</a></div>");
			$tpl->parse("uncontenu","contenus",true);
			$G_title .= "Fan-fictions - $titre_fiction - $titre_chapitre";
		else:
			if($_COOKIE["pass"]==$pass):
				$tpl->set_var("navigation","<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste")."\">Fan-fictions</a> - <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste&amp;titre=$titre")."\">$titre_fiction</a> - $titre_chapitre");
				$tpl->set_var("untext",$contenu . "<br/><div style=\"text-align:center\"<br/>Cette fiction a été écrite par <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs&nom=$id")."\">$st_html::auteur</a></div>");
				$tpl->parse("uncontenu","contenus",true);
				$G_title .= "Fan-fictions - $titre_fiction - $titre_chapitre";
			else:
				if(!$_POST["envoyer"]):
				$tpl->set_var("navigation","<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste")."\">Fan-fictions</a> - <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste&amp;titre=$titre")."\">$titre_fiction</a> - Chapitre protégé");
				$tpl->set_var("untext","
				<form action=\""."index.php?".st_url::FormateUrl($_SERVER["QUERY_STRING"])."\" method=\"post\">
				<div class=\"center\">
				Le chapitre de cette fan-fiction est protégé par un mot de passe pour la visualisation<br />
				<br />
				Mot de passe : <input type=\"password\" name=\"passe\"  /><br /><br />
				<input type=\"submit\" name=\"envoyer\" value=\"envoyer\" /><br />
				</div>
				</form>
				"
				. "<br/><div style=\"text-align:center\"><br/>Cette fiction a été écrite par <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs&nom=$id")."\">$st_html::auteur</a></div>");
				$tpl->parse("uncontenu","contenus",true);
				$G_title .= "Fan-fictions - $titre_fiction - chapitre protégé";
				else:
					setcookie("pass",$_POST["passe"]);
					$tpl->set_var("navigation","<a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste")."\">Fan-fictions</a> - <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=liste&amp;titre=$titre")."\">$titre_fiction</a> - Chapitre protégé");
					$tpl->set_var("untext"," <a href=\"".st_url::FormateUrl($_SERVER["QUERY_STRING"])."\">Continuer</a>".
					"<br/><div style=\"text-align:center\"<br/>Cette fiction a été écrite par <a href=\"".st_url::FormateUrl("index.php?lang=$lang_id&amp;rub=fanfictions&amp;action=st_html::auteurs&nom=$id")."\">$st_html::auteur</a></div>");
					$tpl->parse("uncontenu","contenus",true);
					$G_title .= "Fan-fictions - $titre_fiction - chapitre protégé";
				endif;
			endif;
		endif;
	endif;
else:
	erreur_generale("Paramétres invalides");
endif;
$com  = new commentaires("fanfictions-$_GET[action]-$_GET[nom]-$_GET[titre]-$_GET[chapitre]",st_url::FormateUrl($_SERVER['QUERY_STRING']));
?>