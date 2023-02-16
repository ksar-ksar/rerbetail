<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

/* Renvoi la liste filtrée des trains
Données d'entrée : 
$trains 		Le tableau JSON de la réponse
$ligne 			Ligne de transport considéré
$direction		Direction considérée
Données de sortie: 
-1 si on a rien trouvé
Sinon un tableau avec les données sur les trains : 
- mission	Mission
- terminus 	Terminus
- heure		Heure de départ
- quai 		Quai de départ (si renseigné)
- attente	Attente en minutes
- a_quai	Si le train est a quai */

function tri_trains ($trains, $ligne, $direction, $selected_direction, $quais, $arrets, $selected_arret){
	
	$retour_list = array();
	$i=0;
	if (!empty($trains)){
		if (DEBUG) { echo "Il y a au moins un train\n"; }
		foreach ($trains as $train) {
			// On vérifie la ligne
			if (DEBUG) { print_r($train);}
			if ($train->MonitoredVehicleJourney->LineRef->value == $ligne){
				if (DEBUG) { echo "ligne OK : ".$train->MonitoredVehicleJourney->LineRef->value."\n"; }
				$train_ok = false;
				// On vérifie la destination
				// Cas RATP
				if (!empty($train->MonitoredVehicleJourney->DirectionName[0]->value)){
					if ($train->MonitoredVehicleJourney->DirectionName[0]->value == $direction){
						$train_ok = true ;
						if (DEBUG) { echo "direction OK : ".$train->MonitoredVehicleJourney->DirectionName[0]->value."\n"; }
					}else{
						if (DEBUG) { echo "direction NOK : ".$train->MonitoredVehicleJourney->DirectionName[0]->value." / ".$direction."\n"; }
					}
				}else{
					//Cas SNCF, plus compliqué
					if (!empty($train->MonitoredVehicleJourney->DestinationName[0]->value)){
						
						$gare_trouvée = array_recursive_search_key_map ($train->MonitoredVehicleJourney->DestinationName[0]->value, $arrets);
						if ($gare_trouvée == false){
							if (DEBUG) { echo "direction NOK (SNCF) : Gare pas trouvée ".$train->MonitoredVehicleJourney->DestinationName[0]->value." \n"; }
						}else{
							if (($gare_trouvée[0] > $selected_arret) && ($selected_direction == 'S')){
								$train_ok = true ;
								if (DEBUG) { echo "direction OK (SNCF) : Gare trouvée et au sud \n"; }
							}else if (($gare_trouvée[0] < $selected_arret) && ($selected_direction == 'N')){
								$train_ok = true ;
								if (DEBUG) { echo "direction OK (SNCF) : Gare trouvée et au Nord \n"; }
							}else {
								if (DEBUG) { echo "direction NOK (SNCF) : Gare trouvée mais ni au Nord, ni au sud ".$gare_trouvée[0]." / ".$selected_arret." / ".$selected_direction." \n"; }
							}
						}
					}else{
						if (DEBUG) { echo "direction NOK (SNCF) pas de direction pour le train \n"; }
					}
				}
				
				if ((empty($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)) && (empty($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime))){
					$train_ok = false ;
					if (DEBUG) { echo "Pas d'heure de départ trouvé \n"; }
				}
				
				if ($train_ok){
					//Mission
					if (!empty($train->MonitoredVehicleJourney->VehicleJourneyName[0]->value)){
						// RATP
						$mission = $train->MonitoredVehicleJourney->VehicleJourneyName[0]->value;
						$retour_list[$i]["mission"] = $mission;
						if (DEBUG) { echo "Mission OK : ".$mission."\n"; }
					}else{
						//SNCF
						if (!empty($train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value)){
							$retour_list[$i]["mission"] = $train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value;
							if (DEBUG) { echo "Mission OK (SNCF): ".$train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value."\n"; }
						}else if (!empty($train->MonitoredVehicleJourney->JourneyNote[0]->value)){
							$retour_list[$i]["mission"] = $train->MonitoredVehicleJourney->JourneyNote[0]->value;
							if (DEBUG) { echo "Mission Defaut (SNCF): ".$train->MonitoredVehicleJourney->JourneyNote[0]->value."\n"; }
						}
					}
					//Destination
					if (!empty($train->MonitoredVehicleJourney->DestinationName[0]->value)){
						$retour_list[$i]["destination"] = $train->MonitoredVehicleJourney->DestinationName[0]->value;
						if (DEBUG) { echo "Terminus OK : ".$train->MonitoredVehicleJourney->DestinationName[0]->value."\n"; }
					}else{
						$retour_list[$i]["destination"] = "Non définit";
						if (DEBUG) { echo "Terminus NOK\n"; }
					}
					//Heure de départ
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)){
						$retour_list[$i]["heure"] = date('d-m-Y H:i:s', strtotime($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime));
						$attente_s = strtotime($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime) - strtotime(date('d-m-Y H:i:s'));
						$attente_string = "";
						if ($attente_s < 0){
							// Si supperieur à 5 minutes de retard on vire le train
							if ($attente_s < -300) {
								if (DEBUG) { echo "Train trop en retard, viré de la liste \n"; }
								$retour_list[$i]["heure"] = false;
								$retour_list[$i]["attente"] = false; 
							}else{
								$attente_string = "- ";
								$attente_s = abs($attente_s);
							}
						}
						if ($attente_s > 60*60 ){
							$attente_s_heure = intval ($attente_s/(60*60));
							$attente_s = $attente_s - $attente_s_heure*60*60;
							$attente_string .= $attente_s_heure."h ";
						}
						/*
						if ($attente_s > 60 ){
							$attente_s_min = intval ($attente_s/60);
							$attente_s = $attente_s - $attente_s_min*60;
							$attente_string .= $attente_s_min."m ";
						}
						$attente_string .= $attente_s."s"; */
						$attente_string .= round($attente_s/60)."m ";
						
						$retour_list[$i]["attente"] = $attente_string;
						if (DEBUG) { echo "Heure OK : ".$retour_list[$i]["heure"]."\n"; }
						if (DEBUG) { echo "Attente OK : ".$retour_list[$i]["attente"]." / ".strtotime(date('d-m-Y H:i:s'))." / ".strtotime($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)."\n"; }
					}else if (!empty($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime)){
						$retour_list[$i]["heure"] = date('d-m-Y H:i:s',strtotime($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime));
						$attente_s = strtotime($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime) - strtotime(date('d-m-Y H:i:s'));
						$attente_string = "";
						if ($attente_s < 0) {
							// Si supperieur à 5 minutes de retard on vire le train
							if ($attente_s < -300) {
								if (DEBUG) { echo "Train trop en retard, viré de la liste \n"; }
								$retour_list[$i]["heure"] = false;
								$retour_list[$i]["attente"] = false; 
							}else{
								$attente_string = "- ";
								$attente_s = abs($attente_s);
							}
						}
						if ($attente_s > 60*60 ){
							$attente_s_heure = intval ($attente_s/(60*60));
							$attente_s = $attente_s - $attente_s_heure*60*60;
							$attente_string .= $attente_s_heure."h ";
						}
						/*
						if ($attente_s > 60 ){
							$attente_s_min = intval ($attente_s/60);
							$attente_s = $attente_s - $attente_s_min*60;
							$attente_string .= $attente_s_min."m ";
						}
						$attente_string .= $attente_s."s"; */
						$attente_string .= round($attente_s/60)."m ";
						
						$retour_list[$i]["attente"] = $attente_string;
						if (DEBUG) { echo "Heure OK (AimedDepartureTime) : ".$retour_list[$i]["heure"]."\n"; }
						if (DEBUG) { echo "Attente OK : ".$retour_list[$i]["attente"]." / ".strtotime(date('d-m-Y H:i:s'))." / ".strtotime($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime)."\n"; }
					}else{	
						//On a rien trouvé comme heure, train a supprimer.
						$retour_list[$i]["heure"] = false;
						$retour_list[$i]["attente"] = false; 
						if (DEBUG) { echo "Heure NOK\n"; }
					}						
					//Train peut-être supprimé ou retardé : 
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus)){
						if ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus == "cancelled"){
							$retour_list[$i]["attente"] = "Supprimé";
							if (DEBUG) { echo "Train Supprimé\n"; }
						}else if ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus == "delayed"){
							$retour_list[$i]["attente"] = "Retardé";
							if (DEBUG) { echo "Train Retardé\n"; }
						}
					}
					
					//Quai (si il existe)
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->ArrivalPlatformName->value)){
						$retour_list[$i]["quai"] = "Voie ".$train->MonitoredVehicleJourney->MonitoredCall->ArrivalPlatformName->value;
						if (DEBUG) { echo "Quai OK : ".$retour_list[$i]["quai"]."\n"; }
					}else if (!empty($train->MonitoringRef->value)){
						$temp = explode(":", $train->MonitoringRef->value);
						if (array_key_exists($temp[3],$quais)){
							$retour_list[$i]["quai"] = $quais[$temp[3]];
							if (DEBUG) { echo "Quai OK : ".$retour_list[$i]["quai"]."\n"; }
						}else{
							$retour_list[$i]["quai"] = "";
							if (DEBUG) { echo "Quai NOK\n"; }
						}						
					}else{
						$retour_list[$i]["quai"] = "";
						if (DEBUG) { echo "Quai NOK\n"; }
					}
					//A quai
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->VehicleAtStop)){
						if ($train->MonitoredVehicleJourney->MonitoredCall->VehicleAtStop) {
							$retour_list[$i]["a_quai"] = " A quai";
						}else{
							$retour_list[$i]["a_quai"] = "";
						}
						if (DEBUG) { echo "A Quai OK : ".$retour_list[$i]["a_quai"]."\n"; }
					}else{
						$retour_list[$i]["a_quai"] = "";
						if (DEBUG) {  echo "A Quai NOK\n"; }
					}
					
					//Status départ
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus)){
						if ( ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "onTime") && ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "cancelled")){
							$retour_list[$i]["a_quai"] .= " ".$train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus;
						}
						if (DEBUG) { echo "Status OK : ".$retour_list[$i]["a_quai"]."\n"; }
					}else{
						if (DEBUG) {  echo "Status Départ NOK\n"; }
					}
					
					if ($retour_list[$i]["heure"] != false){
						$i++;
					}
				}
				
			}else{
				if (DEBUG) { echo "Mauvaise Ligne : ".$train->MonitoredVehicleJourney->LineRef->value." / ".$ligne."\n"; }
			}
		}
		if (!empty($retour_list)){
			usort($retour_list, 'date_compare');
			if (DEBUG) { print_r($retour_list); }
			return $retour_list;
		}else{
			return false;
			if (DEBUG) { echo "Retour_list est vide\n"; }
		}
	}else{
		if (DEBUG) { echo "Pas de trains envoyés\n"; }
		return false;
	}
}

// Compare deux timstampe
function date_compare($a, $b)
{
    $t1 = strtotime($a['heure']);
    $t2 = strtotime($b['heure']);
    return $t1 - $t2;
}    

// Recherche les informations sur PRIM 
// TODO : Passer à curl et gérer les érreurs
function prim_retrive ($stop_point){
	$opts = [
		"http" => [
			"method" => "GET",
			"header" => "Accept: application/json\r\n".
						"apikey: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\r\n"
		]
	];
	$context = stream_context_create($opts);
	$file = file_get_contents('https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef=STIF%3AStopPoint%3AQ%3A'.$stop_point.'%3A', false, $context);
	return json_decode($file);
}

// Fonction de recherche dans un tabelau multi-dimensionel
function array_recursive_search_key_map($needle, $haystack) {
    foreach($haystack as $first_level_key=>$value) {
        if ($needle === $value) {
            return array($first_level_key);
        } elseif (is_array($value)) {
            $callback = array_recursive_search_key_map($needle, $value);
            if ($callback) {
                return array_merge(array($first_level_key), $callback);
            }
        }
    }
    return false;
}