<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

/********** Définition des constantes *********/

//Verbose débug
define('DEBUG', false);

//Tableau des arrets avec leur nom commercial et ArID
$arrets = array (
	1 => array("Aéroport CDG 2 TGV", 473364),
	2 => array("Aéroport CDG 1", 462398, "Aéroport Charles de Gaulle 1 (Terminal 3)"),
	3 => array("Parc des Expositions", 47878),
	4 => array("Villepinte", 58793),
	5 => array("Sevran-Beaudottes", 43193),
	6 => array("Mitry-Claye", 43164),
	7 => array("Villeparisis-Mitry-le-Neuf", 46725),
	8 => array("Vert-Galant", 46222),
	9 => array("Sevran-Livry", 43194),
	10 => array("Aulnay-sous-Bois", 43071),
	11 => array("Le Blanc-Mesnil", 46163),
	12 => array("Drancy", 43122),
	13 => array("Le Bourget", 43231),
	14 => array("La Courneuve-Aubervilliers", 43140),
	15 => array("La Plaine-Stade de France", 43145),
	16 => array("Gare du Nord", 462394),
	17 => array("Châtelet-Les Halles", 45102),
	18 => array("Saint-Michel", 44877),
	19 => array("Luxembourg", 43833),
	20 => array("Port Royal", 44500),
	21 => array("Denfert-Rochereau", 473890),
	22 => array("Cité Universitaire", 473843),
	23 => array("Gentilly", 45877),
	24 => array("Laplace", 43607),
	25 => array("Arcueil-Cachan", 43067),
	26 => array("Bagneux", 44493),
	27 => array("Bourg-la-Reine", 43097),
	28 => array("Sceaux", 59206),
	29 => array("Fontenay-aux-Roses", 43125),
	30 => array("Robinson", 43186),
	31 => array("Parc de Sceaux", 43177),
	32 => array("La Croix-de-Berny-Fresnes", 46007),
	33 => array("Antony", 43066),
	34 => array("Fontaine-Michalon", 43124),
	35 => array("Les Baconnets", 43228),
	36 => array("Massy-Verrières", 47940),
	37 => array("Massy-Palaiseau", 58774),
	38 => array("Palaiseau", 47009),
	39 => array("Palaiseau-Villebon", 43175),
	40 => array("Lozère", 474069),
	41 => array("Le Guichet", 43232),
	42 => array("Orsay-Ville", 43086),
	43 => array("Bures-sur-Yvette", 43103),
	44 => array("La Hacquinière", 47046),
	45 => array("Gif-sur-Yvette", 47888),
	46 => array("Courcelle-sur-Yvette", 47052),
	47 => array("Saint-Rémy-Lès-Chevreuse", 47889)
);

//Tableau des voies pour la zone RATP. Vu que la RATP ne remote pas le quai dans l'outil....
//En fontion du StoP point, donne le quai
$quais = array (
	// Saint remy
	474012 => "Voie 1",
	474011 => "Voie 2",
	474009 => "Voie 3",
	// Orsay Ville
	473901 => "Voie 2",
	473903 => "Voie 1",
	473904 => "Voie Z",
	// Massy Palaiseau
	474056 => "Voie 2",
	474058 => "Voie A",
	474060 => "Voie 1",
	474061 => "Voie B",
	// Robinson
	474024 => "Voie 1B",
	474025 => "Voie 4",
	474026 => "Voie 2B",
	// Bourg-la-Reine
	473969 => "Voie 1",
	473970 => "Voie 2",
	473971 => "Voie 1B",
	473972 => "Voie 2B",
	// Laplace
	473926 => "Voie Z",
	473927 => "Voie 2",
	473932 => "Voie 1",
	// Denfert-Rochereau
	473994 => "Voie 1",
	473997 => "Voie 2",
	480170 => "Voie 3"
	);