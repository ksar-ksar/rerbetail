<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

//On inclus les configurations
include '../inc/conf.php';

//On inclus les fonctions
include '../inc/functions.php';

/******************* Début du script *********************/

header('Content-Type: application/json');

//Comptage des visiteurs
visiteur_comptage('ajax_next');

$gare='';
if (!empty($_GET["arret"])){
	$selected_arret = $_GET["arret"]; 
	if (array_key_exists($selected_arret,$arrets)){
		$gare = $arrets[$selected_arret][1];
		$gare_nom = $arrets[$selected_arret][0];
	}
}
$direction='';
if (!empty($_GET["direction"])){
	$selected_direction = $_GET["direction"]; 
	if ($selected_direction == 'N'){
		$direction='AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
	}else{
		$direction='ROBINSON-SAINT REMY LES CHEVREUSE';
	}
}

if (empty($gare)){
	$gare = 45102;
	$gare_nom = 'Châtelet-Les Halles';
	$selected_arret = 17;
}
if (empty($direction)){
	$direction = 'AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
	$selected_direction = 'N';
}

$answer = prim_retrive ($gare);
$trains_triées = tri_trains ($answer,"STIF:Line::C01743:", $direction, $selected_direction, $quais, $arrets, $selected_arret );

$i=0;
$trains_display=array();
if (!empty($trains_triées)){
	foreach ($trains_triées as $train){
		$trains_display["data"][$i]["Mission"] = $train["mission"];
		$trains_display["data"][$i]["Destination"] = $train["destination"];
		if ($train["quai"] != ''){
			$trains_display["data"][$i]["Heure de passage"] = "<b>".date("H:i",strtotime($train["heure"]))." ".$train["quai"]."</b>";
		}else{
			$trains_display["data"][$i]["Heure de passage"] = "<b>".date("H:i",strtotime($train["heure"]))."</b>";
		}
		if ($train["a_quai"] != ''){
			$trains_display["data"][$i]["Attente"] = $train["a_quai"];
		}else{
			$trains_display["data"][$i]["Attente"] = $train["attente"];
		}
		$i++;
	}
}

if ($i == 0){
	$trains_display["data"][0]["Mission"]="Pas de trains trouvés";
	$trains_display["data"][0]["Destination"]="";
	$trains_display["data"][0]["Heure de passage"]="";
	$trains_display["data"][0]["Attente"]="";
}
echo json_encode($trains_display);