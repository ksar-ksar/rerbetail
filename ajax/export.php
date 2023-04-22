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
visiteur_comptage('export');

$date='';
if (!empty($_GET["date"])){
	$date = date('Y-m-d', strtotime($_GET["date"]));
}else{
	$date = date('Y-m-d');
}
$today = date('Y-m-d');

$type='';
if (!empty($_GET["type"])){
	$type = $_GET["type"]; 
}

$display=array();
if ($type == 'prevus'){
	$i=0;
	$sql  = "SELECT time AS Heure, dir as Direction, ";
	$sql .= "CASE ";
	$sql .= "	WHEN stop = 43097 THEN 'Bourg-la-Reine'";
	$sql .= "	WHEN stop = 43071 THEN 'Aulnay-Sous-Bois'";
	$sql .= "	WHEN stop = 43833 THEN 'Luxembourg'";
	$sql .= "	WHEN stop = 58774 THEN 'Massy-Palaiseau'";
	$sql .= "	WHEN stop = 47889 THEN 'Saint-Rémy-Lès-Chevreuse'";
	$sql .= "	WHEN stop = 43164 THEN 'Mitry-Claye'";
	$sql .= "	ELSE 'Pas trouvé'";
	$sql .= "END AS Gare,";
	$sql .= "train as Mission, terminus as Terminus ";
	$sql .= "FROM passages_prevus ";
	$sql .= "WHERE time ".time_where ($date);
	$sql .= " ORDER BY Heure";
	$reponse = $db->query($sql);
		
	if (!$reponse) {
		//On a un probléme a traiter
		trigger_error("Erreur de lecture dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}else{
		while ($donnees = $reponse->fetch_assoc()){
			$display["data"][$i]["Heure"] = $donnees['Heure'];
			$display["data"][$i]["Direction"] = $donnees['Direction'];
			$display["data"][$i]["Gare"] = $donnees['Gare'];
			$display["data"][$i]["Mission"] = $donnees['Mission'];
			$display["data"][$i]["Terminus"] = $donnees['Terminus'];
			$i++;
		}
	}

	if ($i == 0){
		$display["data"][0]["Heure"]="Pas de trains trouvés";
		$display["data"][$i]["Direction"] = "";
		$display["data"][$i]["Gare"] = "";
		$display["data"][$i]["Mission"] = "";
		$display["data"][$i]["Terminus"] = "";
	}
}

if ($type == 'enreg'){
	$i=0;
	$sql  = "SELECT time AS Heure, dir as Direction, ";
	$sql .= "CASE ";
	$sql .= "	WHEN stop = 43097 THEN 'Bourg-la-Reine'";
	$sql .= "	WHEN stop = 43071 THEN 'Aulnay-Sous-Bois'";
	$sql .= "	WHEN stop = 43833 THEN 'Luxembourg'";
	$sql .= "	WHEN stop = 58774 THEN 'Massy-Palaiseau'";
	$sql .= "	WHEN stop = 47889 THEN 'Saint-Rémy-Lès-Chevreuse'";
	$sql .= "	WHEN stop = 43164 THEN 'Mitry-Claye'";
	$sql .= "	ELSE 'Pas trouvé'";
	$sql .= "END AS Gare,";
	$sql .= "train as Mission, terminus as Terminus ";
	$sql .= "FROM passages ";
	$sql .= "WHERE time ".time_where ($date);
	$sql .= " ORDER BY Heure";
	$reponse = $db->query($sql);
		
	if (!$reponse) {
		//On a un probléme a traiter
		trigger_error("Erreur de lecture dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}else{
		while ($donnees = $reponse->fetch_assoc()){
			$display["data"][$i]["Heure"] = $donnees['Heure'];
			$display["data"][$i]["Direction"] = $donnees['Direction'];
			$display["data"][$i]["Gare"] = $donnees['Gare'];
			$display["data"][$i]["Mission"] = $donnees['Mission'];
			$display["data"][$i]["Terminus"] = $donnees['Terminus'];
			$i++;
		}
	}

	if ($i == 0){
		$display["data"][0]["Heure"]="Pas de trains trouvés";
		$display["data"][$i]["Direction"] = "";
		$display["data"][$i]["Gare"] = "";
		$display["data"][$i]["Mission"] = "";
		$display["data"][$i]["Terminus"] = "";
	}
}

if ($type == 'cons'){
	$i=0;
	$sql  = "SELECT IF(p.time IS NULL, t.time, p.time) AS Heure, p.time as Passage, t.time as Prevue, p.dir as Direction,";
	$sql .= "CASE ";
	$sql .= "	WHEN p.stop = 43097 THEN 'Bourg-la-Reine' ";
	$sql .= "	WHEN p.stop = 43071 THEN 'Aulnay-Sous-Bois' ";
	$sql .= "	WHEN p.stop = 43833 THEN 'Luxembourg' ";
	$sql .= "	WHEN p.stop = 58774 THEN 'Massy-Palaiseau'";
	$sql .= "	WHEN p.stop = 47889 THEN 'Saint-Rémy-Lès-Chevreuse'";
	$sql .= "	WHEN p.stop = 43164 THEN 'Mitry-Claye'";
	$sql .= "	ELSE 'Pas trouvé' ";
	$sql .= "END AS Gare, ";
	$sql .= "p.train as Mission, IF(t.terminus IS NULL, p.terminus, t.terminus) as Terminus ";
	$sql .= "FROM passages p ";
	$sql .= "LEFT JOIN passages_prevus t ON p.train = t.train AND p.stop = t.stop ";
	$sql .= "AND t.time ".time_where ($date)." ";
	$sql .= "WHERE p.time ".time_where ($date)." ";
	$sql .= "UNION ";
	$sql .= "SELECT IF(p.time IS NULL, t.time, p.time) AS Heure, p.time as Passage, t.time as Prevue, t.dir as Direction, ";
	$sql .= "CASE ";
	$sql .= "	WHEN t.stop = 43097 THEN 'Bourg-la-Reine' ";
	$sql .= "	WHEN t.stop = 43071 THEN 'Aulnay-Sous-Bois' ";
	$sql .= "	WHEN t.stop = 43833 THEN 'Luxembourg' ";
	$sql .= "	WHEN t.stop = 58774 THEN 'Massy-Palaiseau'";
	$sql .= "	WHEN t.stop = 47889 THEN 'Saint-Rémy-Lès-Chevreuse'";
	$sql .= "	WHEN t.stop = 43164 THEN 'Mitry-Claye'";
	$sql .= "	ELSE 'Pas trouvé' ";
	$sql .= "END AS Gare, ";
	$sql .= "t.train as Mission, IF(t.terminus IS NULL, p.terminus, t.terminus) as Terminus ";
	$sql .= "FROM passages p ";
	$sql .= "RIGHT JOIN passages_prevus t ON p.train = t.train AND p.stop = t.stop ";
	$sql .= "AND p.time ".time_where ($date)." ";
	$sql .= "WHERE t.time ".time_where ($date)." ";
	$sql .= "ORDER BY Heure";
	$reponse = $db->query($sql);
		
	if (!$reponse) {
		//On a un probléme a traiter
		trigger_error("Erreur de lecture dans la BD ".$sql." (".$db->errno.") ".$db->error);
	}else{
		while ($donnees = $reponse->fetch_assoc()){
			$display["data"][$i]["Heure"] = $donnees['Heure'];
			$display["data"][$i]["Passage"] = $donnees['Passage'];
			$display["data"][$i]["Prevue"] = $donnees['Prevue'];
			$display["data"][$i]["Direction"] = $donnees['Direction'];
			$display["data"][$i]["Gare"] = $donnees['Gare'];
			$display["data"][$i]["Mission"] = $donnees['Mission'];
			$display["data"][$i]["Terminus"] = $donnees['Terminus'];
			$i++;
		}
	}

	if ($i == 0){
		$display["data"][0]["Heure"]="Pas de trains trouvés";
		$display["data"][$i]["Passage"] = "";
		$display["data"][$i]["Prevue"] = "";
		$display["data"][$i]["Direction"] = "";
		$display["data"][$i]["Gare"] = "";
		$display["data"][$i]["Mission"] = "";
		$display["data"][$i]["Terminus"] = "";
	}
}
echo json_encode($display);