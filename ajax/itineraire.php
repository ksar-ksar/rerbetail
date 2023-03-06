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
visiteur_comptage('ajax_iti');

if (!empty($_GET["depart"])){
	$selected_depart = $_GET["depart"]; 
	if (array_key_exists($selected_depart,$arrets)){
		$depart = $arrets[$selected_depart][1];
		$depart_nom = $arrets[$selected_depart][0];
	}
}

if (!empty($_GET["arrivee"])){
	$selected_arrivee = $_GET["arrivee"]; 
	if (array_key_exists($selected_arrivee,$arrets)){
		$arrivee = $arrets[$selected_arrivee][1];
		$arrivee_nom = $arrets[$selected_arrivee][0];
	}
}

if (empty($depart)){
	$depart = 45102;
	$depart_nom = 'Châtelet-Les Halles';
	$selected_depart = 17;
}
if (empty($arrivee)){
	$arrivee = 43097;
	$arrivee_nom = 'Bourg-la-Reine';
	$selected_arrivee = 27;
}

if (DEBUG){
	trigger_error("$depart_nom / $selected_depart / $depart");
	trigger_error("$arrivee_nom / $selected_arrivee / $arrivee");
}

//Recherche de l'itiniéraire.
if ($selected_depart > $selected_arrivee){
	$direction = 'AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
	$selected_direction = 'N';
	if (DEBUG){ trigger_error("Direction Nord");}
}elseif ($selected_depart < $selected_arrivee){
	$direction = 'ROBINSON-SAINT REMY LES CHEVREUSE';
	$selected_direction = 'S';
	if (DEBUG){ trigger_error("Direction Sud");}
}else{
	$direction = false;
	if (DEBUG){ trigger_error("Direction NOK");}
}

if ($direction != false){
	if ($selected_direction == 'N') {
		$list_stations = explode(',',$arrets[$selected_depart][2]);
		$nombre_stations = array_search ($selected_arrivee,$list_stations);
		if (DEBUG){ trigger_error("Itiniéraire trouvé dans la liste 2");}
		if ($nombre_stations == false){
			$list_stations = explode(',',$arrets[$selected_depart][3]);
			$nombre_stations = array_search ($selected_arrivee,$list_stations);
			if (DEBUG){ trigger_error("Itiniéraire trouvé dans la liste 3");}
			if ($nombre_stations == false){
				$list_stations = false;
				$nombre_stations = false;
				if (DEBUG){ trigger_error("Pas trouvé d'itiniéraire Nord");}
			}
		}
	}
	if ($selected_direction == 'S') {
		$list_stations = explode(',',$arrets[$selected_depart][4]);
		$nombre_stations = array_search ($selected_arrivee,$list_stations);
		if (DEBUG){ trigger_error("Itiniéraire trouvé dans la liste 4");}
		if ($nombre_stations == false){
			$list_stations = explode(',',$arrets[$selected_depart][5]);
			$nombre_stations = array_search ($selected_arrivee,$list_stations);
			if (DEBUG){ trigger_error("Itiniéraire trouvé dans la liste 5");}
			if ($nombre_stations == false){
				$list_stations = false;
				$nombre_stations = false;
				if (DEBUG){ trigger_error("Pas trouvé d'itiniéraire Sud");}
			}
		}
	}
	
	$trains_display=array();
	if ($list_stations != false) {
		for ($i = 0 ; $i <= $nombre_stations; $i++){
		
			$selected_arret = $list_stations[$i]; 
			if (array_key_exists($selected_arret,$arrets)){
				$gare = $arrets[$selected_arret][1];
				$gare_nom = $arrets[$selected_arret][0];
			}
			if (DEBUG){ trigger_error("On lance la recherche pour : $selected_arret / $gare / $gare_nom");}
			$trains_display["data"][$i]["Arret"]=$gare_nom;
			
			$answer = prim_retrive ($gare);
			$trains_triées = tri_trains ($answer,"STIF:Line::C01743:", $direction, $selected_direction, $quais, $arrets, $selected_arret );
			

			if (!empty($trains_triées)){
				if ($trains_triées[0]["quai"] != ''){
					$trains_display["data"][$i]["Heure"] = "<b>".date("H:i",strtotime($trains_triées[0]["heure"]))." ".$trains_triées[0]["quai"]."</b>";
				}else{
					$trains_display["data"][$i]["Heure"] = "<b>".date("H:i",strtotime($trains_triées[0]["heure"]))."</b>";
				}
				if ($trains_triées[0]["a_quai"] != ''){
					$trains_display["data"][$i]["Attente"] = $trains_triées[0]["a_quai"];
				}else{
					$trains_display["data"][$i]["Attente"] = $trains_triées[0]["attente"];
				}
				if (!empty ($trains_triées[1]["heure"])){
					if ($trains_triées[1]["quai"] != ''){
						$trains_display["data"][$i]["Suivant"] = "<b>".date("H:i",strtotime($trains_triées[1]["heure"]))." ".$trains_triées[1]["quai"]."</b>";
					}else{
						$trains_display["data"][$i]["Suivant"] = "<b>".date("H:i",strtotime($trains_triées[1]["heure"]))."</b>";
					}
				}else{
					$trains_display["data"][$i]["Suivant"] = "";
				}
				$trains_display["data"][$i]["Mission"]=$trains_triées[0]["mission"];
				$trains_display["data"][$i]["Terminus"]= '<small>'.$trains_triées[0]["destination"].'</small>';
			}else{
				$trains_display["data"][$i]["Heure"] = "Pas de trains";
				$trains_display["data"][$i]["Attente"] = "";
				$trains_display["data"][$i]["Suivant"] = "";
				$trains_display["data"][$i]["Mission"] = "";
				$trains_display["data"][$i]["Terminus"] = "";
			}
		}
	}else{
		$trains_display["data"][0]["Arret"] = "Pas d'itinéraire trouvé";
		$trains_display["data"][0]["Heure"] = "";
		$trains_display["data"][0]["Attente"] = "";
		$trains_display["data"][0]["Suivant"] = "";
		$trains_display["data"][0]["Mission"] = "";
		$trains_display["data"][0]["Terminus"] = "";
	}
}else{
	$trains_display["data"][0]["Arret"] = "Mauvaise sélection";
	$trains_display["data"][0]["Heure"] = "";
	$trains_display["data"][0]["Attente"] = "";
	$trains_display["data"][0]["Suivant"] = "";
	$trains_display["data"][0]["Mission"] = "";
	$trains_display["data"][0]["Terminus"] = "";
}

echo json_encode($trains_display);