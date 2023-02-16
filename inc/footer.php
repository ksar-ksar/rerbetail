<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

?>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Site fait pour mes propres besoin. <br> <b>Je n'ai aucun lien avec la RATP / SNCF / Ile de France Mobilitée</b></span>
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

<?php if ($footer_javascript) { ?>
    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

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
				  ]
		    });
		
		setInterval( function () {
		    table.ajax.reload( null, false ); // user paging is not reset on reload
		}, 20000 );
	</script>
	
<?php } ?>
</body>

</html>