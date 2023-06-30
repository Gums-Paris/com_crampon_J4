<?php
defined('_JEXEC') or die;
use \Joomla\CMS\Factory;

class CramponModelVisupdf extends JModelItem
{

  public function getItem($id = null) {
  
     $db		= $this->getDbo();

    // Format normal de l'id = n°crampon_n°item

     $jinput = Factory::getApplication()->input;
     $no2 = (int) $jinput->get('no');          
     $id = $jinput->get('id');     
     $x = explode("_", $id);         
     $no = (int) $x[0];
     $item = (int) $x[1];

     if ($no > 0 and $item > 0) {       

        // On cherche l'article dans la table crampon_articles

		    $query	= $db->getQuery(true);
   			$query->select(array('no_page', 'nb_pages', '#__crampon_articles.fichier', 'alias', 'reserve', 'if(date > subdate(now(), INTERVAL 4 MONTH), true, false) as non_publie'))
        ->from($db->quoteName('#__crampon_articles'))
        ->join('LEFT', $db->quoteName('#__crampon', 'c') . ' ON (' . $db->quoteName('no') . ' = ' . $db->quoteName('c.id') . ')')              
        ->where( array ($db->quoteName('item') . " = " . (int) $item, 
                        $db->quoteName('no') . " = " . (int) $no));
         $db->setQuery($query);
            
        $f = $db->loadObject();



        // Si le champ "fichier" est renseigné => c'est un vieux numéro qu'on n'a pas su convertir
        // donc le nom du fichier pdf est resté celui d'origine
        //
        // Sinon le nom du fichier pdf est n°crampon_n°page_nbre-pages . pdf
        //

        if ($f->fichier<>'') {
          $file = PATH_CRAMPON . "/" . $no . "/" . $f->fichier;  
        } else {
          $file = PATH_CRAMPON . "/" . $no . "/" . $no . "_" . $f->no_page . "_" . $f->nb_pages . ".pdf";
        }        

        // On met à jour le nombre de vues de l'article
        //

        if (is_file($file)) {
          $query	= $db->getQuery(true);
          $query->update($db->quoteName('#__crampon_articles'))
            ->set($db->quoteName('vues') . ' = '  . $db->quoteName('vues') . '+1')
            ->where( array ($db->quoteName('item') . " = " . (int) $item, 
                            $db->quoteName('no') . " = " . (int) $no));
          $db->setQuery($query);
          $db->execute(); 
          $f->file = $file;

          return $f;

        } else {
          return false;
        }                                        
   
      } elseif($no2>0) {

		    $query	= $db->getQuery(true);
         $query->select(array('if(date > subdate(now(), INTERVAL 4 MONTH), true, false) as non_publie'))         
        ->from($db->quoteName('#__crampon'))
        ->where( $db->quoteName('id') . " = " . (int) $no2 );
         $db->setQuery($query);

        $f = $db->loadObject();
        $f->alias = "numero_".$no2;
        $f->reserve = true;


        $file = PATH_CRAMPON . "/" . $no2 . "/" . $no2 . ".pdf";  
        if (is_file($file)) {
          $f->file = $file;
          return $f;
        } else {
          return false;
        }

      }
            
      return false;

  }



  
}
