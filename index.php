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

/******************* Début du script *********************/
if (DEBUG){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

$gare='';
if (!empty($_GET["arret"])){
	$selected_arret = $_GET["arret"]; 
	if (array_key_exists($selected_arret,$arrets)){
		$gare = $arrets[$selected_arret][1];
		$gare_nom = $arrets[$selected_arret][0];
	}
}
$direction='';
if (!empty($_GET["direction"])){
	$selected_direction = $_GET["direction"]; 
	if ($selected_direction == 'N'){
		$direction='AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
	}else{
		$direction='ROBINSON-SAINT REMY LES CHEVREUSE';
	}
}

if (empty($gare)){
	$gare = 45102;
	$gare_nom = 'Châtelet-Les Halles';
	$selected_arret = 17;
}
if (empty($direction)){
	$direction = 'AEROPORT CH.DE GAULLE 2-MITRY CLAYE';
	$selected_direction = 'N';
}

//On inclus le header
include 'inc/header.php';

?>
                    <div class="row">
						<div class="col-lg-6">
							<!-- Selection -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Choix de l'arrêt</h6>
								</div>
								<div class="card-body">
									<form method="GET">
										<label for="arret">Arret :</label>
										<select id="arret" name="arret" class="btn btn-secondary btn-icon-split">
									<?php
									$i = 1;
									foreach ($arrets as $arret){
										if (!empty($selected_arret)){
											if ($i == $selected_arret){
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
										</select>
										<label for="direction">Direction :</label>
										<select id="direction" name="direction" class="btn btn-secondary btn-icon-split">
											<option value="S" 
									<?php
										if ($direction == "ROBINSON-SAINT REMY LES CHEVREUSE"){echo " selected";}
									?>>ROBINSON-SAINT REMY</option>
											<option value="N"		
									<?php
										if ($direction == "AEROPORT CH.DE GAULLE 2-MITRY CLAYE"){echo " selected";}
									?>>AEROPORT-MITRY CLAYE</option>
										</select>
									  <input type="submit" value=" Go " class="btn btn-primary btn-user btn-block" />
									</form> 
								</div>
							</div>
						</div>

                        <div class="col-lg-6">
							
							<!-- DataTable -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Prochains passages à <?php echo $gare_nom; ?></h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table-sm table-striped" id="dataTable" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th>Mission</th>
													<th>Destination</th>
													<th>Heure</th>
													<th>Attente</th>
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
$footer_javascript = true;
include 'inc/footer.php';