<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/
//Verbose débug
define('DEBUG', true);

//On inclus les configurations
include '../inc/conf.php';



//On inclus les fonctions
include '../inc/functions.php';

/******************* Début du script *********************/
if (DEBUG){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

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
$trains = [];
if (!empty($answer->Siri->ServiceDelivery->StopMonitoringDelivery[0]->MonitoredStopVisit)){
	$trains = $answer->Siri->ServiceDelivery->StopMonitoringDelivery[0]->MonitoredStopVisit;
	$trains_triées = tri_trains ($trains,"STIF:Line::C01743:", $direction, $selected_direction, $quais, $arrets, $selected_arret );
}

$i=0;
$trains_display=array();
if (!empty($trains_triées)){
	foreach ($trains_triées as $train){
		$trains_display["data"][$i]["Mission"] = $train["mission"];
		$trains_display["data"][$i]["Destination"] = $train["destination"];
		if ($train["quai"] != ''){
			$trains_display["data"][$i]["Heure de passage"] = "<b>".date("H:i",strtotime($train["heure"]))." | ".$train["quai"]."</b>";
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
	
echo json_encode($trains_display);