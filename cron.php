<?php
/**********************************************************************************
*		Script cron pour sauvegarder les passages dans une gare
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

if(isset($_SERVER['REMOTE_ADDR']))die('Permission denied.');

//On inclus les configurations
include './inc/conf.php';

//On inclus les fonctions
include './inc/functions.php';

/******************* Les fonctions ***********************/

//Retourne le where pour la journée
function time_where () {
	
	//on cherche si on est avant 3h du matin ou aprés
	if (date("G") > 3 ){
		// On est dans le même jour
		$debut = mktime(3, 0, 0, date("m") , date("d") , date("Y"));
		$fin = mktime(2, 59, 59, date("m") , date("d")+1 , date("Y"));
	}else{
		// On est le matin
		$debut = mktime(3, 0, 0, date("m") , date("d")-1 , date("Y"));
		$fin = mktime(2, 59, 59, date("m") , date("d") , date("Y"));
	}
	$sql = "BETWEEN '".date("Y-m-d H:i:s",$debut)."' AND '".date("Y-m-d H:i:s",$fin)."'";
	
	return $sql;
}

//Enregistre un train
function enregistre_train ($gare, $selected_direction, $mission, $terminus, $qualite) {
	global $db;
	
	//On cherche si ça existe
	$sql = "SELECT * FROM passages ";
	$sql .= "WHERE train = '$mission' ";
	$sql .= "AND stop = '$gare' ";
	$sql .= "AND time ".time_where ();
	
	$result = $db->query($sql);
	if($result->num_rows == 0) {
		//Le train n'est pas déjà dans la base de donnée pour le jours donné, on l'insert
		$sql = "INSERT INTO passages (stop, dir, train, terminus, quality) ";
		$sql .= "VALUES ('$gare', '$selected_direction', '$mission', '$terminus', '$qualite')";
		
		if ($db->query($sql) === TRUE) {
			// ça a marché
			echo date("Y-m-d H:i:s")."train inseré ".$sql."\n";
		}else{
			//On a un probléme a traiter
			echo date("Y-m-d H:i:s"). "train pas inseré ".$sql." (".$db->errno.") ".$db->error."\n";
			trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
		}
	}else{
		echo date("Y-m-d H:i:s"). "train déjà dans la base de donnée ".$sql."\n";
	}
}

//Recherche le dernier train loggé dans temp
function recherche_dernier ($gare, $selected_direction) {
	global $db;
	
	//On cherche si ça existe
	$sql = "SELECT train, terminus FROM temp ";
	$sql .= "WHERE stop = '$gare' ";
	$sql .= "AND dir = '$selected_direction'";
	
	$result = $db->query($sql);
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$train = $row;
		echo date("Y-m-d H:i:s"). "Train ancien trouvé : ".$train["train"]." ".$train["terminus"]."\n";
	}else{
		$train = false;
		echo date("Y-m-d H:i:s"). "Pas de train ancien trouvé ".$sql."\n";
	}
	
	return $train;
}

//Insert dernier train trouvé dans temp
function insert_dernier ($gare, $selected_direction, $mission, $terminus) {
	global $db;
	
	//On supprime l'ancien si il existe
	$sql = "DELETE FROM temp WHERE stop = '$gare' AND dir = '$selected_direction'";
	if (!$db->query($sql)) {
		//On a un probléme a traiter
		echo date("Y-m-d H:i:s"). "train pas supprimé de temp ".$sql." (".$db->errno.") ".$db->error."\n";
		trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}
	
	//On insert 
	$sql = "INSERT INTO temp (stop, dir, train, terminus) ";
	$sql .= "VALUES ('$gare', '$selected_direction', '$mission', '$terminus')";
	
	if ($db->query($sql) === TRUE) {
		// ça a marché
		echo date("Y-m-d H:i:s"). "train inseré dans temp ".$sql."\n";
	}else{
		//On a un probléme a traiter
		echo date("Y-m-d H:i:s"). "train pas inseré dans temp ".$sql." (".$db->errno.") ".$db->error."\n";
		trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}
}

//supprimer dernier train trouvé dans temp
function supprime_dernier ($gare, $selected_direction) {
	global $db;
	
	//On supprime l'ancien si il existe
	$sql = "DELETE FROM temp WHERE stop = '$gare' AND dir = '$selected_direction'";
	if (!$db->query($sql)) {
		//On a un probléme a traiter
		echo date("Y-m-d H:i:s"). "train pas supprimé de temp ".$sql." (".$db->errno.") ".$db->error."\n";
		trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}
}

//Fonction qui permet de stocker les points de la courbe.
function statistiques ($gare, $dir, $quality){
	global $db;
	
	$une_heure_en_moins = time() - 60 * 60 ;
	
	//On cherche si ça existe dans les trains passés
	$sql = "SELECT COUNT(train) as nb FROM passages ";
	$sql .= "WHERE dir = '$dir' ";
	$sql .= "AND stop = '$gare' ";
	$sql .= "AND time BETWEEN '".date("Y-m-d H:i:s",$une_heure_en_moins)."' AND '".date("Y-m-d H:i:s")."'";
	
	$result = $db->query($sql);
	if($result->num_rows > 0) {
		echo date("Y-m-d H:i:s"). "Requêtes pour les Statistiques ".$sql."\n";
		$row = $result->fetch_assoc();
		//On insert le nombre de train dans la derniére heure 
		$sql = "INSERT INTO meteo (gare, dir, nombre, quality) ";
		$sql .= "VALUES ('$gare', '$dir', '".$row["nb"]."', '$quality')";
		
		if ($db->query($sql) === TRUE) {
			// ça a marché
			echo date("Y-m-d H:i:s")."Statistique inseré ".$sql."\n";
		}else{
			//On a un probléme a traiter
			echo date("Y-m-d H:i:s"). "Statistique pas inseré ".$sql." (".$db->errno.") ".$db->error."\n";
			trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
		}
	}else{
		echo date("Y-m-d H:i:s"). "Statistiques à zéro ???? ".$sql."\n";
	}
	
	//On cherche si ça existe dans les trains passés
	$sql = "SELECT COUNT(train) as nb FROM passages_prevus ";
	$sql .= "WHERE dir = '$dir' ";
	$sql .= "AND stop = '$gare' ";
	$sql .= "AND time BETWEEN '".date("Y-m-d H:i:s",$une_heure_en_moins)."' AND '".date("Y-m-d H:i:s")."'";
	
	$result = $db->query($sql);
	if($result->num_rows > 0) {
		echo date("Y-m-d H:i:s"). "Requêtes pour les Statistiques prévues ".$sql."\n";
		$row = $result->fetch_assoc();
		//On insert le nombre de train dans la derniére heure 
		$sql = "INSERT INTO meteo_prevus (gare, dir, nombre, quality) ";
		$sql .= "VALUES ('$gare', '$dir', '".$row["nb"]."', '1')";
		
		if ($db->query($sql) === TRUE) {
			// ça a marché
			echo date("Y-m-d H:i:s")."Statistique prévues inseré ".$sql."\n";
		}else{
			//On a un probléme a traiter
			echo date("Y-m-d H:i:s"). "Statistique prévues pas inseré ".$sql." (".$db->errno.") ".$db->error."\n";
			trigger_error("Erreur d'insertion dans la BD ".$sql." (".$db->errno.") ".$db->error);
		}
	}else{
		echo date("Y-m-d H:i:s"). "Statistiques prévues à zéro ???? ".$sql."\n";
	}
}

//On lance les hostilités
function recherche_trains($gare, $selected_arret, $direction, $selected_direction, $statistique ){
	global $quais, $arrets ;
	
	$minute = (int) date('i');
	$modulo_5 = $minute % 5;
	
	//On lance la requête
	$answer = prim_retrive ($gare);
	$trains_triées = tri_trains ($answer,"STIF:Line::C01743:", $direction, $selected_direction, $quais, $arrets, $selected_arret );

	//On recherche l'ancien premier de la file
	$dernier_dans_temp = recherche_dernier ($gare, $selected_direction);
		
	if (!empty($trains_triées)){
		$demarrage = 0;

		foreach ($trains_triées as $train){
			//print_r ($train);
			//On regarde si le train est à quai
			if ($train["a_quai"] == " A quai"){
				echo date("Y-m-d H:i:s"). "le train est à quai direction: $selected_direction\n";
				//print_r ($train);
				enregistre_train ($gare, $selected_direction, $train["mission"], $train["destination"], 1);
				// On le supprime si il est déjà dans temp
				if ($dernier_dans_temp["train"] == $train["mission"]){
					supprime_dernier ($gare, $selected_direction);
				}
			}	
			
			if ($demarrage == 0) {
								
				//Si le dernier dans temp n'est pas le même que l'actuel, on insert
				if (($dernier_dans_temp != false) && ($dernier_dans_temp["train"] != $train["mission"])){
					enregistre_train ($gare, $selected_direction, $dernier_dans_temp["train"], $dernier_dans_temp["terminus"], 2);
					supprime_dernier ($gare, $selected_direction);
				}
				
				//timestamp du train 
				$difference_train_actuel = strtotime($train["heure"]) - strtotime(date('d-m-Y H:i:s'));
				
				//Si le train actuelle est supprimé ou en retard
				if (($train["attente"] == "Supprimé") || ($train["attente"] == "Retardé")) {
					supprime_dernier ($gare, $selected_direction);
				}else{				
					//Si le train actuel n'est pas à quai et avec une heure d'arrivée inférieur à 1 minute on l'insert dans temp
					if (($train["a_quai"] != " A quai") && ($difference_train_actuel < 60) && ($dernier_dans_temp["train"] != $train["mission"])) {
						insert_dernier ($gare, $selected_direction, $train["mission"], $train["destination"]);
					}
				}
			}
			
			$demarrage++;
		}	
		if (($statistique == 1) && ($modulo_5 == 0)) {
			statistiques ($gare, $selected_direction, 1);
		}
	}else{
		echo date("Y-m-d H:i:s"). "Pas de trains renvoyé direction: $selected_direction\n";
		//On enregistre le dernier
		if ($dernier_dans_temp["train"] != false) {
			enregistre_train ($gare, $selected_direction, $dernier_dans_temp["train"], $dernier_dans_temp["terminus"], 2);
			supprime_dernier ($gare, $selected_direction);
		}
		if (($statistique == 1) && ($modulo_5 == 0)) {
			statistiques ($gare, $selected_direction, 0);
		}
	}
}

function lance($statistique) {
	
	$gares = array (
		1 => array ( 	0 => 43097,
						1 => 27),
		2 => array ( 	0 => 43071,
						1 => 10),
		3 => array ( 	0 => 43833,
						1 => 19));
						
	foreach ($gares as $gare_temp){
		
		$gare = $gare_temp[0];
		$selected_arret = $gare_temp[1];
		//On commence par la partie Nord
		$direction = 'AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
		$selected_direction = 'N';

		recherche_trains($gare, $selected_arret, $direction, $selected_direction, $statistique );

		//Ensuite direction sud
		$direction='ROBINSON-SAINT REMY LES CHEVREUSE';
		$selected_direction = 'S';

		recherche_trains($gare, $selected_arret, $direction, $selected_direction, $statistique );
	}
}
	
	

/******************* Début du script *********************/

//On regarde si on est dans un tranche horraire
$heure = (int) date('G');
$minute = (int) date('i');

if (($heure < 1) || ($heure >= 5) || (($heure == 1) && ($minute < 15))) {

	lance(1);
	
	//Sleep during x seconds to relaunch the scan at 30s
	$seconds = (int) date('s');
	if ($seconds < 20){
		$seconds = 20 - $seconds;
		sleep($seconds);
	}
	
	lance(0);
	
	//Sleep during x seconds to relaunch the scan at 30s
	$seconds = (int) date('s');
	if ($seconds < 40){
		$seconds = 40 - $seconds;
		sleep($seconds);
	}

	lance(0);
}