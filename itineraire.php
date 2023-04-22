<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

//On inclus les configurations
include 'inc/conf.php';

//On inclus les fonctions
include 'inc/functions.php';

$page_actuelle = 'itineraire';

/******************* Début du script *********************/

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

//On inclus le header
include 'inc/header.php';

?>
                    <h1 class="h3 mb-2 text-gray-800">Prochains passages sur votre itinéraire du RER B</h1>
                    <p class="mb-4">Selectionnez une gare de départ et une gare d'arrivée pour découvrir les trains sur votre trajet.</p>
					<div class="row">
						<div class="col-lg-6">
							<!-- Selection -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Choix de l'itinéraire</h6>
								</div>
								<div class="card-body">
									<form method="GET">
										<label for="depart">Départ :</label>
										<select id="depart" name="depart" class="btn btn-secondary btn-icon-split">
									<?php
									$i = 1;
									foreach ($arrets as $arret){
										if (!empty($selected_depart)){
											if ($i == $selected_depart){
												echo '<option value="'.$i.'" selected>'.$arret[0]."</option>\n";
											}else{
												echo '<option value="'.$i.'">'.$arret[0]."</option>\n";
											}
										}else{
											echo '<option value="'.$i.'">'.$arret[0]."</option>\n";
										}
										$i++;
									}
									?>
										</select><br>
										<label for="arrivee">Arivée :</label>
										<select id="arrivee" name="arrivee" class="btn btn-secondary btn-icon-split">
									<?php
									$i = 1;
									foreach ($arrets as $arret){
										if (!empty($selected_arrivee)){
											if ($i == $selected_arrivee){
												echo '<option value="'.$i.'" selected>'.$arret[0]."</option>\n";
											}else{
												echo '<option value="'.$i.'">'.$arret[0]."</option>\n";
											}
										}else{
											echo '<option value="'.$i.'">'.$arret[0]."</option>\n";
										}
										$i++;
									}
									?>
										</select><br>
									  <input type="submit" value=" Go " class="btn btn-primary btn-user btn-block" />
									</form> 
								</div>
							</div>
						</div>

                        <div class="col-lg-6">
							
							<!-- DataTable -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Itinéraire de <?php echo $depart_nom; ?> à <?php echo $arrivee_nom; ?></h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table-sm table-striped" id="dataTable" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th>Arrêt</th>
													<th>Heure</th>
													<th></th>
													<th>Suivant</th>
													<th>Mission</th>
													<th>Terminus</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>

                </div>
                <!-- /.container-fluid -->
<?php
include 'inc/footer.php';