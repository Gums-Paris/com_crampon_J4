<?php 
jimport( 'joomla.application.component.view');

class CramponViewVisupdf extends JViewLegacy
{
	function display($tpl = null)
	{     

    $f = $this->get( 'Item' );
//echo ('item entrée = <pre>');print_r($f);echo'</pre>';exit(0);
    $this->abonne = CramponHelper::checkAbonnement(); 

    
    if ($f!==false) {

      if(JFactory::getUser()->guest and $f->reserve) {

        $this->message =  'Lecture reservée au membres connectés';

      } elseif($f->non_publie and ! $this->abonne) {

        $this->message =  'Lecture reservée aux abonnés<br><br><a href="index.php/revue-le-crampon/abonnement">Cliquer ici pour s\'abonner</a>';

      } else {

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="crampon_'.$f->alias.'.pdf"');
        readfile($f->file);
        exit;   

      }   
    
    } else {
    
      $this->message =  'Fichier pdf non trouvé';
    
    }    
    
    parent::display($tpl);
   
    
	}
}
?>
