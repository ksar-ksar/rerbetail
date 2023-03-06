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

/******************* Début du script *********************/

header('Content-Type: application/json');

//Comptage des visiteurs
visiteur_comptage('ajax_next');

$gare='';
if (!empty($_GET["gare"])){
	$selected_gare = $_GET["gare"]; 
	if ($selected_gare == 43097){
		$gare = 43097;
	}else if ($selected_gare == 43071){
		$gare = 43071;
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
		$sql = "SELECT  time as Heure, train as Mission, terminus as Terminus FROM passages ";
		$sql .= "WHERE dir = '$direction' ";
		$sql .= "AND stop = '$gare' ";
		$sql .= "AND time ".time_where ();
		$sql .= " ORDER BY id";
		
		//echo "sql : $sql \n";
		
		$i=0;
		$result = $db->query($sql);
		while ($row = $result->fetch_assoc()) {
			$data["data"][$i]["Heure"] = date("H:i",strtotime($row["Heure"]));
			$data["data"][$i]["Mission"] = $row["Mission"];
			$data["data"][$i]["Terminus"] = $row["Terminus"];
			$i++;
		}
		if ($i == 0){
			$data["data"][0]["Heure"]="Pas de train enregistré";
			$data["data"][0]["Mission"]="";
			$data["data"][$i]["Terminus"] = "";
		}
	}else{
		//On cherche les données
		$sql = "SELECT  time as date, nombre as value, quality FROM meteo ";
		$sql .= "WHERE dir = '$direction' ";
		$sql .= "AND gare = '$gare' ";
		$sql .= "AND time ".time_where ();
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
	}
}else{
	if ($chart == 'table') {
		$data["data"][0]["Heure"]="Mauvaise gare sélectionnée";
		$data["data"][0]["Mission"]="";
	}
}
echo json_encode($data);