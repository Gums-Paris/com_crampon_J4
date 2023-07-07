<?php

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;

if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}

//require_once JPATH_COMPONENT.'/helpers/inscriptions.php';
JLoader::register('CramponHelper', JPATH_COMPONENT . '/helpers/crampon.php');

$controller = JControllerLegacy::getInstance('Crampon');

$input = Factory::getApplication()->input;

// Require specific controller if requested
if($controller = $input->getWord('controller', '')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}
// Create the controller
$classname	= 'CramponController'.$controller;
$controller = new $classname();

$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
?> 
