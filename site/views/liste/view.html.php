<?php 
jimport( 'joomla.application.component.view');
jimport('joomla.filesystem.folder');

class CramponViewListe extends JViewLegacy
{
	function display($tpl = null)
	{     
		$jinput = JFactory::getApplication()->input;
		$this->an = $jinput->get("an", 0, "INT");
		$this->search = $jinput->get("search");
		
		$this->crampons = $this->get("Data");  
		$this->pagination = $this->get('Pagination');          

		$this->admin = CramponHelper::checkUser(false);

		$this->abonne = CramponHelper::checkAbonnement(); 


		//echo '<pre>'; var_dump($this->abonne); echo '</pre>'; exit;

		//$this->mod = JModelLegacy::getInstance('crampon', 'cramponModel');
		

	  parent::display($tpl);    
	}
}
?>
