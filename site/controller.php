<?php
/**
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

define('PATH_CRAMPON', JPATH_ROOT."/crampon");

/**
 * 
 */
class CramponController extends JControllerLegacy
{

  public function display($cachable = false, $urlparams = false) {
// forcer la vue par défaut si aucun paramètre renseignés dans l'url

     $jinput = JFactory::getApplication()->input;
     $view = $jinput->get('view');

    if ( $view == "") {
      $jinput->set('view', 'visupdf');
    }

    parent::display();

  }

  public function save() {
// sauvegarde du sommaire (via ajax)

    $jinput = JFactory::getApplication()->input;
    $l1 = $jinput->post->get('liste', '', 'RAW');
    $liste = json_decode($jinput->post->get('liste', '', 'RAW'));        
    $model = $this->getModel();  
    $nb = $model->sauveArticles($liste);
    if ($nb>0) {
      echo $nb . " articles inserés";
    } else {
      echo "Aucun articles inserés";
    }    
  }

  public function change_date() {
// sauvegarde du sommaire (via ajax)
    $jinput = JFactory::getApplication()->input;
    $no = $jinput->get('no');
    $mois = $jinput->get('mois');
    $an = $jinput->get('an');
    $model = $this->getModel();
    echo $model->changeDate($no, $mois, $an);
       
  }

  public function couverture() {

    // sauvegarde du sommaire (via ajax)
    $jinput = JFactory::getApplication()->input;
    $couv = json_decode($jinput->post->get('couv', '', 'RAW'));    
    $model = $this->getModel();  
    $msg =  $model->sauveCouverture($couv);
    echo 'Sauvegarde : '.$msg;
           
  }

  public function decoupe() {
    $jinput = JFactory::getApplication()->input;
    $no = $jinput->post->get('no', 0, "INT");   
    $model = $this->getModel();  
    $model->decoupePDF($no);

    echo implode(";", $model->msg);


  }


}
