<?php
/**********************************************************************************
*		Script cron pour sauvegarder les passages prévisionels dans une gare
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/
if(isset($_SERVER['REMOTE_ADDR']))die('Permission denied.');

$time_start = microtime(true);

//if(isset($_SERVER['REMOTE_ADDR']))die('Permission denied.');

//On inclus les configurations
include './inc/conf.php';

//On inclus les fonctions
include './inc/functions.php';

/******************** Configuration ***********************/

$url_gtfs = "https://data.iledefrance-mobilites.fr/api/v2/catalog/datasets/offre-horaires-tc-gtfs-idfm/files/a925e164271e4bca93433756d6a340d1";
$temp_dir = "tmp/";
$gtfs_file_name = "gtfs.zip";
$route_id = "IDFM:C01743";
$tomorow = date("Ymd", strtotime('tomorrow'));
$tomorow_mois = date("m", strtotime('tomorrow'));
$tomorow_jours = date("d", strtotime('tomorrow'));
$tomorow_annee = date("Y", strtotime('tomorrow'));
echo date("Y-m-d H:i:s"). " Demain est le $tomorow\n";
$tomorow_day = date("N", strtotime('tomorrow'));
echo date("Y-m-d H:i:s"). " Demain est un $tomorow_day\n";

//Les gares 
$gares_clean = array ('43097', //Bourg-la-Reine
					'43071', //Aulnay-Sous-Bois
					'43833', //Luxembourg
					'58774', //Massy-Palaiseau
					'47889', //Saint-Rémy-Lès-Chevreuse
					'43164', //Mitry-Claye
					'473364', //Aéroport CDG 2 TGV
					'43186', //Robinson
					'43086', //Orsay-Ville
					'43145', //La Plaine
					'473890', //Denfert
					'43607'); //LaPlace 

/******************* Les fonctions ***********************/

/******************* Début du script *********************/

// On fait le ménage
array_map('unlink', array_filter((array) glob($temp_dir."*")));

//On supprime les anciens trips
$sql = "TRUNCATE trips_temp";
if (!$db->query($sql)) {
	//On a un probléme a traiter
	echo date("Y-m-d H:i:s"). " trips_temp pas vidée ".$sql." (".$db->errno.") ".$db->error."\n";
	trigger_error("trips_temp pas vidée ".$sql." (".$db->errno.") ".$db->error);
}

// On récupére le GTFS
if (file_put_contents($temp_dir.$gtfs_file_name, file_get_contents($url_gtfs))){
		$actual_time = microtime(true) - $time_start;
        echo date("Y-m-d H:i:s"). " Fichier GTFS télécharger en $actual_time seconds\n";
		
		//On essaie de le dézippé
		$zip = new ZipArchive;
		if ($zip->open($temp_dir.$gtfs_file_name) === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
			$actual_time = microtime(true) - $time_start;
			echo date("Y-m-d H:i:s"). " Fichier GTFS dézippé ($actual_time seconds)\n";
			
			// On ouvre le fichier trips.txt
			$trips = array();
			$i = 0;
			$handle = fopen($temp_dir."trips.txt", "r");
			if ($handle) {
				while (($row = fgetcsv($handle)) !== false) {
					
					// On test si c'est du RERB
					if (trim($row[0]) == $route_id){
						
						$trips[] = trim($row[1]);
						//On insert 
						
						if (trim($row[5]) == 1){
							$direction_trouve = 'S';
						}else{
							$direction_trouve = 'N';
						}
						$sql = "INSERT INTO trips_temp (service_id, trip_id, mission, terminus, direction) ";
						$sql .= "VALUES ('".trim($row[1])."', '".trim($row[2])."', '".trim($row[4])."', '".trim($row[3])."', '$direction_trouve')";
						
						if (!$db->query($sql)) {
							//On a un probléme a traiter
							echo date("Y-m-d H:i:s"). " Trips pas inseré dans trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
							trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
						}
						$i++;
					}
				}
				if (!feof($handle)) {
					echo date("Y-m-d H:i:s"). " Erreur de lecture du fichier trips.txt"."\n";
				}
				fclose($handle);
				$actual_time = microtime(true) - $time_start;
				echo date("Y-m-d H:i:s"). " Fin du parcours pour le fichier trips.txt, trouvé $i trips ($actual_time seconds)\n";
				//print_r ($trips);
				
				// On ouvre le fichier calendar.txt
				$i = 0;
				$trips_trouves = array();
				$handle = fopen($temp_dir."calendar.txt", "r");
				if ($handle) {
					while (($row = fgetcsv($handle)) !== false) {
						$service_id = trim($row[0]);
						
						// On test si c'est un service RER B
						if (in_array($service_id, $trips, true)){
							
							echo date("Y-m-d H:i:s"). " Trips trouvé $service_id / ".$row[8]." / ".$row[9]."\n";
							$trips_trouves [] = $service_id;
							//On test si on est dans le bon range de date 8 et 9
							if ((trim($row[8]) <= $tomorow) && (trim($row[9]) >= $tomorow)){
								
								echo date("Y-m-d H:i:s"). " On est dans le bon range / ".$row[$tomorow_day]."\n";
								
								//On test si on a pas un 1 pour demain
								if (trim($row[$tomorow_day]) != 1){
									//On marque le trips comme supprimable
									$sql = "UPDATE trips_temp SET del_cal = '1' WHERE service_id = '$service_id'";
									if (!$db->query($sql)) {
										//On a un probléme a traiter
										echo date("Y-m-d H:i:s"). " Trips pas marqué à supprimé de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
										trigger_error("Trips pas marqué à de trips_temp ".$sql." (".$db->errno.") ".$db->error);
									}
									$i++;
								}
							}else{
								//On marque le trips comme supprimable
								$sql = "UPDATE trips_temp SET del_cal = '1' WHERE service_id = '$service_id'";
								if (!$db->query($sql)) {
									//On a un probléme a traiter
									echo date("Y-m-d H:i:s"). " Trips pas marqué à supprimé de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
									trigger_error("Trips pas marqué à de trips_temp ".$sql." (".$db->errno.") ".$db->error);
								}
								$i++;
							}
						}

					}
					if (!feof($handle)) {
						echo date("Y-m-d H:i:s"). " Erreur de lecture du fichier calendar.txt"."\n";
					}
					fclose($handle);
					$actual_time = microtime(true) - $time_start;
					echo date("Y-m-d H:i:s"). " Fin du parcours pour le fichier calendar.txt, marqués pour suppression $i trips ($actual_time seconds)\n";
					
					// On ouvre le fichier calendar_dates.txt
					$i = 0;
					$handle = fopen($temp_dir."calendar_dates.txt", "r");
					if ($handle) {
						while (($row = fgetcsv($handle)) !== false) {
							$service_id = trim($row[0]);
							
							// On test si c'est un service RER B
							if (in_array($service_id, $trips, true)){
								
								echo date("Y-m-d H:i:s"). " Trips trouvé $service_id / ".$row[1]."\n";
								
								//On test si on est dans le bon jour
								if (trim($row[1]) == $tomorow){
									
									echo date("Y-m-d H:i:s"). " On est dans le bon jour / ".$row[2]."\n";

									//On marque le trip
									$sql = "UPDATE trips_temp SET cal_dates = '".trim($row[2])."' WHERE service_id = '$service_id'";
									if (!$db->query($sql)) {
										//On a un probléme a traiter
										echo date("Y-m-d H:i:s"). " Trips pas marqué à supprimé de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
										trigger_error("Trips pas marqué à supprimer de trips_temp ".$sql." (".$db->errno.") ".$db->error);
									}
									$i++;
								}else{
									//C'est pas le bon jours, il faut voir si il a été vu avant
									if (!in_array($service_id, $trips_trouves, true)){
										//On marque le trips comme supprimable
										$sql = "UPDATE trips_temp SET del_cal = '1' WHERE service_id = '$service_id'";
										if (!$db->query($sql)) {
											//On a un probléme a traiter
											echo date("Y-m-d H:i:s"). " Trips marqué à supprimé de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
											trigger_error("Trips marqué à supprimé de trips_temp ".$sql." (".$db->errno.") ".$db->error);
										}
									}
								}
							}
						}
						if (!feof($handle)) {
							echo date("Y-m-d H:i:s"). " Erreur de lecture du fichier calendar_dates.txt"."\n";
						}
						fclose($handle);
						$actual_time = microtime(true) - $time_start;
						echo date("Y-m-d H:i:s"). " Fin du parcours pour le fichier calendar_dates.txt, marqués pour suppression $i trips ($actual_time seconds)\n";
						
						//On clean les trips en supprimants les services supprimés
						$sql = "DELETE FROM trips_temp WHERE cal_dates = '2'";
						if (!$db->query($sql)) {
							//On a un probléme a traiter
							echo date("Y-m-d H:i:s"). " Trips pas supprimés de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
							trigger_error("Trips pas supprimés trips_temp ".$sql." (".$db->errno.") ".$db->error);
						}
						
						//On clean les trips en supprimants les services pas du bon jour et pas en exception
						$sql = "DELETE FROM trips_temp WHERE cal_dates = '0' AND del_cal = '1'";
						if (!$db->query($sql)) {
							//On a un probléme a traiter
							echo date("Y-m-d H:i:s"). " Trips pas supprimés de trips_temp ".$sql." (".$db->errno.") ".$db->error."\n";
							trigger_error("Trips pas supprimés trips_temp ".$sql." (".$db->errno.") ".$db->error);
						}
						
						//On récupére la liste des trips
						$i=0;
						$trips_clean = array();
						$mission_clean = array();
						$terminus_clean = array();
						$direction_clean = array();
						$sql = "SELECT trip_id, mission, terminus, direction FROM trips_temp";
						$reponse = $db->query($sql);
						while ($donnees = $reponse->fetch_assoc()){
							$trips_clean[] = $donnees['trip_id'];
							$mission_clean = array_merge($mission_clean, array ( $donnees['trip_id'] => $donnees['mission']));
							$terminus_clean = array_merge($terminus_clean, array ( $donnees['trip_id'] => $donnees['terminus']));
							$direction_clean = array_merge($direction_clean, array ( $donnees['trip_id'] => $donnees['direction']));
							$i++;
						}
						$actual_time = microtime(true) - $time_start;
						echo date("Y-m-d H:i:s"). " On a récupérer $i trips ($actual_time seconds)\n";
						//print_r ($trips_clean);
						//print_r ($mission_clean);
						
						// On ouvre le fichier stop_times.txt
						$i = 0;
						$handle = fopen($temp_dir."stop_times.txt", "r");
						if ($handle) {
							$arrets_trouves = null;
							$gare_depart_trouvee = null;
							$terminus_trouvee = null;
							$to_store = false;
							$j = 0;
							while (($row = fgetcsv($handle)) !== false) {
								
								$trip_id = trim($row[0]);
								
								// On test si c'est un service RER B
								if (in_array($trip_id, $trips_clean, true)){
									//echo date("Y-m-d H:i:s"). " TRIP trouvé $trip_id \n";
																	
									$stop_id = str_replace("IDFM:monomodalStopPlace:", "", trim($row[3]));
									//echo date("Y-m-d H:i:s"). " Gare trouvée $stop_id \n";
									
									//On regarde si on est au début de la course
									if (trim($row[6]) == '1'){
										$temp = array_recursive_search_key_map((int)$stop_id, $arrets);
										$gare_depart_trouvee = $temp[0];
										echo date("Y-m-d H:i:s"). " $trip_id Gare de départ trouvée $gare_depart_trouvee \n";
									}
									
									//On regarde si on est au début de la course
									if (trim($row[5]) == '1'){
										$temp = array_recursive_search_key_map((int)$stop_id, $arrets);
										$terminus_trouvee = $temp[0];
										echo date("Y-m-d H:i:s"). " $trip_id Gare d'arrivée trouvée $terminus_trouvee \n";
										if ($terminus_trouvee > $gare_depart_trouvee){
											$direction_trouvee = 'S';
										}else{
											$direction_trouvee = 'N';
										}
										echo date("Y-m-d H:i:s"). " $trip_id Direction trouvée $direction_trouvee \n";
										$terminus_trouvee = $arrets[$terminus_trouvee][0];
										echo date("Y-m-d H:i:s"). " $trip_id Terminus trouvée $terminus_trouvee \n";
										$mission_trouvee = $mission_clean[$trip_id];
										$to_store = true;
									}
									
									//On test si c'est le bon arret
									if (in_array($stop_id, $gares_clean, true)){
										$depart = explode( ":",trim($row[2]));
										$date_passage[$j] = date ("Y-m-d H:i:s", mktime( (int)$depart[0], (int)$depart[1], (int)$depart[2], (int)$tomorow_mois, (int)$tomorow_jours, (int)$tomorow_annee));					
										$arrets_trouves[$j] = $stop_id;
										echo date("Y-m-d H:i:s"). " $trip_id Gare d'arrets dans le range trouvée $stop_id \n";
										$j++;
									}
									
									if ($to_store){
										
										for ($j2 = 0; $j2 < $j; $j2++){
											// On insert le passage 
											$sql = "INSERT INTO passages_prevus (time, stop, dir, train, terminus) ";
											$sql .= "VALUES ('".$date_passage[$j2]."', '".$arrets_trouves[$j2]."', '$direction_trouvee', '$mission_trouvee', '$terminus_trouvee')";
											
											if ($db->query($sql) === TRUE) {
												// ça a marché
												echo date("Y-m-d H:i:s")." train inseré ".$sql."\n";
											}else{
												//On a un probléme a traiter
												echo date("Y-m-d H:i:s"). " train pas inseré ".$sql." (".$db->errno.") ".$db->error."\n";
												trigger_error("Erreur d'insertion train prévu dans la BD ".$sql." (".$db->errno.") ".$db->error);
											}
											
											echo date("Y-m-d H:i:s"). " $sql \n";
											$i++;
										}
										$arrets_trouves = null;
										$gare_depart_trouvee = null;
										$terminus_trouvee = null;
										$to_store = false;
										$j = 0;
									}
								}
							}
							if (!feof($handle)) {
								echo date("Y-m-d H:i:s"). " Erreur de lecture du fichier stop_times.txt"."\n";
							}
							fclose($handle);
							$actual_time = microtime(true) - $time_start;
							echo date("Y-m-d H:i:s"). " Fin du parcours pour le fichier stop_times.txt, nombre de passages insérées $i ($actual_time seconds)\n";
														
						}else{
							echo date("Y-m-d H:i:s"). " Erreur d'ouverture du fichier stop_times.txt\n";
						}
					}else{
						echo date("Y-m-d H:i:s"). " Erreur d'ouverture du fichier calendar_dates.txt\n";
					}
				}else{
					echo date("Y-m-d H:i:s"). " Erreur d'ouverture du fichier calendar.txt\n";
				}
			}else{
				echo date("Y-m-d H:i:s"). " Erreur d'ouverture du fichier trips.txt\n";
			}
		} else {
			echo date("Y-m-d H:i:s"). " Erreur : Fichier GTFS non dézippé"."\n";
		}
}else{
        echo date("Y-m-d H:i:s"). " Erreur du téléchargement du fichier GTFS"."\n";
}



?>