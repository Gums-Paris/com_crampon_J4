<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
use \Joomla\CMS\Factory;
setlocale(LC_TIME, "fr_FR.UTF8");
$this->guest = Factory::getUser()->guest;
?>
<div id="blogcrampon">
<div class="cb_template">
<form id="listCrampon" method="get" action="<?php echo JROUTE::_('index.php?option=com_crampon&view=liste'); ?>">
<h3>Le Crampon</h3>
<select name="an" id="an" onChange="this.form.submit()">
    <?php echo CramponHelper::selectAn($this->an, true); ?>
</select>
<input type="text" class="form-control" id ="search" name="search" value="<?php echo $this->search; ?>" placeholder="Mot clé...">
<button type="submit" name="Submit" class="btn"><span class="fa-raw fa-search"></span></button>
<button type="submit" name="reset" class="btn" onClick="myFunction()"><span class="fa-raw fa-times-circle-o"></span></button>
</form>
</div>
<script>
function myFunction() {
    jQuery("#an").val(0);
    jQuery("#search").val("");
}
</script>
<?php 

//$user	= Factory::getUser();

foreach($this->crampons as $no => $crampon) {     

    // Affichage vignette + lien popup
    //
    $img = "/home/gumspari/www/images/crampon/". $no ."_petit.jpg";
    $img2 = "/home/gumspari/www/images/crampon/". $no ."_moyen.jpg";   


/*
    if ($user->id == 62 ) {                   

        if (! is_file($img2) ) {        
            echo 'test = ' . $no; 
            $this->mod->vignette_couverture($no);            
        }
        
    }
*/


    if (is_file($img) ) {        
      $image = "/images/crampon/" . $no ."_petit.jpg";        
    } else {
        $image = "";          
    }    
    if (is_file($img2) ) {        

        if ($crampon[0]->titre_couverture<>'') {
            $mediabox_title = 'data-mediabox-title="'.$crampon[0]->titre_couverture.'"';
        } else {
            $mediabox_title = '';
        }
        if ($crampon[0]->auteur_couverture<>'') {
            $mediabox_caption = 'data-mediabox-caption="par '.$crampon[0]->auteur_couverture.'"';
        } else {
            $mediabox_caption = '';
        }

        $href_image = '<a href="/images/crampon/' . $no .'_moyen.jpg" target="_blank" class="jcepopup" 
            '.$mediabox_title.' '.$mediabox_caption.'>';        
        $href_image2 = "</a>";
        $class_image = '';
    } else {
        $href_image = "";          
        $href_image2 = "";
        $class_image = 'class="no_lien"';;
    }
    $txt_image = "Couverture crampon n° ".$no;

    // Numéros visibles uniquement par éditeurs crampons
    //
    if (isset($crampon[0]->non_publie) and $crampon[0]->non_publie) {
        $class_h2 = ' class="non_publie"';
        $npub = ' ** Reservé abonnés ** ';
        if(! $this->abonne) {            
            $npub .= '<a href="index.php/revue-le-crampon/abonnement"> =&gt; <span style="text-decoration: underline;">s\'abonner</span></a>';
        }
    } else {
        $class_h2 = "";
        $npub = "";
    }

    // Numéros hors série
    if ($no>900) {
        $no_affiche = 'N° Hors-Serie '.($no-900).' - '.strftime('%Y', strtotime($crampon[0]->date));
    } else {
        $no_affiche = 'N° ' . $no . ' - ' . ucfirst(strftime('%B %Y', strtotime($crampon[0]->date)));
    }
    
?>
<div style="clear: both;">
<h2 <?php echo $class_h2;?>>
<span>
<?php echo $no_affiche . $npub; ?>
</span>
<?php
    if ($this->admin) {
        echo '<span style="float: right;">'.
        '<a href="'.JROUTE::_('index.php?option=com_crampon&view=crampon&Itemid=850&id='.$no).'"><i class="icon-pencil"></i></a>'        
        .'</span>';
    }
?>
</h2>
<?php echo $href_image; ?>
<img src="<?php echo $image; ?>" alt="<?php echo $txt_image; ?>" title="<?php echo $txt_image; ?>" <?php echo $class_image; ?>>
<?php echo $href_image2; ?>
<ul>
<?php 
foreach($crampon as $article) { 
    echo '<li>';
    if ( ($this->guest and $article->reserve) or (! $this->abonne and $article->non_publie)) {
        echo $article->titre;
    } else {
		echo '<a href="'. JROUTE::_('index.php?option=com_crampon&view=visupdf&id='.$article->alias.'&Itemid=776') .'" target="_blank">'
            . $article->titre .'</a>';
    }
    if ($article->auteur<>"") { 
        echo ' par ' . $article->auteur; 
    }
    if ( $article->reserve) { 
        echo ' <i class="icon-lock" style="color: #80808099;"></i>';
    }
    echo '</li>';
}
if ($this->abonne and $this->search == "") {
    echo '<li style="list-style: none; margin-left: -12px; font-weight: bold">';
    echo '<a href="'. JROUTE::_('index.php?option=com_crampon&view=visupdf&no='.$no.'&Itemid=776') .'" target="_blank">Numéro '. $no .' complet</a> (attention ! fichier volumineux)';
    echo '</li>';

}


?>
</ul>
</div>
<?php } ?>
</div>
<div style="clear: both; text-align: center;">
<?php echo $this->pagination->getListFooter(); ?>
</div>
