<?php
/**********************************************************************************
*		Script pour extraire les prochains passages avec le plus de détails possible
*		En attendant le retour de NanoRatp
*
*		ksar <ksar.ksar@gmail.com>
************************************************************************************/

$messages = prim_retrive_messages ();
$nb_messages = count($messages);


?>
<!DOCTYPE html>
<html lang="fr">

<head>

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-2ZLXM7S00E"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-2ZLXM7S00E');
	</script>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Permet de connaitre les horraire ddes prochains passage des RER B">
    <meta name="author" content="ksar.ksar@gmail.com">
	
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">

    <title>RER Bétail</title>

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3">RER</div>
				<div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-b"></i>
                </div>
                <div class="sidebar-brand-text mx-3">étail</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Temps de passage -->
            <li class="nav-item <?php if ($page_actuelle == 'index') { echo "active";}  ?>">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Temps de passage</span></a>
            </li>

            <!-- Nav Item - Itinéraire -->
            <li class="nav-item <?php if ($page_actuelle == 'itineraire') { echo "active";}  ?>">
                <a class="nav-link" href="itineraire.php">
                    <i class="fas fa-fw fa-route"></i>
                    <span>Itinéraire</span></a>
            </li>
			
			<!-- Divider -->
            <hr class="sidebar-divider">
			
			<!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-cloud-sun"></i>
                    <span>Météo</span>
                </a>
                <div id="collapseUtilities" class="collapse<?php if ($page_actuelle == 'meteo') { echo " show";}  ?>" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Arrêts:</h6>
                        <a class="collapse-item <?php if (($page_actuelle == 'meteo') && ($gare == 43097)) { echo "active";}  ?>" href="meteo.php?arret=27">Bourg-la-reine</a>
						<a class="collapse-item <?php if (($page_actuelle == 'meteo') && ($gare == 43071)) { echo "active";}  ?>" href="meteo.php?arret=10">Aulnay-Sous-Bois</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">
			
            <!-- Nav Item - A propos -->
            <li class="nav-item <?php if ($page_actuelle == 'about') { echo "active";}  ?>">
                <a class="nav-link" href="about.php">
                    <i class="fas fa-fw fa-question"></i>
                    <span>A propos</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <form class="form-inline">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
						
						<!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter"><?php echo $nb_messages; ?></span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Liste des Messages
                                </h6>
<?php
foreach ($messages as $message){
	
	if ($message->InfoChannelRef->value == "Information"){
		$icon = "fa-circle-info";
		$bg = "bg-info";
	}else if ($message->InfoChannelRef->value == "Perturbation"){
		$icon = "fa-person-digging";
		$bg = "bg-warning";
	}else if ($message->InfoChannelRef->value == "Commercial"){
		$icon = "fa-cash-register";
		$bg = "bg-primary";
	}else{
		$icon = "fa-comment";
		$bg = "bg-secondary";
	}
?>
                                <span class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle <?php echo $bg; ?>">
                                            <i class="fas <?php echo $icon; ?> text-white"></i>
                                        </div>
                                    </div>
                                   <div>
                                   <?php echo nl2br($message->Content->Message[0]->MessageText->value)."\n"; ?>
                                    </div>
                                </span>
<?php
}
?>
                            </div>
                        </li>
						
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <span class="nav-link dropdown-toggle" id="userDropdown" >
                                <span class="mr-2 d-none d-inline text-gray-600 ">RER</span>
                                <img class="img-profile"
                                    src="img/RER_B_couleur_RVB.svg">
								<span class="mr-2 d-none d-inline text-gray-600">éTAIL</span>
                            </span>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">