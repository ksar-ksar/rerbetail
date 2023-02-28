<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

//https://prim.iledefrance-mobilites.fr/marketplace/general-message?LineRef=STIF%3ALine%3A%3AC01743%3A


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
		if (DEBUG) { trigger_error("Il y a au moins un train"); }
		foreach ($trains as $train) {
			// On vérifie la ligne
			if (DEBUG) { trigger_error(print_r($train,true));}
			if ($train->MonitoredVehicleJourney->LineRef->value == $ligne){
				if (DEBUG) { trigger_error("ligne OK : ".$train->MonitoredVehicleJourney->LineRef->value); }
				$train_ok = false;
				// On vérifie la destination
				// Cas RATP
				if (!empty($train->MonitoredVehicleJourney->DirectionName[0]->value)){
					if ($train->MonitoredVehicleJourney->DirectionName[0]->value == $direction){
						$train_ok = true ;
						if (DEBUG) { trigger_error("direction OK : ".$train->MonitoredVehicleJourney->DirectionName[0]->value); }
					}else{
						if (DEBUG) { trigger_error("direction NOK : ".$train->MonitoredVehicleJourney->DirectionName[0]->value." / ".$direction); }
					}
				}else{
					//Cas SNCF, plus compliqué
					if (!empty($train->MonitoredVehicleJourney->DestinationName[0]->value)){
						
						$gare_trouvée = array_recursive_search_key_map ($train->MonitoredVehicleJourney->DestinationName[0]->value, $arrets);
						if ($gare_trouvée == false){
							trigger_error("direction NOK (SNCF) : Gare pas trouvée ".$train->MonitoredVehicleJourney->DestinationName[0]->value);
						}else{
							if (($gare_trouvée[0] > $selected_arret) && ($selected_direction == 'S')){
								$train_ok = true ;
								if (DEBUG) { trigger_error("direction OK (SNCF) : Gare trouvée et au sud"); }
							}else if (($gare_trouvée[0] < $selected_arret) && ($selected_direction == 'N')){
								$train_ok = true ;
								if (DEBUG) { trigger_error("direction OK (SNCF) : Gare trouvée et au Nord"); }
							}else {
								if (DEBUG) { trigger_error("direction NOK (SNCF) : Gare trouvée mais ni au Nord, ni au sud ".$gare_trouvée[0]." / ".$selected_arret." / ".$selected_direction); }
							}
						}
					}else{
						trigger_error("direction NOK (SNCF) pas de direction pour le train ".print_r($train,true));
					}
				}
				
				if ((empty($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)) && (empty($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime))){
					$train_ok = false ;
					if (DEBUG) { trigger_error("Pas d'heure de départ trouvé ".print_r($train,true)); }
				}
				
				if ($train_ok){
					//Mission
					if (!empty($train->MonitoredVehicleJourney->VehicleJourneyName[0]->value)){
						// RATP
						$mission = $train->MonitoredVehicleJourney->VehicleJourneyName[0]->value;
						$retour_list[$i]["mission"] = $mission;
						if (DEBUG) { trigger_error("Mission OK : ".$mission); }
					}else{
						//SNCF
						if (!empty($train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value)){
							$retour_list[$i]["mission"] = $train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value;
							if (DEBUG) { trigger_error("Mission OK (SNCF): ".$train->MonitoredVehicleJourney->TrainNumbers->TrainNumberRef[0]->value); }
						}else if (!empty($train->MonitoredVehicleJourney->JourneyNote[0]->value)){
							$retour_list[$i]["mission"] = $train->MonitoredVehicleJourney->JourneyNote[0]->value;
							if (DEBUG) { trigger_error("Mission Defaut (SNCF): ".$train->MonitoredVehicleJourney->JourneyNote[0]->value); }
						}else{
							trigger_error("Mission pas trouvée : ".print_r($train,true));
						}
					}
					//Destination
					if (!empty($train->MonitoredVehicleJourney->DestinationName[0]->value)){
						$retour_list[$i]["destination"] = $train->MonitoredVehicleJourney->DestinationName[0]->value;
						if (DEBUG) { trigger_error("Terminus OK : ".$train->MonitoredVehicleJourney->DestinationName[0]->value); }
					}else{
						$retour_list[$i]["destination"] = "Non définit";
						trigger_error("Terminus NOK : ".print_r($train,true));
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
								$attente_s = abs($attente_s);
								if ($attente_s > 59) {
									$attente_string = "- ";
								}
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
						if (DEBUG) { trigger_error("Heure OK : ".$retour_list[$i]["heure"]); }
						if (DEBUG) { trigger_error("Attente OK : ".$retour_list[$i]["attente"]." / ".strtotime(date('d-m-Y H:i:s'))." / ".strtotime($train->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)); }
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
								$attente_s = abs($attente_s);
								if ($attente_s > 59) {
									$attente_string = "- ";
								}
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
						if (DEBUG) { trigger_error("Heure OK (AimedDepartureTime) : ".$retour_list[$i]["heure"]); }
						if (DEBUG) { trigger_error("Attente OK : ".$retour_list[$i]["attente"]." / ".strtotime(date('d-m-Y H:i:s'))." / ".strtotime($train->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime)); }
					}else{	
						//On a rien trouvé comme heure, train a supprimer.
						$retour_list[$i]["heure"] = false;
						$retour_list[$i]["attente"] = false; 
						trigger_error("Heure pas trouvée : ".print_r($train,true));
					}						
					//Train peut-être supprimé ou retardé : 
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus)){
						if ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus == "cancelled"){
							$retour_list[$i]["attente"] = "Supprimé";
							if (DEBUG) { trigger_error("Train Supprimé"); }
						}else if ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus == "delayed"){
							$retour_list[$i]["attente"] = "Retardé";
							if (DEBUG) { trigger_error("Train Retardé"); }
						}else if ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "onTime"){
							trigger_error("Nouveau status : ".$train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus);
						}
					}
					
					//Quai (si il existe)
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->ArrivalPlatformName->value)){
						$retour_list[$i]["quai"] = "Voie ".$train->MonitoredVehicleJourney->MonitoredCall->ArrivalPlatformName->value;
						if (DEBUG) { trigger_error("Quai OK : ".$retour_list[$i]["quai"]); }
					}else if (!empty($train->MonitoringRef->value)){
						$temp = explode(":", $train->MonitoringRef->value);
						if (array_key_exists($temp[3],$quais)){
							$retour_list[$i]["quai"] = $quais[$temp[3]];
							if (DEBUG) { trigger_error("Quai OK : ".$retour_list[$i]["quai"]); }
						}else{
							$retour_list[$i]["quai"] = "";
							if (DEBUG) { trigger_error("Quai NOK"); }
						}						
					}else{
						$retour_list[$i]["quai"] = "";
						if (DEBUG) { trigger_error("Quai NOK"); }
					}
					//A quai
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->VehicleAtStop)){
						if ($train->MonitoredVehicleJourney->MonitoredCall->VehicleAtStop) {
							$retour_list[$i]["a_quai"] = " A quai";
						}else{
							$retour_list[$i]["a_quai"] = "";
						}
						if (DEBUG) { trigger_error("A Quai OK : ".$retour_list[$i]["a_quai"]); }
					}else{
						$retour_list[$i]["a_quai"] = "";
						if (DEBUG) {  trigger_error("A Quai NOK"); }
					}
					
					//Status départ
					if (!empty($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus)){
						if (($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "onTime") && ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "cancelled") && ($train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus != "delayed")){
							$retour_list[$i]["a_quai"] .= " ".$train->MonitoredVehicleJourney->MonitoredCall->DepartureStatus;
						}
						if (DEBUG) { trigger_error("Status OK : ".$retour_list[$i]["a_quai"]); }
					}else{
						if (DEBUG) {  trigger_error("Status Départ NOK"); }
					}
					
					if ($retour_list[$i]["heure"] != false){
						$i++;
					}else{
						unset($retour_list[$i]);
					}
				}
				
			}else{
				if (DEBUG) { trigger_error("Mauvaise Ligne : ".$train->MonitoredVehicleJourney->LineRef->value." / ".$ligne); }
			}
		}
		if (!empty($retour_list)){
			usort($retour_list, 'date_compare');
			if (DEBUG) { trigger_error(print_r($retour_list,true)); }
			return $retour_list;
		}else{
			return false;
			if (DEBUG) { trigger_error("Retour_list est vide"); }
		}
	}else{
		if (DEBUG) { trigger_error("Pas de trains envoyés"); }
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

// Recherche les informations temps réel sur PRIM 
// TODO : Passer à curl et gérer les érreurs
function prim_retrive ($stop_point){
	global $db;
	
	$cache = '../cache/'.$stop_point ;
	$expire = time() -20 ; // valable 20 secondes
	
	if(file_exists($cache) && filemtime($cache) > $expire){
		
		$file = file_get_contents($cache);
	}else{
		
		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accept: application/json\r\n".
							"apikey: ".API_KEY."\r\n"
			]
		];
		$context = stream_context_create($opts);
		$file = file_get_contents('https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef=STIF%3AStopPoint%3AQ%3A'.$stop_point.'%3A', false, $context);
		file_put_contents($cache,$file);

		$sql = "INSERT INTO requetes_temps_reel(date, nombre) "; 
		$sql .= "VALUES ('".date("Y-m-d")."', 1) ";
		$sql .= "ON DUPLICATE KEY UPDATE ";
		$sql .= "nombre = nombre + 1";
		
		if (!$db->query($sql)) {
			trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
		}
	}
	
	$temp = json_decode($file);
	
	if (!empty($temp->Siri->ServiceDelivery->StopMonitoringDelivery[0]->MonitoredStopVisit)){
		$trains = $temp->Siri->ServiceDelivery->StopMonitoringDelivery[0]->MonitoredStopVisit;
	}else{
		//Houston we get a problem
		trigger_error ("Erreur de retour PRIM temps reel : ".print_r($temp,true));
		$trains = [];
	}
	
	return $trains;
}

// Recherche les informations traffic sur PRIM 
// TODO : Passer à curl et gérer les érreurs
function prim_retrive_messages (){
	global $db;
	
	$cache = './cache/messages' ;
	$expire = time() -60 ; // valable 60 secondes
	
	if(file_exists($cache) && filemtime($cache) > $expire){
		
		$file = file_get_contents($cache);
	}else{
		
		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accept: application/json\r\n".
							"apikey: ".API_KEY."\r\n"
			]
		];
		$context = stream_context_create($opts);
		$file = file_get_contents('https://prim.iledefrance-mobilites.fr/marketplace/general-message?LineRef=STIF%3ALine%3A%3AC01743%3A', false, $context);
		file_put_contents($cache,$file);
		
		$sql = "INSERT INTO requetes_messages(date, nombre) "; 
		$sql .= "VALUES ('".date("Y-m-d")."', 1) ";
		$sql .= "ON DUPLICATE KEY UPDATE ";
		$sql .= "nombre = nombre + 1";
		
		if (!$db->query($sql)) {
			trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
		}
	}
	
	$temp = json_decode($file);

	if (!empty($temp->Siri->ServiceDelivery->GeneralMessageDelivery[0]->InfoMessage)){
		$messages = $temp->Siri->ServiceDelivery->GeneralMessageDelivery[0]->InfoMessage;
		
	}else{
		//Houston we get a problem
		trigger_error ("Erreur de retour PRIM Message : ".print_r($temp,true));
		$messages = [];
	}
	
	return array_reverse($messages);
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

// Fonction comptage des visiteurs
function visiteur_comptage($page_actuelle) {
	global $db;
	
	//Visiteur
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$sql = "INSERT INTO visiteur(ip,page) "; 
	$sql .= "VALUES ('$ip', '$page_actuelle') ";
			
	if (!$db->query($sql)) {
		//On a un probléme a traiter
		trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}
}

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function log_error( $num, $str, $file, $line, $context = null )
{
    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}

/**
 * Uncaught exception handler.
 */
function log_exception( Exception $e ){
    global $db;
	
	$logfile_dir = $_SERVER['DOCUMENT_ROOT']."/log/";
    $logfile = $logfile_dir . "php_" . date("y-m-d") . ".log";
    $logfile_delete_days = 30;
	
	$class = $db->real_escape_string(get_class( $e ));
	$file = $db->real_escape_string($e->getFile());
	$line = $db->real_escape_string($e->getLine());
	$message = $db->real_escape_string($e->getMessage());
	
	$sql = "INSERT INTO log(class, file, line, message) "; 
	$sql .= "VALUES ('$class', '$file', '$line', '$message') ";

	if (!$db->query($sql)) {
		$message = date("y-m-d H:i:s")." " . get_class( $e ) . "; File: {$e->getFile()}; Line: {$e->getLine()}; Message: {$e->getMessage()};";
		file_put_contents( $logfile, $message . PHP_EOL, FILE_APPEND );
	}
    
	// delete any files older than 30 days
	$files = glob($logfile_dir . "*");
	$now   = time();

	foreach ($files as $file)
		if (is_file($file))
			if ($now - filemtime($file) >= 60 * 60 * 24 * $logfile_delete_days)
				unlink($file);
	
    //exit();
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function check_for_fatal()
{
    $error = error_get_last();
    if ( $error["type"] == E_ERROR )
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}

register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "log_exception" );
ini_set( "display_errors", "off" );
error_reporting( E_ALL );