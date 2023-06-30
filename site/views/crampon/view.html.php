<?php 
jimport( 'joomla.application.component.view');
jimport('joomla.filesystem.folder');

class CramponViewCrampon extends JViewLegacy
{
	function display($tpl = null)
	{     
    CramponHelper::checkUser();

    $model = $this->getModel();

    $this->item = $this->get( 'Item' );    

    $this->articles = $model->getArticles();

    $this->msg = $model->msg;

//    echo '<pre>'; print_r($this); echo '</pre>'; exit(0);
    
	  parent::display($tpl);
    
	}
}
?>
