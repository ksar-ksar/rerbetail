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

$page_actuelle = 'meteo';

/******************* Début du script *********************/

$date='';
if (!empty($_GET["date"])){
	$date = date('Y-m-d', strtotime($_GET["date"]));
}else{
	$date = date('Y-m-d');
}
$today = date('Y-m-d');

$gare='';
if (!empty($_GET["arret"])){
	$selected_arret = $_GET["arret"]; 
	if (array_key_exists($selected_arret,$arrets)){
		$gare = $arrets[$selected_arret][1];
		$gare_nom = $arrets[$selected_arret][0];
	}
}

if (empty($gare)){
	$gare = 43097;
	$gare_nom = 'Bourg-la-Reine';
	$selected_arret = 27;
}

//On inclus le header
include 'inc/header.php';

?>
					<!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Météo du RER B à <?php echo $gare_nom; ?> </h1>
                    <p class="mb-4">La météo du RER B donne les passages entregistrés et prévus à un arret donné pour la journée considérée.<br />
					Attention, ces données ne peuvent pas être considérées comme fiable à 100%, c'est un reconstitutions sur la base des données récupérés, toutes les 20 secondes, sur la plate forme PRIM</p>

                    <!-- Content Row -->
                    <div class="row">
						<div class="col-xl-3 col-md-6 mb-4">
								<div class="card border-left-primary shadow h-100 py-2">
									<div class="card-body">
										<div class="row no-gutters align-items-center">
											<div class="col mr-2">
												<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
													Sélectionner une date</div>
												<div class="h5 mb-0 font-weight-bold text-gray-800">
													<form method="GET">													
														<input
														  type="date"
														  name="date"
														  min="2023-04-12"
														  max="<?php echo $today; ?>"
														  required pattern="\d{4}-\d{2}-\d{2}"
														  class="btn btn-info btn-icon-split"
														  value="<?php echo $date; ?>"/>
														<input type="submit" value=" Go " class="btn btn-primary btn-user btn-block" />
													</form> 
												</div>
											</div>
											<div class="col-auto">
												<i class="fas fa-calendar fa-2x text-gray-300"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
						<div class="col-lg-6">
							<!-- Area Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Passages direction Nord à <?php echo $gare_nom; ?> </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="PassageNord"></canvas>
                                    </div>
                                    <hr>
                                    Nombre de passages dans la derniére heure
                                </div>
                            </div>
							<div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Passages direction Sud à <?php echo $gare_nom; ?> </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="PassageSud"></canvas>
                                    </div>
                                    <hr>
                                    Nombre de passages dans la derniére heure
                                </div>
                            </div>
						</div>

                        <div class="col-lg-6">
							
							<!-- DataTable -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Liste des passages enregistrées direction Nord à <?php echo $gare_nom; ?></h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table-sm table-striped" id="TableNord" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th>Heure</th>
													<th>Prévue</th>
													<th>Mission</th>
													<th>Terminus</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Liste des passages enregistrées direction Sud à <?php echo $gare_nom; ?></h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table-sm table-striped" id="TableSud" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th>Heure</th>
													<th>Prévue</th>
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