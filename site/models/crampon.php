<?php
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
use \Joomla\CMS\Factory;

class CramponModelCrampon extends JModelAdmin
{
  
  public $msg = array();  // Messages 
  private $fdpi = false;  // Est-ce que la classe Fpdi est chargée

  // Récupération des infos d'un numéro $id

  public function getItem($id = null) { 
    $db		= $this->getDbo();
//echo('après db<br>');
    
    $jinput = Factory::getApplication()->input;

    // Est-ce que id (=no du crampon) est renseigné dans le formulaire ?   
    $id = $jinput->post->get('no', 0, "INT");

    if ($id == 0) {
      $id = $jinput->get('no', 0, "INT");
    }
    $this->id = $id;


    // Id non renseigné => on en reste là
    if ($id == 0) {             
      return $this;
    }

    // Si id renseigné on regarde si présent dans la base
    //
    $this->row = $this->getEntete($id);
    // N° non trouvé dans la table, on crée un nouvel enregistrement
    if ( $this->row->id <> $id ) {
      $this->row = $this->newItem($id);
    }

    // On regarde si un fichier a été chargé via le formulaire
    $this->row->uploaded = false;       
    $fichier = $jinput->files->get('fichier');      
    if ($fichier["size"]>0) {
      // un fichier a été chargé dans le formulaire, on le place dans 
      // le répertoire crampon et on le vérifie      
      $this->row->uploaded = $this->upload($fichier, $id);
    } else {
      // Pas de fichier dans le formulaire, on regarde si le pdf source est présent
      //
      $file = PATH_CRAMPON."/". $id. "/" . $id . ".pdf";
      if (JFile::exists($file)) {
        $this->row->uploaded = true;    
      } 

    }
  
    return $this->row;                                       
      
  }

  // Recherche d'un numéro de crampon dans la table crampons
  //
  private function getEntete($id) {

    $db		= $this->getDbo();
    $query	= $db->getQuery(true);
    $query->select( array('id', 'date', 'date_en_ligne', 'couverture',
                            'titre_couverture', 'auteur_couverture'))
    ->from($db->quoteName('#__crampon'))
    ->where($db->quoteName('id') . " = " . (int) $id);
    $db->setQuery($query);
    $row = $db->loadObject();

    return $row;

  }


  // Création d'un nouveau numéro dans la base
  //
  private function newItem($id) {
  
    $db		= $this->getDbo();
    $query	= $db->getQuery(true);
    $query
    ->insert($db->quoteName('#__crampon'))
    ->columns($db->quoteName(array('id')))
    ->values(array($id));      
    $db->setQuery($query);
    $db->execute();

    $this->msg[] = (int) $db->getAffectedRows() . " enregistrement créé dans base crampon";

    // On relance la recherche pour avoir un objet complet (avec les champs vides)
    $row = $this->getEntete($id);
    return $row;
  
  }

  // Traitement du fichier téléchargé
  //
  private function upload($fichier, $id) {

    $src  = $fichier['tmp_name'];          // Fichier téléchargé
    $pdf = (string) $id . '.pdf';   // Fichier de destination
    
    // Répertoire du n° en cours - si il n'existe pas on le crée
    $path = PATH_CRAMPON."/". $id;         
    if (!JFolder::exists($path)) {
      JFolder::create($path);
      $this->msg[] = "Répertoire ".$path." créé";
    }

    $dest = $path . "/" . $pdf;
        
    // Backup de l'éventuel fichier existant
    if (JFile::exists($dest)) {
      JFile::copy($dest, $dest. "." . time() .".bak");
    } 
    $upload = JFile::upload($src, $dest);
    if ($upload === false) {
      $this->msg[] = "Erreur téléchargement ".$src;
      return false;
    } else {
      $this->msg[] = "Fichier ".$dest." téléchargé";
      $check = $this->check_pdf($dest);
      if ($check === false) {
        unlink($dest);
      }
      return $check;
    }            


  }

  // Charge la classe Fdpi (pour manipuler les pdf)
  //
  private function loadFdpi() {
    if ($this->fdpi === false) {
      $this->root = str_replace(strrchr($_SERVER["DOCUMENT_ROOT"],"/"), "/", $_SERVER["DOCUMENT_ROOT"]);
      require( $this->root . 'vendor/autoload.php');
      $this->fdpi = true;
    }
  }


  // Vérifie que le pdf chargé peut être manipulé par Fdpi
  //
  private function check_pdf($file) {
    $this->loadFdpi();
    $pdf = new  \setasign\Fpdi\Fpdi(); 
    try {
      $pdf->setSourceFile($file);
      return true;
    } catch (\Throwable $th) {
      $this->msg[] = "Fichier pdf ".$file." incompatible avec le découpage";
      return false;
    }

  }


  // Récupère la liste des articles du crampon $id 
  // dans la table crampon_articles
  //
  public function getArticles($id = null) {
    $db		= $this->getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
    ->from($db->quoteName('#__crampon_articles'))
    ->where($db->quoteName('no') . " = " . (int) $this->id);  
    $db->setQuery($query);  
    return $db->loadAssocList("item");  
  }


  // Mise à jour de la date du crampon
  //
  function changeDate($no, $mois, $an) {

    $db		= $this->getDbo();
    $query = $db->getQuery(true);
    $query->update($db->quoteName('#__crampon'))
    ->set( $db->quoteName('date') . ' = ' . $db->quote($an."-".$mois."-01"))
    ->where($db->quoteName('id') . ' = ' . $db->quote($no));
    $db->setQuery($query); 
    $db->execute();
    return $db->getAffectedRows();

  }

  // Sauvegarde des données de la couverture
  //
  function sauveCouverture($couv) {
    $db		= $this->getDbo();
    $query = $db->getQuery(true);
    $query->update($db->quoteName('#__crampon'))
    ->set( array ($db->quoteName('couverture') . ' = ' . (int) $couv->check, 
                  $db->quoteName('titre_couverture') . ' = ' . $db->quote($couv->titre), 
                  $db->quoteName('auteur_couverture') . ' = ' . $db->quote($couv->auteur) ) )
    ->where($db->quoteName('id') . ' = ' . (int) $couv->no);
    $db->setQuery($query); 
    $db->execute();

    $msg = $db->getAffectedRows() . ' enregistrements modifiés';

    // Création des vignettes couvertures
    if ($couv->check == 1) {
      $msg .= "\n" . $this->vignette_couverture((int) $couv->no);
    }
    $this->msg[] =  $msg;
    
  }


  //----------------------------------------
  // Création des vignettes de couverture
  // à partir de la première page du pdf
  //
  function vignette_couverture($no) {

    $x_moyen = 1200;
    $y_moyen = 1600;
    $x_petit = 120;
    $y_petit = 160;

    $path = PATH_CRAMPON."/". (string) $no;  
    $path_images = $_SERVER["DOCUMENT_ROOT"]. "/images/crampon";
    
    // Image de base - si pas présente, la vignette est à créer
    $image = $path ."/". $no . ".jpg";
    //if (! is_file($image)) {

      // Extraction de la première page du pdf global
      //
      $this->loadFdpi();
      $pdf = new  \setasign\Fpdi\Fpdi();      
      $file = $path ."/". (string) $no . ".pdf";
      if (! is_file($file)) {
        return "Erreur création vignette _ fichier ".$no.".pdf absent";
      }     
      try {
        $pdf->setSourceFile($file);
      } catch (\Throwable $th) {
        return "Erreur source file ".$file."\n".var_export($th);
      }
      
            
      $pdf->AddPage();
      $pdf->useTemplate($pdf->importPage(1));
      $pdf_couverture = substr($file, 0, -4)."_couverture.pdf";
      $pdf->Output($pdf_couverture, "F");
      if (! is_file($pdf_couverture)) {
        return "Erreur création pdf de couverture ".$no.".pdf";
      }     

      // Conversion du pdf de couverture en jpg
      //
      $imagick = new Imagick();
      $imagick->readImage($pdf_couverture);
      $resolution = $imagick->getImageResolution();
      $size = $imagick->getSize();
      $x = min(300, $resolution["x"]);
      $y = min(300, $resolution["y"]);
      $imagick->setResolution($x,$y);
  
      $nf = $path . "/" . $no . ".jpg";
      $imagick->writeImage($nf);
      if (! is_file($nf)) {
        return "Erreur création jpg depuis ".$no."_couverture.pdf";
      }   

      // Création de la vignette moyenne
      //
      $x = min(150, $resolution["x"]);
      $y = min(150, $resolution["y"]);
      $imagick->resampleImage($x, $y, Imagick::FILTER_LANCZOS, 1);
      $imagick->thumbnailImage($x_moyen, $y_moyen, true, true);
      
      $moyen = $path . "/" . $no . "_moyen.jpg";
      $moyen2 = $path_images ."/" . $no . "_moyen.jpg";
      $imagick->writeImage($moyen);
      copy($moyen, $moyen2);
  
      $x = min(72, $resolution["x"]);
      $y = min(72, $resolution["y"]);
      $imagick->resampleImage($x, $y, Imagick::FILTER_LANCZOS, 1);
      $imagick->thumbnailImage($x_petit, $y_petit, true, true);
      
      $petit = $path . "/" . $no . "_petit.jpg";
      $petit2 = $path_images ."/" . $no . "_petit.jpg";      
      $imagick->writeImage($petit);
      copy($petit, $petit2);

      return "Creation vignette OK";

    //}

  }


  function sauveArticles($liste) {
    $db		= $this->getDbo();
    $no = (int) $liste[0]->no;
  
    if ($no>0) {
      $query = $db->getQuery(true);
      $query->delete($db->quoteName('#__crampon_articles'))
      ->where($db->quoteName('no') . " = ". $no);
      $db->setQuery($query);
      $db->execute();


      $i = 0;
      $vals = array();
      foreach($liste as $item) {
        $i++;
        $alias= $no . "_" . $i .":". JFilterOutput::stringURLSafe($item->titre);
        $values = array(
          $db->quote($item->no),          
          $db->quote($i),
          $db->quote($item->titre),          
          $db->quote($alias),          
          $db->quote($item->auteur),
          $db->quote($item->no_page),
          $db->quote($item->nb_pages),          
          $db->quote($item->fichier),
          $db->quote($item->reserve)
        );

        $vals[] = "(" . implode(",", $values) .")";        

      }
      
      $query = "insert into `#__crampon_articles` 
        (`no`, `item`, `titre`, `alias`, `auteur`,`no_page`, `nb_pages`, `fichier`, `reserve`) 
        values " . implode(",", $vals) ;
      
      $db->setQuery($query);
      $db->execute();

      return $db->getAffectedRows();

    }    

  }

  public function decoupePdf($no) {

    $this->loadFdpi();
    $pdf = new  \setasign\Fpdi\Fpdi();

    $path = PATH_CRAMPON."/". (string) $no;  
    $file = $path ."/". (string) $no . ".pdf";
    if (! is_file($file)) {
      echo $file . "<br>Erreur"; exit;
    }

    // Nettoyage
    $files = glob($path."/" . (string) $no . "_*.pdf");
    if (count($files)>0) {
      foreach($files as $f) {
        unlink($f);
      }
      //$this->msg[] = count($files) . " fichiers pdf existants supprimés";
    }
    
    
    $pagecount = $pdf->setSourceFile($file);
    
    $db		= $this->getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
    ->from($db->quoteName('#__crampon_articles'))
    ->join("LEFT", $db->quoteName('#__crampon') ." ON `id` = `no`")
    ->where($db->quoteName('no') . " = " . $no);  
    $db->setQuery($query);  
    $articles = $db->loadObjectList();  

    if ($articles[0]->couverture == 1) {
      $decalage = 0;
    } else {
      $decalage = 1;
    }

    foreach($articles as $k => $a) {

      $new_pdf = new \setasign\Fpdi\FPDI();
      $new_pdf->setSourceFile($file);

      // Metatdata
      $new_pdf->SetCreator("GUMS Paris");
      if ($a->auteur<>'') {
        $at = $a->auteur . " - GUMS Paris";
      } else {
        $at = "GUMS Paris";
      }        
      $new_pdf->SetAuthor(iconv('utf-8', 'cp1252', $at));        
      $new_pdf->SetTitle(iconv('utf-8', 'cp1252', $a->titre));
      $new_pdf->SetSubject(iconv('utf-8', 'cp1252', "Crampon n° ".$no." - " .CramponHelper::formatDate($a->date)));

      for ($i=0; $i<$a->nb_pages; $i++) {
        $new_pdf->AddPage();
        $page_a_importer = $a->no_page - $decalage + $i;
        $new_pdf->useTemplate($new_pdf->importPage($page_a_importer));
      }
      $new_filename = $path."/". (string) $no . "_". $a->no_page . "_". $a->nb_pages .".pdf";
      $new_pdf->Output($new_filename, "F");
      if (is_file($new_filename)) {
        $check = "true";
      } else {
        $check = "false";
      }
      $this->msg[] .= ($k+1) . ":" . $check;
      unset($new_pdf);

    } 
    
  }

  public function recupDocman() {

    ob_start();

    $root = str_replace("www", "", $_SERVER["DOCUMENT_ROOT"]);
    require $root . 'vendor/autoload.php';

    $path_docman = "/home/gumspari/www/joomlatools-files/docman-files";    
    $db		= $this->getDbo();
    $query = $db->getQuery(true);

    $mois = explode(",", "janvier,fevrier,mars,avril,mai,juin,juillet,aout,septembre,octobre,novembre,decembre" );    
    $files_pdf = array();

    $query = "SELECT b.docman_category_id, b.slug, b.description
      FROM `#__docman_categories` as b 
      left join `#__docman_category_relations` on descendant_id=b.docman_category_id 
      where ancestor_id=46 and level=1 order by b.slug
    ";
    $db->setQuery($query);  
    $numeros = $db->loadObjectList(); 
    //echo '<pre>'; print_r($articles); echo '</pre>'; exit;

    echo '<table border="1">';

    foreach($numeros as $n) {

      preg_match("`n-([0-9]*)-([^-]*)-([0-9]{4})`", $n->slug, $matches1);
      $no = (int) $matches1[1];
      if ($no==0) {
        echo 'Pb no<br><pre>'; print_r($n); echo '</pre>'; exit;
      }
      if($no>387) {
        break;
      }
      
      
      $pdf_total_file = PATH_CRAMPON . "/" . $no . "/" . $no . ".pdf";
      if (! is_file($pdf_total_file)) {
        $pdf_total_a_creer = true;
        //$pdf =  new  \setasign\Fpdi\Fpdi();
      } else {
        $pdf_total_a_creer = false;
      }

      $compteur_pages = 2;
      $ms = array_search($matches1[2], $mois) + 1;
      $date = $matches1[3]."-".str_pad($ms, 2, 0, STR_PAD_LEFT)."-01";

      /*

      $query = "INSERT IGNORE INTO j3x_crampon (`id`,`date`,`date_en_ligne`) 
      values(".$no.", '".$date."', '".$date."')";
      $db->setQuery($query);  
      $db->execute();
      */

      $fs = glob(PATH_CRAMPON . "/" . $no . "/*.pdf");
      foreach ($fs as $fts) {
        unlink($fts);
      }
      
      $t = $n->description;

      $image = PATH_CRAMPON . "/" . $no . "/" . $no ."_petit.jpg";
      if(!is_file($image)) {
        preg_match('`src="([^"]*)"`', $t, $matches);
        if(is_file($root."www/".$matches[1])) {
          copy($root."www/".$matches[1], $image);
        }
      }

      preg_match_all("`<li>(.*?)<\/li>`m", $t, $matches);      

      $item_compteur = 0;

      foreach($matches[1] as $item) {
        //echo '<br>'.htmlentities($item);
        $item_compteur++;
        
        preg_match('`<a.*gid=(.*)"[^>]*>(.*)<\/a>(.*)`', $item, $matches2);
        
        if (count($matches2) == 0) {
          preg_match('`<a.*alias=([0-9]{3})[^>]*>(.*)<\/a>(.*)`', $item, $matches2);
        } 

        $no_doc = (int) $matches2[1];
        
        if ($no_doc==0) {
          echo 'xx<br>'.htmlentities($item);
          echo '<pre>'; var_dump($matches2); echo '</pre>'; exit;
        }



        $titre = strip_tags(trim($matches2[2]));

        $auteur = trim($matches2[3]);
        $auteur = strip_tags(str_replace("- ", "", $auteur));        

        $query = "SELECT title, storage_path, hits 
        FROM `#__docman_documents` where docman_document_id = ".$no_doc;
        $db->setQuery($query);  
        $article = $db->loadObject(); 
        if ($article === NULL) {
          $document = 'Non trouvé';
          $vues = '';
          $titre2 = 'Non trouvé';
        } else {
        
          $document = $article->storage_path;
          preg_match("`n[^0-9]*([0-9]{3})`", $document, $matches3);

          if ($matches3[1] <> $no ) {
            echo 'no=' . $no . 'no_doc=' . $no_doc . '<br><pre>'; 
            echo htmlentities($item) ;          
            print_r($article); 
            echo '</pre>'; exit;
          }


          $vues = (int) $article->hits;
          $titre2 = strip_tags($article->title);
        }

        if (strtolower($titre)<>"couverture") {
        
          $files_pdf[] = $path_docman . "/" . $document;
          $no_page = $compteur_pages;

          $pdf =  new  \setasign\Fpdi\Fpdi();     
          try {
            $nb_pages = $pdf->setSourceFile( $path_docman . "/" . $document);
                      
            $compteur_pages += $nb_pages;
            
            echo '<tr><td>'.$no .'</td><td>'.$titre.'</td><td>'.$titre2 .'</td><td>'. $auteur.'</td><td>'.$document
            .'</td><td>'.$vues.'</td><td>'.$no_page.'</td><td>'.$nb_pages.'</td></tr>';        

            

            
            // Creation new PDF
            //
            $new_file = PATH_CRAMPON . "/" . $no . "/" . $no . "_". $no_page . "_". $nb_pages . ".pdf";
            if (!is_file($new_file))  { 


              $pdf->SetCreator("GUMS Paris");
              if ($auteur<>'') {
                $at = $auteur . " - GUMS Paris";
              } else {
                $at = "GUMS Paris";
              }        
              $pdf->SetAuthor(iconv('utf-8', 'cp1252', $at));        
              $pdf->SetTitle(iconv('utf-8', 'cp1252', $titre));
              $pdf->SetSubject(iconv('utf-8', 'cp1252', "Crampon n° ".$no." - " .CramponHelper::formatDate($date)));
                      
              for ($pageNo = 1; $pageNo <= $nb_pages; $pageNo++) {
                  $templateId = $pdf->importPage($pageNo);
                  $pdf->AddPage();
                  $pdf->useTemplate($templateId);                  
              }
              
              $pdf->Output($new_file, "F");
            }
            $file_bdd = "";

          } catch (\Throwable $th) {

            echo '<br>Erreur Document incompatible '.$document; 
            $new_file = PATH_CRAMPON . "/" . $no . "/" . $document;
            copy($path_docman . "/" . $document, $new_file);
            $incompatible = true;
            $pdf_total_a_creer = false;

            $file_bdd = $document;

          }  

          unset($pdf);

          // insertion base
          $values = array (
            $no,
            $item_compteur, 
            $db->quote($titre),
            $db->quote($no . "_" . $item_compteur .":".JFilterOutput::stringURLSafe($titre)),
            $db->quote($auteur),
            $no_page,
            $nb_pages,
            $db->quote($file_bdd),
            $vues
          );


          $query = "REPLACE INTO j3x_crampon_articles VALUES (" . implode(",", $values) . ")";
          $db->setQuery($query);  
          $db->execute();
        
        } else {

          $new_file = PATH_CRAMPON . "/couvertures/couverture_" . $no . ".pdf";
          if (! is_file($new_file)) {
            copy($path_docman . "/" . $document, $new_file);
          }
          

        }


        /*
        $path = PATH_CRAMPON."/". (string) $no;  
        $file = $path ."/". (string) $no . ".pdf";
        if (! is_file($file)) {
          echo $file . "<br>Erreur"; exit;
        }
        */
        
      }

      if ($pdf_total_a_creer) {

        $pdf =  new  \setasign\Fpdi\Fpdi();

        $pdf->SetCreator("GUMS Paris");
        $pdf->SetAuthor("GUMS Paris");
        $pdf->SetTitle(iconv('utf-8', 'cp1252', "Crampon n° ".$no." - " .CramponHelper::formatDate($date)));

        // iterate through the files
        foreach ($files_pdf AS $file) {
            // get the page count
          try {
            //code...
            $pageCount = $pdf->setSourceFile($file);

            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);      
                
            }
          } catch (\Throwable $th) {
            $rien = 1;
          }  

        }
              
        $pdf->Output($pdf_total_file, "F");
      }

      //exit;
      
    }
    echo '</table>';
    exit;



  }

  function test() {
    $root = str_replace("www", "", $_SERVER["DOCUMENT_ROOT"]);
    require $root . 'vendor/autoload.php';
    $path_docman = "/home/gumspari/www/joomlatools-files/docman-files";    

    $document = "n353_02_com_coopt_ski.pdf";

    $pdf =  new  \setasign\Fpdi\Fpdi();        

    try {
      $nb_pages = $pdf->setSourceFile( $path_docman . "/" . $document);
    } catch (\Throwable $th) {
      echo 'Erreur'; exit;
    }

    
/*
    for ($pageNo = 1; $pageNo <= $nb_pages; $pageNo++) {
      $templateId = $pdf->importPage($pageNo);
      $pdf->AddPage();
      $pdf->useTemplate($templateId);                  
    }

    $pdf->Output();
*/
    echo 'nb page = '.$nb_pages;
    echo '<pre>'; print_r($pdf); echo '</pre>'; exit;


  }

	protected function loadFormData(){
  	$data = $this->getItem();            
		return $data;
	}

  
	public function getForm($data = array(), $loadData = true){  
   return true;  
  }


  
}
