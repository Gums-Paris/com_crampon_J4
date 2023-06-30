<?php
defined('_JEXEC') or die;
use \Joomla\CMS\Factory;

class CramponModelListe extends JModelList
{

  /**
   * Items total
   * @var integer
   */
  var $_total = null;

  /**
   * Pagination object
   * @var object
   */
  var $_pagination = null;

  function __construct() {
    parent::__construct();

    $mainframe = Factory::getApplication();
    $input = $mainframe->input;

    // Get pagination request variables
    //$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
    $limit = 10;
    $limitstart = $input->getInt('limitstart', 0, 'int');

    // In case limit has been changed, adjust it
    $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);

  }



  function getData() {

    $jinput = Factory::getApplication()->input;
       
    $db		= $this->getDbo();

    $query	= $db->getQuery(true);
    $query->select(array('*', 'if(date > subdate(now(), INTERVAL 4 MONTH), true, false) as non_publie'))
      ->from($db->quoteName('#__crampon_articles'))
      ->join('LEFT', $db->quoteName('#__crampon', 'c') . ' ON (' . $db->quoteName('no') . ' = ' . $db->quoteName('c.id') . ')')      
      ->order(array('date DESC', 'no DESC', 'item ASC'));

    // Affichage d'un numéro seulement
    $no = $jinput->get('no', 0, "INT");       
    if ($no>0) {
      $query->where($db->quoteName('no') . ' = ' . $no);
    }

    // Affichage d'une année
    $an = $jinput->get('an', 0, "INT");       
    if ($an>0) {
      $query->where('year('.$db->quoteName('date') . ') = ' . $an);
    }

    // Recherche
    $search = $jinput->get('search', '');       
    if ($search <> "") {
      $query->where($db->quoteName('titre') . " like '%" . $search . "%'");
    }


    $db->setQuery($query);
    $rows = $db->loadObjectList();     

    /*
		$user	= Factory::getUser();
    if ($user->id==62) {	
			echo '<pre>'; print_r($rows);echo '</pre>';exit;
    }    
    */

    $crampons = array();
    foreach($rows as $r) {
      if (! isset($crampons[$r->no])) {
       $crampons[$r->no] = array();
      }
    }
    $this->_total = count($crampons);
    
    $crampons = array_slice($crampons, $this->getState('limitstart'), $this->getState('limit'), true);



    foreach($rows as $r) {
      if (isset($crampons[$r->no])) {
        $crampons[$r->no][] = $r;      
      }
    }

    return $crampons;

  }
 
  function getPagination() {
    // Load the content if it doesn't already exist
    if (empty($this->_pagination)) {
      jimport('joomla.html.pagination');
      $this->_pagination = new JPagination($this->_total, $this->getState('limitstart'), $this->getState('limit') );
    }
    return $this->_pagination;
  }
  

}
