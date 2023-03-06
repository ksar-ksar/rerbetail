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
	<script src="vendor/datatables/buttons.html5.min.js"></script>
	<script src="vendor/datatables/jszip.min.js"></script>

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
							className: 'btn btn-success btn-user btn-half-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        },
						{
				            extend: 'excel',
				            text: 'Export',
				            className: 'btn btn-info btn-user btn-half-block',
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
	<script src="vendor/datatables/buttons.html5.min.js"></script>
	<script src="vendor/datatables/jszip.min.js"></script>
	
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
							className: 'btn btn-success btn-user btn-half-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        },
						{
				            extend: 'excel',
				            text: 'Export',
				            className: 'btn btn-info btn-user btn-half-block',
				        }
				    ]
		   		 });
		
		/*setInterval( function () {
		    table.ajax.reload( null, false ); // user paging is not reset on reload
		}, 30000 );*/
	</script>
	
<?php } ?>

<?php if ($page_actuelle == 'meteo') { ?>
    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
	<script src="vendor/datatables/buttons.bootstrap4.min.js"></script>
	<script src="vendor/datatables/dataTables.buttons.min.js"></script>
	<script src="vendor/datatables/buttons.html5.min.js"></script>
	<script src="vendor/datatables/jszip.min.js"></script>
	
	<script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
		// Call the dataTables jQuery plugin
		  var table = $('#TableNord').DataTable({
		        ajax: 'ajax/passages.php?chart=table&gare=<?php echo $gare; ?>&direction=N',
				columns: [
			        { data: 'Heure' },
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
				ordering: true,
				lengthChange: false,
				paging: true,
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
							className: 'btn btn-success btn-user btn-half-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        },
						{
				            extend: 'excel',
				            text: 'Export',
				            className: 'btn btn-info btn-user btn-half-block',
				        }
				    ]
		   		 });
		var table = $('#TableSud').DataTable({
		        ajax: 'ajax/passages.php?chart=table&gare=<?php echo $gare; ?>&direction=S',
				columns: [
			        { data: 'Heure' },
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
				ordering: true,
				lengthChange: false,
				paging: true,
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
							className: 'btn btn-success btn-user btn-half-block',
				            action: function ( e, dt, node, config ) {
				                dt.ajax.reload();
				            }
				        },
						{
				            extend: 'excel',
				            text: 'Export',
				            className: 'btn btn-info btn-user btn-half-block',
				        }
				    ]
		   		 });
		
			// Set new default font family and font color to mimic Bootstrap's default styling
			Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
			Chart.defaults.global.defaultFontColor = '#858796';
			
			$(document).ready(function () {
				showGraph();
				showGraph2();
			});
			
			const showGraph = () => {
            {
                $.get("ajax/passages.php?chart=curve&gare=<?php echo $gare; ?>&direction=N",function (data)
                {
                    var date = [];
                    var value = [];
					var pointcolorget = [];

                    for (var i in data) {
                        date.push(data[i].date);
                        value.push(data[i].value);
						if (data[i].color == 1){
							pointcolorget.push("rgba(78, 115, 223, 1)");
						}else{
							pointcolorget.push("rgba(209, 212, 223, 1)");
						}
                    }

                    var chartdata = {
                        labels: date,
                        datasets: [{
                              label: 'Passages Direction Nord',
							  lineTension: 0.3,
							  backgroundColor: "rgba(78, 115, 223, 0.05)",
							  borderColor: "rgba(78, 115, 223, 1)",
							  pointRadius: 2,
							  pointBorderColor: "rgba(78, 115, 223, 1)",
							  pointHoverRadius: 3,
							  pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
							  pointHoverBorderColor: "rgba(78, 115, 223, 1)",
							  pointHitRadius: 10,
							  pointBorderWidth: 0,
                              data: value,
							  pointBackgroundColor: pointcolorget
                            }]

                    };

                    var graphTarget = $("#PassageNord");

                    var barGraph = new Chart(graphTarget, {
                        type: 'line',
                        data: chartdata,
						options: {
							maintainAspectRatio: false,
							layout: {
							  padding: {
								left: 10,
								right: 25,
								top: 25,
								bottom: 0
							  }
							},
							scales: {
							  xAxes: [{
								time: {
								  unit: 'date'
								},
								gridLines: {
								  display: false,
								  drawBorder: false
								},
								ticks: {
								  maxTicksLimit: 15
								}
							  }],
							  yAxes: [{
								ticks: {
								  maxTicksLimit: 5,
								  padding: 10,
								},
								gridLines: {
								  color: "rgb(234, 236, 244)",
								  zeroLineColor: "rgb(234, 236, 244)",
								  drawBorder: false,
								  borderDash: [2],
								  zeroLineBorderDash: [2]
								}
							  }],
							},
							legend: {
							  display: false
							},
							tooltips: {
							  backgroundColor: "rgb(255,255,255)",
							  bodyFontColor: "#858796",
							  titleMarginBottom: 10,
							  titleFontColor: '#6e707e',
							  titleFontSize: 14,
							  borderColor: '#dddfeb',
							  borderWidth: 1,
							  xPadding: 15,
							  yPadding: 15,
							  displayColors: false,
							  intersect: false,
							  mode: 'index',
							  caretPadding: 10
							  }
							}

                    });

                });
            }
        }
		
		const showGraph2 = () => {
            {
                $.get("ajax/passages.php?chart=curve&gare=<?php echo $gare; ?>&direction=S",function (data2)
                {
                    var date2 = [];
                    var value2 = [];
					var pointcolorget2 = [];

                    for (var i in data2) {
                        date2.push(data2[i].date);
                        value2.push(data2[i].value);
						if (data2[i].color == 1){
							pointcolorget2.push("rgba(78, 115, 223, 1)");
						}else{
							pointcolorget2.push("rgba(209, 212, 223, 1)");
						}
                    }

                    var chartdata2 = {
                        labels: date2,
                        datasets: [{
                              label: 'Passages Direction Sud',
							  lineTension: 0.3,
							  backgroundColor: "rgba(78, 115, 223, 0.05)",
							  borderColor: "rgba(78, 115, 223, 1)",
							  pointRadius: 2,
							  pointBorderColor: "rgba(78, 115, 223, 1)",
							  pointHoverRadius: 3,
							  pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
							  pointHoverBorderColor: "rgba(78, 115, 223, 1)",
							  pointHitRadius: 10,
							  pointBorderWidth: 0,
                              data: value2,
							  pointBackgroundColor: pointcolorget2
                            }]

                    };

                    var graphTarget2 = $("#PassageSud");

                    var barGraph2 = new Chart(graphTarget2, {
                        type: 'line',
                        data: chartdata2,
						options: {
							maintainAspectRatio: false,
							layout: {
							  padding: {
								left: 10,
								right: 25,
								top: 25,
								bottom: 0
							  }
							},
							scales: {
							  xAxes: [{
								time: {
								  unit: 'date'
								},
								gridLines: {
								  display: false,
								  drawBorder: false
								},
								ticks: {
								  maxTicksLimit: 15
								}
							  }],
							  yAxes: [{
								ticks: {
								  maxTicksLimit: 5,
								  padding: 10,
								},
								gridLines: {
								  color: "rgb(234, 236, 244)",
								  zeroLineColor: "rgb(234, 236, 244)",
								  drawBorder: false,
								  borderDash: [2],
								  zeroLineBorderDash: [2]
								}
							  }],
							},
							legend: {
							  display: false
							},
							tooltips: {
							  backgroundColor: "rgb(255,255,255)",
							  bodyFontColor: "#858796",
							  titleMarginBottom: 10,
							  titleFontColor: '#6e707e',
							  titleFontSize: 14,
							  borderColor: '#dddfeb',
							  borderWidth: 1,
							  xPadding: 15,
							  yPadding: 15,
							  displayColors: false,
							  intersect: false,
							  mode: 'index',
							  caretPadding: 10
							  }
							}
                    });

                });
            }
		}
	</script>
	
<?php } ?>
</body>

</html>