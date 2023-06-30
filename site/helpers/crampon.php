<?php
/**
 * @package     Joomla.Site
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
setlocale(LC_TIME, "fr_FR.UTF8");
use \Joomla\CMS\Factory;

abstract class CramponHelper
{

	public static function checkUser($redirect = true)
	{
  	$user	= Factory::getUser();
    $aid = $user->groups;
    $groupes_autorises = array(8, 14);
    $check = ( count(array_intersect($aid, $groupes_autorises)) > 0);
    if($check === false and $redirect) {     
      $uri = Factory::getURI(); 
      $return = $uri->toString(); 
      $app = Factory::getApplication();
  		$app->redirect('index.php', 
        JText::_('Reservé aux administrateurs') ); 
    }          
    
    return $check;
    
	}

  
	public static function checkAbonnement()
	{

    $user	= Factory::getUser();

    $db		= Factory::getDbo();
    $query	= $db->getQuery(true);
    $query->select('cb_crampon')
      ->from($db->quoteName('#__comprofiler'))
      ->where($db->quoteName('user_id') . ' = ' . $user->id);
    $db->setQuery($query);
    $abonne = (float) $db->loadResult();     
 
    if ($abonne > 0) {
      return true;
    } else {
      return false;
    }
    
	}


  public static function selectMois($date = null) {
    if ($date === null) {
      $m = date("m");
    } else {
      $m = date("m", strtotime($date));
    }    
  
    $select = array();
    $i = 1;
    for($i=1; $i<13; $i++) {           
      if ($i==$m) {
        $selected = " selected";
      } else {
        $selected = "";      
      }
      $nom_mois = ucfirst(strftime('%B', strtotime("2000-".$i."-01")));
      $select[] = '<option value="'.$i.'"'.$selected.'>'.$nom_mois.'</option>';
    }
    
    return implode("\n", $select);
  
  }

  public static function selectAn($date = null, $tout = false) {
    if ($date === null) {
      $a = date("Y");
    } else {
      $a = substr($date, 0, 4);
      //$a = date("Y", strtotime($date));
    }      

    $select = array();

    if ($tout) {
      if ($a==0) {
        $selected = " selected";
      } else {
        $selected = "";      
      }
      $select[] = '<option value="0" '.$selected.'>Année...</option>';
    }

    for($i=date("Y"); $i>date("Y")-20;$i--) {     
      if ($i==$a) {
        $selected = " selected";
      } else {
        $selected = "";      
      }
      $select[] = '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';

    }
    
    return implode("\n", $select);
  
  }

  public static function formatDate($date) {
    
    $nom_mois = ucfirst(strftime('%B', strtotime($date)));
    $an = date("Y", strtotime($date));
    return $nom_mois." ".$an;
  
  }

  
}
