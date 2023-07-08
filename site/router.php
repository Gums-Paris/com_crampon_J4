<?php
/**
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;

class CramponRouter extends JComponentRouterBase
{
   
	public function build(&$query)
	{      
    
    $segments = array();

    $items = array ('view', 'id', 'component');
 
      foreach($items as $item) {

        if (isset($query[$item])) {

          if ($item == 'id' ) {
            $segments[] = $query[$item];
          }
  
          //$segments[] = $query[$item];
          unset($query[$item]);
        }
  

  	}

    return $segments;

	}


	public function parse(&$segments)
	{                   
    
    $user	= Factory::getUser();
    if ($user->id==62) {
      $app  = Factory::getApplication();
      $menu = $app->getMenu()->getActive()->query;
      
      //echo '<pre>'; print_r($this); echo '</pre>'; exit;
    } 
   
    
  	$vars = array();
//echo ('segments entrée parse = <pre>');print_r($segments);echo'</pre>';exit(0);
    if(isset($segments[0])) {

      // Appel en modif d'un crampon
      //
      $app  = Factory::getApplication();
      $qry = $app->getMenu()->getActive()->query; 
      if(isset($qry["view"]) and $qry["view"]=="crampon") {
        $vars["no"] = (int) $segments[0];
        $vars["Itemid"] = $app->getMenu()->getActive()->id; 
        $vars["view"] = "crampon";         
        $vars["layout"] = "edit"; 
        return $vars;
      }

      // Pour traiter les anciens liens vers un numéro du crampon
      //
      if (strpos($segments[0], ":")===false) {
        preg_match("`n-([0-9]{3})-`", $segments[0], $m);
        if ((int) $m[1]>0) {
          $vars["no"] = (int) $m[1];
          $vars["Itemid"] = 85; 
          $vars["view"] = "liste"; 
         // echo '<pre>'; print_r($this);echo '</pre>';exit;
          return $vars;
        }
        
      }      
      
      
  /*    $s = explode(":", $segments[0]);
      $vars['id'] = $s[0];
      $vars['alias'] = $s[1];  */
      $vars['id'] = $segments[0];
    }
//         echo 'vars = <pre>'; print_r($vars);echo '</pre>';exit;

	  return $vars;
	}

}

?>
