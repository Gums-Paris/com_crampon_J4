<?php
defined('_JEXEC') or die;

class CramponController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = array())
	{

		$view   = $this->input->get('view', 'crampon');
		$id     = $this->input->getInt('id');

		return parent::display();
	}
}
