<?php
/*
 |---------------------------------------------------------------
 | MENU ONLINE
 |---------------------------------------------------------------
 *
 * A super simple web application to display restaurant menu
 * 
 * Developed by Carlo Feniello in November 2020 during the Covid-19 pandemic
 * 
 * Made with UIKit (https://getuikit.com/)
 *
 * @author	Carlo Feniello (https://www.carlof.it)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @since	Version 1.0.0
 * 
 * 
 */

define('ENVIRONMENT', 'development');

switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		ini_set('log_errors',0);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		ini_set('log_errors',1);
		ini_set("error_log","error.log");
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

// define current directory as base path
chdir(dirname(__DIR__));
$base_path = __DIR__;
define('BASEPATH', $base_path);

try {
    if (!file_exists($base_path."/config/config.php"))
        throw new Exception ('Web page not available');
	else
		// laod configuration file
		$config = require_once($base_path.'/config/config.php');
		
}
catch(Exception $e) {    
    die($e->getMessage());
}

// laod data file
$data = simplexml_load_file($base_path."/data/menu.xml") or die("Menu non disponibile");

$categories = [];
$menu = [];

//fill categories
foreach($data as $elem) {
	if(!in_array($elem->category,$categories)) {
		array_push($categories,(string)$elem->category);
	}
	$json = json_encode($elem);
	$menu[(string)$elem->category][] = json_decode($json, TRUE);
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/uikit.min.css" />
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>
	<title>Naviga il nostro menu e ordina online | <?= $client['name'] ?></title>
	<meta name="description" content="<?= $client['meta_description'] ?>" />
	<meta name ="keywords" content="<?= $client['meta_keywords'] ?>" />

	<style>
		:root {
		--primary-color: <?= $client['primary_color'] ?>;
		--secondary-color: <?= $client['secondary_color'] ?>;
		}

		body {
			font-size: 14px;
		} 

		.uk-text-primary {
			color: var(--primary-color) !important;
		}

		.menu-category {
			font-weight: bold;
		}

		.uk-subnav-pill > .uk-active > a {
			background-color: var(--primary-color);
		}
		.order-by-whatsapp {
			background-color: #25D366;
		}
		.order-by-whatsapp:hover {
			background-color: #128C7E;
		}
	</style>
</head>
<body>
		<!-- INFO -->
		<section class="uk-section uk-section-default uk-box-shadow-small uk-section-xsmall">
			<div class="uk-container">
				<div class="uk-grid uk-grid-small uk-flex uk-flex-middle" data-uk-grid>
					<div class="uk-width-auto uk-margin-bottom">
						<img data-src="img/logo.png" alt="" width="180" height="68" uk-img>
					</div>
					<div class="uk-width-1-1 uk-width-expand@l uk-margin-bottom">
						<h4 class="uk-margin-remove"><?= $client['name'] ?></h4>
						<i class="uk-text-muted uk-text-small"><?= $client['address'] ?></i><br>
						<i class="uk-text-muted uk-text-small">
							<?php if($client['take_away']) : ?>
							<span class="uk-badge">Asporto</span> 
							<?php endif; ?>
							<?php if($client['home_delivery']): ?>
							<span class="uk-badge">Consegna domicilio</span>
							<?php endif; ?>
						</i>
					</div>
					<div class="uk-width-1-2 uk-width-1-6@l uk-margin-bottom">
						<span class="uk-text-success" data-uk-icon="icon:calendar; ratio: 0.8"></span><span class="uk-text-small uk-text-muted uk-text-bottom"> Apertura settimanale</span><br>
						<span class="uk-text-large uk-text-success"><?= $client['weekly_opening'] ?></span>
					</div>
					<div class="uk-width-1-2 uk-width-1-6@l uk-margin-bottom">
						<span class="uk-text-success" data-uk-icon="icon:location; ratio: 0.8"></span><span class="uk-text-small uk-text-muted  uk-text-bottom"> Orario</span><br>
						<span class="uk-text-large uk-text-success"><?= $client['opening_hours'] ?></span>
					</div>
					
					<div id="order-now" class="uk-width-auto@l uk-margin-bottom">
						<h4 class="uk-margin-remove">Ordina ora</h4>
						<div class="uk-button-group">
							<a href="tel:<?=$client['phone']?>" class="uk-button uk-button-primary order-by-phone"><span uk-icon="receiver"></span> Telefono</a>
							<a href="https://wa.me/<?=$client['whatsapp']?>?text=Ciao%20<?=$client['name']?>%20vorrei%20ordinare" class="uk-button uk-button-secondary order-by-whatsapp"><span uk-icon="whatsapp"></span> Whatsapp</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<hr class="uk-margin-remove">
		<!-- /INFO -->
		<section id="menu" class="uk-section uk-section-default">
			<div class="uk-container uk-container-small uk-text-center uk-margin-bottom">
				<h1>Il nostro Menu</h1>
				<hr class="uk-divider-icon">
			</div>
			
			<div class="uk-container">
				<div class="uk-section uk-section-small uk-padding-remove-top">
					<ul class="uk-subnav uk-subnav-pill uk-flex uk-flex-center" data-uk-switcher="connect: .uk-switcher; animation: uk-animation-fade">
						<?php foreach($menu as $category => $elem) : ?>
						<li><a class="uk-border-pill" href="#"><?= $category ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<ul class="uk-switcher uk-margin">
					<?php foreach($menu as $category => $item) : ?>
					<li>
						<div class="uk-grid uk-flex-middle" data-uk-scrollspy="target: > div; cls: uk-animation-slide-left-medium">
							<div class="uk-width-1-1" data-uk-scrollspy-class="uk-animation-slide-right-medium">
									<h6 class="uk-text-primary menu-category"><?= $category ?></h6>
									<?php foreach ($item as $item) : ?>
                                    <div class="" uk-grid>
										<div class="uk-width-expand">
                                            <h4><?= $item['name'] ?></h4>
                                            <p><?= empty($item['description'])?' ':$item['description'] ?></p>
                                        </div>
										<div><?= $item['price'] ?></div>
                                    </div>
									<hr>
									<?php endforeach; ?>
							</div>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				
				
			</div>
			<div class="uk-container uk-text-right">
				<a class="uk-button uk-button-default" href="#order-now" uk-scroll>Torna su <span uk-icon="chevron-up"></span></a>
			</div>
		</section>
		<!-- FOOTER -->
		<footer class="uk-section uk-section-secondary uk-padding-remove-bottom">
			<div class="uk-container">
				<div class="">
					<div class="">
						<h4><?= $client['name'] ?></h4>
						<p><span class="" data-uk-icon="icon:location; ratio: 0.8"></span> Indirizzo: <?= $client['address'] ?></p>
						<p><span class="" data-uk-icon="icon:receiver; ratio: 0.8"></span> Telefono: <a href="tel:<?=$client['phone']?>"><?= $client['phone'] ?></a></p>
						<p><span class="" data-uk-icon="icon:mail; ratio: 0.8"></span> Email: <a href="mailto:<?= $client['email'] ?>"><?= $client['email'] ?></a></p>
						<p><span class="" data-uk-icon="icon:calendar; ratio: 0.8"></span> Apertura settimanale: <?= $client['weekly_opening'] ?></p>
						<p><span class="" data-uk-icon="icon:clock; ratio: 0.8"></span> Orario: <?= $client['opening_hours'] ?></p>
						<div>
							<a href="tel:<?=$client['phone']?>" class="uk-icon-button" data-uk-icon="receiver"></a>
							<a href="https://wa.me/<?=$client['whatsapp']?>" class="uk-icon-button" data-uk-icon="whatsapp"></a>
						</div>
					</div>
					
				</div>
			</div>
			
			<div class="uk-text-center uk-padding uk-padding-remove-horizontal">
				<span class="uk-text-small uk-text-muted">© <?= date('Y') ?> Creato da <a href="https://www.studioaf.eu/">Studio A.F.</a></span>
			</div>
		</footer>
		<!-- /FOOTER -->
</body>
</html>