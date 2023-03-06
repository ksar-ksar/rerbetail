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

$page_actuelle = 'about';

/******************* Début du script *********************/

//On inclus le header
include 'inc/header.php';

?>
                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">A propos</h1>
                    <p class="mb-4">Suite à la disparition de l'exellent nanoratp.org, j'ai entrepris de récupper les informations de notre RER favoris sur l'open data d'Ile de France Mobilité (https://prim.iledefrance-mobilites.fr)
									<br>Malheureusement beaucoup d'informations ne sont pas disponibles, comme le fameux "A l'approche" qui était pourtant si pratique
									</p>
					<p class="mb-4 bg-gradient-warning text-gray-100 text-lg">Avertissement : Je ne suis pas développeur, ni webmaster. Uniquement bricoleur du dimanche ! Site hébergé sur mon serveur personnel. Je ne garantie aucun des services mise à disposition.</p>

					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">Todo List</h6>
						</div>
						<div class="card-body">
							<p class="mb-4">Tellement de choses à faire ....<br> Pour le moment j'ai en tête :
								<ul>
									<li>Traitement des erreurs</li>
									<li>Déménager le site sur un nom de domaine neutre</li>
									<li>Compter le nombre de requête par jours vers la platforme prim</li>
									<li>Récupérer les infos traffic du rer B</li>
									<li>Faire "la météo du rerb" en comptant le nombre de passage par heure dans une station donnée</li>
								</ul>
							</p>		
							<p class="mb-4">N'hesitez pas à m'envoyer un mail si vous avez des questions / envies : contact at rerbetail.fr</p>	
						</div>
					</div>
					
					<h1 class="h3 mb-2 text-gray-800">Versions</h1>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.1.0</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        06/03/2023
									 </div>
									<p class="mb-4">
										<ul> 
											<li>Rajout de la page météo du RER B pour Bourg-la-reine</li>
											<li>Ajout d'un bouton "Export" sur les listes</li>											
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.0.5</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        28/02/2023
									 </div>
									<p class="mb-4">
										<ul> 
											<li>Passage sur le nom de domaine définitif</li>
											<li>Ajout d'un bouton "recharger" plutot que le rechargement automatique pour dimminuer le nombre de requêtes</li>											
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.0.4</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        23/02/2023
									 </div>
									<p class="mb-4">
										<ul> 
											<li>Récupération des Messages affichés sur les écrans</li>		
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.0.3</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        21/02/2023
									 </div>
									<p class="mb-4">
										<ul> 
											<li>Amélioration de l'ergonomie pour les smartphone</li>		
											<li>Mise en place d'un cache de 20s pour les requetes vers PRIM</li>
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary">V 0.0.2</h6>
						</div>
						<div class="card-body">
							<div class="row no-gutters align-items-center">
                                 <div class="col mr-2">
                                     <div class="font-weight-bold text-success text-uppercase mb-1">
                                        19/02/2023
									 </div>
									<p class="mb-4">Itiniéraire en béta test</p>			
								</div>
							</div>
						</div>
					</div>
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
									<p class="mb-4">
										<ul> 
											<li>Permet de récupper les prochains départs sur une des gares du rer B</li>
											<li>Trié par direction</li>
										</ul>
									</p>			
								</div>
							</div>
						</div>
					</div>
					

                </div>
                <!-- /.container-fluid -->
<?php
include 'inc/footer.php';