<?php
defined('_JEXEC') or die;
use \Joomla\CMS\Factory;

// Include the component HTML helpers.
//JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
//JHtml::_('behavior.formvalidation');
//JHtml::_('behavior.keepalive');
//JHtml::_('formbehavior.chosen', 'select');
//	JHTML::_('behavior.modal');

$script = str_replace( JPATH_ROOT, "", dirname(__FILE__)) . '/edit.js';//.rand();
Factory::getDocument()->addScript($script);  
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="cb_template edit_crampon" enctype="multipart/form-data" >
<input type="hidden" name="option" value="com_crampon">
<input type="hidden" name="view" value="crampon">
<input type="hidden" name="layout" value="edit">
<input type="hidden" name="task" value="">
<?php 

if ($this->item->id == 0) {
?>
  <h4>Mise en ligne Crampon<h4>
    <div class="form-group">
      <label class="control-label" for="no">Numéro</label>
      <div class="controls" style="width: 200px;">
        <input type="text" name="no" id="no" value="" style="width: 100px; height: 25px; font-size: 16px;float: left;">
        <button type="submit" class="btn btn-primary pull-right" name="recherche" style="height: 25px; padding: 0px 12px;">
          Valider</button>           
      </div>
    </div>

<?php        
} else {
  
?>
  <input type="hidden" name="no" id="no" value="<?php echo $this->item->id; ?>">
  <h4>Mise en ligne Crampon n° <?php echo $this->item->id; ?><h4>

  <div class="row">
    <div class="col-xs-3 form-group">
    <label class="control-label" for="mois">Mois</label>
    <select class="form-control" id="mois" name="mois">
    <?php echo CramponHelper::selectMois($this->item->date); ?>
    </select>                   
    </div>
    <div class="col-xs-3 form-group">
    <label class="control-label" for="an">Année</label>
    <select class="form-control"  id="an" name="an">
      <?php echo CramponHelper::selectAn($this->item->date); ?>
    </select>                             
    </div>
    <div class="col-xs-3 form-group" style="padding-top: 20px;">
    <button id="change_date" class="btn btn-primary" style="margin: 15px 10px 0px 10px;">Changer Date</button>
    </div>  
  </div>


  <div class="form-group" id="uploaded" style="<?php if (! $this->item->uploaded) echo 'display: none;'; ?>">
    <label for="fichier_2" class="col-xs-3" style="padding-top: 7px;">Fichier chargé </label>
    <input type="text" id="fichier_2" style="font-size: 14px; height: 28px;" readonly value="<?php echo $this->item->id.".pdf"; ?>">
    <button id="changer" class="btn btn-warning">Changer</button>
  </div>    

  

  <div class="form-group" id="upload" style="<?php if ($this->item->uploaded) echo 'display: none;'; ?>">
    <label for="fichier" class="col-xs-3" style="float: left; padding-top: 7px;">Fichier à charger </label>
    <input type="file" class="form-control-file" id="fichier" name="fichier" style="font-size: 14px; height: 28px; float: left;">
    <button type="submit" class="btn btn-primary" name="recherche">Valider</button>
  </div>    
  <span class="clearfix"></span>

  <div style="<?php if (! $this->item->uploaded) echo 'display: none;'; ?>">

  <div class="form-group data_crampon">
    <label style="">
      Couverture <input type="checkbox" id="check_couverture" style = "margin-top: -5px; margin-right: 5px;" 
      <?php if ($this->item->couverture == 1) { echo " checked"; }?> >
    </label>
    <input type="text" id="titre_couverture" style="width: 250px;" value="<?php echo $this->item->titre_couverture; ?>" placeholder="Titre couv">
    <input type="text" id="auteur_couverture" style="width: 175px;" value="<?php echo $this->item->auteur_couverture; ?>" placeholder="Auteur couv">
    <button class="btn btn-primary" id="couverture">Changer</button>
    <a href="/images/crampon/<?php echo $this->item->id; ?>_moyen.jpg" target="_blank">
    <?php 
      if(is_file($_SERVER["DOCUMENT_ROOT"]."/images/crampon/".$this->item->id. "_petit.jpg")) {
        $src = "/images/crampon/".$this->item->id. "_petit.jpg";
      } else {
        $src = "";
      }
    ?>
      <img id="couvimg" src="<?php echo $src; ?>" style="width: 30px; height: 30px;">
    </a>
  </div>


  <?php if ($this->item->uploaded and count($this->articles)==0) { ?>
  
    <div class="form-group">
    <label for="sommaire">Import sommaire :</label>
    <textarea class="form-control" rows="5" id="sommaire"></textarea>
    </div> 
    <button id="nettoie" class="btn btn-warning">Nettoyer</button>
    <button id="remplit" class="btn btn-warning">Remplit</button>

  <?php } ?>

  <div class="form-group data_crampon" >
  <div style="width: 30px;">i</div>
  <div style="width: 280px;">Titre</div>
  <div style="width: 180px;">Auteur</div>
  <div style="width: 30px;">Début</div>
  <div style="width: 30px;">Nb pg</div>  
  <div style="width: 10px;">Rés</div>  
  </div>
  <div class="clearfix;"></div>
  <?php 

  for ($i=0; $i<20; $i++) { 
    if (! isset($this->articles[$i+1])) {
      $this->articles[$i+1] = array ("titre" => "", "alias" => "", "auteur" => "", "no_page" => "", 
          "nb_pages" => "", "reserve" => 0, "fichier"=>"" );
    }
    $article = (object) $this->articles[$i+1];
  ?>
  <div class="data_crampon">
  <input type="text" id="item<?php echo $i; ?>"style="width: 30px;" value="<?php echo $i+1; ?>">
  <input type="text" id="titre<?php echo $i; ?>" style="width: 280px;" value="<?php echo $article->titre; ?>">
  <input type="text" id="auteur<?php echo $i; ?>" style="width: 180px;" value="<?php echo $article->auteur; ?>">
  <input type="text" id="no_page<?php echo $i; ?>" style="width: 30px;" value="<?php echo $article->no_page; ?>">
  <input type="text" id="nb_pages<?php echo $i; ?>" style="width: 30px;" value="<?php echo $article->nb_pages; ?>">
  <input type="checkbox" id="reserve<?php echo $i; ?>" style="width: 10px;" <?php if($article->reserve==1) {echo "checked";} ?>>
  
  <?php 
  if ($this->item->id<388) { 
    echo '<input type="text" id="fichier'. $i .'" style="width: 300px;" value="' .$article->fichier .'">';
  } else { 
    echo '<input type="hidden" id="fichier'. $i .'" style="width: 300px;" value="">';
  }
  if ($article->alias<>"") {      
    if ($article->fichier<>"") {
      $file = PATH_CRAMPON."/". (string) $article->no  . "/" . $article->fichier;
    } else {
      $file = PATH_CRAMPON."/". (string) $article->no  . "/" . $article->no . "_" . $article->no_page . "_" . $article->nb_pages . ".pdf";
    }
    
    if (is_file($file)) {
      $visible = "visible";
    } else {
      $visible = "cache";
    }
  } else {
    $visible = "hide";
  }
  echo '<a href="' . JROUTE::_('index.php?option=com_crampon&view=visupdf&id='.$article->alias.'&Itemid=776') 
  . '" target="_blank" ><img id="pdf'.($i+1).'" src="/images/M_images/pdf_button.png" class="'. $visible .'"></a>';
  ?>


  </div>
  <?php } ?>
  <p style="clear: both;"><button id="sauve" class="btn btn-primary">Enregistrer</button>   
  <button id="decoupe" class="btn btn-primary">Découpage PDF</button>    </p>
  </div>

  <?php 
    // Messages de log
    if (count($this->msg)>0) {
      $message = implode("<br>", $this->msg); 
      $visible = "show";
    } else {
      $message = ""; 
      $visible = "hide";
    }
    echo '<pre id="msg" class="'. $visible. '">'.$message.'</pre>';
  ?>
  
</div>  


  
<?php } ?>

</form>
