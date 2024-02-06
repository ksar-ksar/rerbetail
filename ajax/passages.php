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

/******************* Les fonctions ***********************/

//Retourne le where pour la journée
function time_where ($date) {
	
	$debut = mktime(3, 0, 0, date("m", strtotime($date)) , date("d", strtotime($date)) , date("Y", strtotime($date)));
	$fin = mktime(2, 59, 59, date("m", strtotime($date)) , date("d", strtotime($date))+1 , date("Y", strtotime($date)));
	$sql = "BETWEEN '".date("Y-m-d H:i:s",$debut)."' AND '".date("Y-m-d H:i:s",$fin)."'";
	
	return $sql;
}

/******************* Début du script *********************/

header('Content-Type: application/json');

//Comptage des visiteurs
visiteur_comptage('ajax_next');

$date='';
if (!empty($_GET["date"])){
	$date = date('Y-m-d', strtotime($_GET["date"]));
}else{
	$date = date('Y-m-d');
}
$today = date('Y-m-d');

$gare='';
if (!empty($_GET["gare"])){
	$selected_gare = $_GET["gare"]; 
	if ($selected_gare == 43097){
		$gare = 43097;
	}else if ($selected_gare == 43071){
		$gare = 43071;
	}else if ($selected_gare == 43833){
		$gare = 43833;
	}else if ($selected_gare == 58774){
		$gare = 58774;
	}else if ($selected_gare == 47889){
		$gare = 47889;
	}else if ($selected_gare == 43164){
		$gare = 43164;
	}else if ($selected_gare == 473364){
		$gare = 473364;
	}else if ($selected_gare == 43186){
		$gare = 43186;
	}else if ($selected_gare == 43086){
		$gare = 43086;
	}else if ($selected_gare == 43145){
		$gare = 43145;
	}else if ($selected_gare == 473890){
		$gare = 473890;
	}else if ($selected_gare == 43607){
		$gare = 43607;
	}else{
		$gare = false;
	}
}
//echo "gare : $gare \n";
$direction='';
if (!empty($_GET["direction"])){
	$selected_direction = $_GET["direction"]; 
	if ($selected_direction == 'N'){
		$direction='N';
	}else{
		$direction='S';
	}
}
//echo "direction : $direction \n";
$chart='';
if (!empty($_GET["chart"])){
	$selected_type = $_GET["chart"]; 
	if ($selected_type == 'table'){
		$chart='table';
	}else{
		$chart='curve';
	}
}
//echo "chart : $chart \n";

$data = [];

if ($gare != false){
	//Type de graphique
	if ($chart == 'table') {
		//On cherche les données
		$sql = "SELECT p.time as Heure, t.time as Prevue, p.train as Mission, p.terminus as Terminus ";
		$sql .= "FROM passages p ";
		$sql .= "LEFT JOIN passages_prevus t ON p.train = t.train AND p.stop = t.stop ";
		$sql .= "AND t.time ".time_where ($date);
		$sql .= "WHERE p.dir = '$direction' ";
		$sql .= "AND p.stop = '$gare' ";
		$sql .= "AND p.time ".time_where ($date);
		$sql .= " ORDER BY p.id";
		
		//echo "sql : $sql \n";
		
		$i=0;
		$result = $db->query($sql);
		while ($row = $result->fetch_assoc()) {
			$data["data"][$i]["Heure"] = date("d/m/Y H:i",strtotime($row["Heure"]));
			if (empty($row["Prevue"])){
				$data["data"][$i]["Prévue"] = "";
			}else{
				$data["data"][$i]["Prévue"] = date("d/m/Y H:i",strtotime($row["Prevue"]));
			}
			$data["data"][$i]["Mission"] = $row["Mission"];
			$data["data"][$i]["Terminus"] = $row["Terminus"];
			$i++;
		}
		if ($i == 0){
			$data["data"][0]["Heure"]="Pas de train enregistré";
			$data["data"][0]["Prévue"] = "";
			$data["data"][0]["Mission"]="";
			$data["data"][0]["Terminus"] = "";
		}
	}else{
		//On cherche les données
		$sql = "SELECT  time as date, nombre as value, quality FROM meteo ";
		$sql .= "WHERE dir = '$direction' ";
		$sql .= "AND gare = '$gare' ";
		$sql .= "AND time ".time_where ($date);
		$sql .= " ORDER BY id ASC";
		//echo "sql : $sql \n";
		$result = $db->query($sql);
		$i=0;
		while ($row = $result->fetch_assoc()) {
			$data[$i]["date"] = date("H:i",strtotime($row["date"]));
			$data[$i]["value"] = $row["value"];
			$data[$i]["color"] = $row["quality"];
			$i++;
		}
		//On cherche les données prévisionelles
		$sql = "SELECT  time as date, nombre as value FROM meteo_prevus ";
		$sql .= "WHERE dir = '$direction' ";
		$sql .= "AND gare = '$gare' ";
		$sql .= "AND time ".time_where ($date);
		$sql .= " ORDER BY id ASC";
		//echo "sql : $sql \n";
		$result = $db->query($sql);
		while ($row = $result->fetch_assoc()) {
			$data[$i]["date"] = date("H:i",strtotime($row["date"]));
			$data[$i]["value"] = $row["value"];
			$data[$i]["color"] = 2;
			$i++;
		}
	}
}else{
	if ($chart == 'table') {
		$data["data"][0]["Heure"]="Mauvaise gare sélectionnée";
		$data["data"][0]["Mission"]="";
		$data["data"][0]["Terminus"] = "";
	}
}
echo json_encode($data);