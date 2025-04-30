<?php

/*
   Plugin Name: Makelaar Plugin
   Plugin URI: http://www.tussendoor.nl/wordpress-plugins
   Author: Tussendoor B.V.
   Author URI: http://www.tussendoor.nl/
   Description: Integratie om WordPress te koppelen met makelaarspakket geschikt voor Realworks.
   Tested up to: 5.6
   Version: 3.5.5-beta.4
*/

require __DIR__.'/vendor/autoload.php';

define('REALWORKS', __FILE__);

$realworks = new Realworks\Realworks;
$realworks->start();

add_action('init', array($realworks, 'boot'));
