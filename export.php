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

$page_actuelle = 'export';

/******************* Début du script *********************/

$date='';
if (!empty($_GET["date"])){
	$date = date('Y-m-d', strtotime($_GET["date"]));
}else{
	$date = date('Y-m-d');
}
$today = date('Y-m-d');

//On inclus le header
include 'inc/header.php';

?>
 <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Export</h1>
                    <p class="mb-4">Page dédié à l'export des données</p>
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
						<!-- DataTable Prévus -->
						<div class="card shadow mb-4">
							<div class="card-header py-3">
								<h6 class="m-0 font-weight-bold text-primary">Liste des trains prévus (téléchargé la veille)</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="dataTable_prevus" width="100%" cellspacing="0">
										<thead>
											<tr>
												<th>Heure</th>
												<th>Direction</th>
												<th>Gare</th>
												<th>Mission</th>
												<th>Terminus</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
						<!-- DataTable Enregistrer -->
						<div class="card shadow mb-4">
							<div class="card-header py-3">
								<h6 class="m-0 font-weight-bold text-primary">Liste des trains passés dans une gare (Enregistrement sur toute la journée)</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="dataTable_enreg" width="100%" cellspacing="0">
										<thead>
											<tr>
												<th>Heure</th>
												<th>Direction</th>
												<th>Gare</th>
												<th>Mission</th>
												<th>Terminus</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
					<!-- DataTable Enregistrer -->
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">Liste des trains consolidés sur la journée</h6>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-striped" id="dataTable_cons" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th>Heure</th>
											<th>Passage</th>
											<th>Prevue</th>
											<th>Direction</th>
											<th>Gare</th>
											<th>Mission</th>
											<th>Terminus</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
                </div>
                <!-- /.container-fluid -->
<?php
include 'inc/footer.php';