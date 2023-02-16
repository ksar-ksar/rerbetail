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

//On inclus le header
include 'inc/header.php';

?>
                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">A propos</h1>
                    <p class="mb-4">Suite à la disparition de l'exellent nanoratp.org, j'ai entrepris de récupper les informations de notre RER favoris sur l'open data d'Ile de France Mobilité (https://prim.iledefrance-mobilites.fr) </p>
					<p class="mb-4">Malheureusement beaucoup d'informations ne sont pas disponibles, comme le fameux "A l'approche" qui était pourtant si pratique</p>

					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">Todo List</h6>
						</div>
						<div class="card-body">
							<p class="mb-4">Tellement de choses à faire ....</p>		
							<p class="mb-4">N'hesitez pas à m'envoyer un mail si vous avez des questions / envies : ksar.ksar at gmail.com</p>	
						</div>
					</div>
					
					<h1 class="h3 mb-2 text-gray-800">Versions</h1>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.0.1</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        16/02/2023
									 </div>
									<p class="mb-4">Permet de récupper les prochains départs sur une des gares du rer B <br> Trié par direction</p>			
								</div>
							</div>
						</div>
					</div>

                </div>
                <!-- /.container-fluid -->
<?php
$footer_javascript = false;
include 'inc/footer.php';