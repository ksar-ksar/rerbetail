<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

// Combien de requetes  ?
$sql = "SELECT * FROM requetes_temps_reel WHERE date = '".date("Y-m-d")."'";

if ($result = $db->query($sql)) {
	$row = $result->fetch_assoc();
	$requetes_auj = $row["nombre"];
}else{
	trigger_error("Pas de résultats SQL ".$sql." (".$db->errno.") ".$db->error);
}

// Combien de messages ?
$sql = "SELECT * FROM requetes_messages WHERE date = '".date("Y-m-d")."'";

if ($result = $db->query($sql)) {
	$row = $result->fetch_assoc();
	$messages_auj = $row["nombre"];
}else{
	trigger_error("Pas de résultats SQL ".$sql." (".$db->errno.") ".$db->error);
}

//Comptage des visiteurs
visiteur_comptage($page_actuelle);

// Combien de visiteurs aujourd'hui
$sql = "SELECT COUNT(DISTINCT ip) AS visiteur FROM `visiteur` WHERE DATE(`time`) = CURDATE()";
if ($result = $db->query($sql)) {
	$row = $result->fetch_assoc();
	$visiteurs_auj = $row["visiteur"];
}else{
	trigger_error("Pas de résultats SQL ".$sql." (".$db->errno.") ".$db->error);
}

?>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        Site fait pour mes propres besoin : <b>Je n'ai aucun lien avec la RATP / SNCF / Ile de France Mobilitée</b>
						<br>Nombre de requêtes API Temps réel aujourd'hui : <?php echo $requetes_auj ?> 
						/ requêtes info traffic aujourd'hui : <?php echo $messages_auj ?> 
						/ Visiteurs uniques aujourd'hui : <?php echo $visiteurs_auj ?> 
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

<?php if ($page_actuelle == 'index') { ?>
    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
	<script src="vendor/datatables/buttons.bootstrap4.min.js"></script>
	<script src="vendor/datatables/dataTables.buttons.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
		// Call the dataTables jQuery plugin
		  var table = $('#dataTable').DataTable({
		        ajax: 'ajax/next.php?arret=<?php echo $selected_arret; ?>&direction=<?php echo $selected_direction; ?>',
				columns: [
			        { data: 'Mission' },
			        { data: 'Destination' },
			        { data: 'Heure de passage' },
			        { data: 'Attente' }
			    ],
				language: {
					    "decimal":        ",",
					    "emptyTable":     "Pas de données disponibles",
					    "info":           "Affiche de _START_ à _END_ sur _TOTAL_",
					    "infoEmpty":      "Affiche 0 à 0 sur 0",
					    "infoFiltered":   "(filtré de _MAX_ entrées totales)",
					    "infoPostFix":    "",
					    "thousands":      " ",
					    "lengthMenu":     "Affiche _MENU_ lignes",
					    "loadingRecords": "Chargement...",
					    "processing":     "Recherche des données encours...",
					    "search":         "Recherche:",
					    "zeroRecords":    "Pas d'entrée trouvée",
					    "paginate": {
					        "first":      "Premier",
					        "last":       "Dernier",
					        "next":       "Suivant",
					        "previous":   "Précédent"
					    },
					    "aria": {
					        "sortAscending":  ": activate to sort column ascending",
					        "sortDescending": ": activate to sort column descending"
					    }
					},
				ordering: false,
				lengthChange: false,
				paging: false,
				searching: false,
				processing: true,
				info: false,
				scrollX: false,
				columnDefs: [
					{"className": "compact row-border dt-center", "targets": "_all"}
				  ],
			  dom: 'B<"clear">frtip',
				buttons: [
				        {
				            text: 'Rafraichir',
							className: 'btn btn-primary btn-user btn-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        }
				    ]
		    });
		
		/*setInterval( function () {
		    table.ajax.reload( null, false ); // user paging is not reset on reload
		}, 20000 );*/
	</script>
	
<?php } ?>

<?php if ($page_actuelle == 'itineraire') { ?>
    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
	<script src="vendor/datatables/buttons.bootstrap4.min.js"></script>
	<script src="vendor/datatables/dataTables.buttons.min.js"></script>
	
    <!-- Page level custom scripts -->
    <script>
		// Call the dataTables jQuery plugin
		  var table = $('#dataTable').DataTable({
		        ajax: 'ajax/itineraire.php?depart=<?php echo $selected_depart; ?>&arrivee=<?php echo $selected_arrivee; ?>',
				columns: [
			        { data: 'Arret' },
			        { data: 'Heure' },
			        { data: 'Attente' },
			        { data: 'Suivant' },
					{ data: 'Mission' },
					{ data: 'Terminus' }
			    ],
				language: {
					    "decimal":        ",",
					    "emptyTable":     "Pas de données disponibles",
					    "info":           "Affiche de _START_ à _END_ sur _TOTAL_",
					    "infoEmpty":      "Affiche 0 à 0 sur 0",
					    "infoFiltered":   "(filtré de _MAX_ entrées totales)",
					    "infoPostFix":    "",
					    "thousands":      " ",
					    "lengthMenu":     "Affiche _MENU_ lignes",
					    "loadingRecords": "Chargement...",
					    "processing":     "Recherche des données encours...",
					    "search":         "Recherche:",
					    "zeroRecords":    "Pas d'entrée trouvée",
					    "paginate": {
					        "first":      "Premier",
					        "last":       "Dernier",
					        "next":       "Suivant",
					        "previous":   "Précédent"
					    },
					    "aria": {
					        "sortAscending":  ": activate to sort column ascending",
					        "sortDescending": ": activate to sort column descending"
					    }
					},
				ordering: false,
				lengthChange: false,
				paging: false,
				searching: false,
				processing: true,
				info: false,
				scrollX: false,
				columnDefs: [
					{"className": "compact row-border dt-center", "targets": "_all"}
				  ],
				dom: 'B<"clear">frtip',
				buttons: [
				        {
				            text: 'Rafraichir',
							className: 'btn btn-primary btn-user btn-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        }
				    ]
		   		 });
		
		/*setInterval( function () {
		    table.ajax.reload( null, false ); // user paging is not reset on reload
		}, 30000 );*/
	</script>
	
<?php } ?>
</body>

</html>