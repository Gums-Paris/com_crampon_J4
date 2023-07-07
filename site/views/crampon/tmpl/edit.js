jQuery(document).ready(function($){

    $("#change_date").click(function(e) {
      e.preventDefault();      
      change_date();
    });

    $("#changer").click(function(e) {
      e.preventDefault();      
      $('#upload').toggle();
    });
    $("#couverture").click(function(e) {
      e.preventDefault();      
      couverture1();
    });
  
    $("#nettoie").click(function(e) {
      e.preventDefault();      
      nettoie();
    });
  
    $("#remplit").click(function(e) {
      e.preventDefault();      
      remplit();
    });
  
    $("#sauve").click(function(e) {
      e.preventDefault();      
      sauve();
    });    

    $("#decoupe").click(function(e) {
      e.preventDefault();      
      //$("#task").val("decoupe");
      //submitform( "decoupe" );            
      decoupe();
    });    
  
    $("div.data_crampon :input").keyup(function() {
      if ($("#sauve").hasClass("btn-success") || $("#sauve").hasClass("btn-primary") ) {
        $("#sauve").attr( "class", "btn btn-warning" );
        $("#decoupe").attr("disabled", "disabled");
        $("#msg").attr( "class", "hide" );
        $("#msg").text("");
      }
    })

    $(window).on('beforeunload',function(){
      if ($("#sauve").hasClass("btn-warning") ) {
        return "Attention : données non sauvegardées";
      }
   });


    function nettoie() {
      texte = $("#sommaire").val();
      texte = texte.replace(/\.{2,50}\s{0,1}(?=[0-9]{1,2})/gm, "µ");
      texte = texte.replace(/\.{2,50}/gm, "");
      texte = texte.replace(/\n/gm, " ");
      texte = texte.replace(/\s{2,}/gm, " ");
      
      m = texte.match(/(µ[0-9]{1,2})/gm, "");
      for (var i = 0, len = m.length; i < len; i++) {
        texte = texte.replace( m[i], " "+m[i]+"\n") 
      }
      $("#sommaire").val(texte);
    }  
    
    function remplit() {
                 
      var ks = $('#sommaire').val().split(/\r?\n/);
      ks.unshift('Edito µ2');

      $.each(ks, function(k) { 
        txt = ks[k];        
        tt = txt.match(/µ([0-9]*)/g);
        if (tt !== null) {
          no_page = tt[0].substr(1).trim();
          txt = txt.replace(tt[0], '');          
        } else {
            no_page = '';
        }
        
        tt = null;
        tt = txt.match(/par(.*)/g);
        if (tt !== null) {
          auteur = tt[0].substr(3).trim();
          txt = txt.replace(tt[0], '');          
        } else {
            auteur = '';
        }
        titre = txt.trim();
        
        $("#titre"+k).val(titre);
        $("#auteur"+k).val(auteur);
        $("#no_page"+k).val(no_page);
        $("#nb_pages"+k).val("1");
                        
     });
    }
    
    function getData() {
    
      data = [];
    
      for (var i = 0, len = 20; i < len; i++) {
        if ($("#titre"+i).val().trim() != "") {

          item = new Object();
          
          item.no       = $("#no").val();
          item.item     = $("#item"+i).val(),
          item.titre    = $("#titre"+i).val();
          item.auteur   = $("#auteur"+i).val();
          item.no_page  = $("#no_page"+i).val();
          item.nb_pages = $("#nb_pages"+i).val();           
          item.fichier  = $("#fichier"+i).val();          
          if ($("#reserve"+i).prop('checked')) {
            item.reserve = 1;
          } else {
            item.reserve = 0;
          }          
          data.push(item);

        } 
                 
      } 
      
      data = data.sort(trier);                 
      for (var i = 0, len = 20; i < len; i++) {
        if(data[i]) {
          $("#item"+i).val(i+1);
          $("#titre"+i).val(data[i].titre);
          $("#auteur"+i).val(data[i].auteur);
          $("#no_page"+i).val(data[i].no_page);
          $("#nb_pages"+i).val(data[i].nb_pages);           
          $("#fichier"+i).val(data[i].fichier);   
          if (data[i].reserve==1) {
            $("#reserve"+i).prop('checked', true);
          } else {
            $("#reserve"+i).prop('checked', false);
          }
        } else {
          $("#item"+i).val("");
          $("#titre"+i).val("");
          $("#auteur"+i).val("");
          $("#no_page"+i).val("");
          $("#nb_pages"+i).val("");           
          $("#fichier"+i).val(""); 
          $("#pdf" + i).attr("class", "cache");  
          $("#reserve"+i).prop('checked', false);
        }
      }    
      return data;
    
    }
    
    function trier(a, b) {    
      return parseInt(a.item) > parseInt(b.item);    
    }
    
    function sauve() {      
      liste = encodeURIComponent(JSON.stringify(getData()));      
      $.post({
        url: "index.php?option=com_crampon&format=raw&Itemid=777&task=save", 
        type: "POST",
        data: "liste=" + liste,        
        success: function(data,status,xhr) {            
            $("#sauve").attr( "class", "btn btn-success" );
            $("#decoupe").removeAttr("disabled"); 
            $("#msg").attr( "class", "show" );
            $("#msg").text(data);
        },
        error:  function(data,status,xhr) {            
          console.log(data);
          console.log(status);
          console.log(xhr);
        }
      });
    }

    function change_date() {
      no =  $("#no" ).val();
      mois = $("#mois" ).val();
      an =  $("#an" ).val();
      $.post({
        url: "index.php?option=com_crampon&format=raw&Itemid=777&task=change_date",
        type: "POST",
        data: "no=" + no + "&mois=" + mois + "&an=" + an,        
        success: function(data,status,xhr) {
            console.log(data);
            if (data==1) {
             $("#change_date").attr( "class", "btn btn-success" );
            }
        }
      });
    }
   
    function couverture1() {
      couverture = new Object();      
      couverture.no = $("#no" ).val();
      if ($('#check_couverture').prop('checked')) {
        couverture.check =  1;  
      } else {
        couverture.check =  0;  
      }            
      couverture.titre = $("#titre_couverture" ).val();
      couverture.auteur = $("#auteur_couverture" ).val();
      couv = JSON.stringify(couverture);    
  
      $.post({
        url: "index.php?option=com_crampon&format=raw&Itemid=777&task=couverture", 
        type: "POST",
        data: "couv=" + couv,        
        success: function(data,status,xhr) {
            console.log(data);
             $("#couverture").attr( "class", "btn btn-success" );
             $("#couvimg").attr("src", "/images/crampon/" + couverture.no +"_petit.jpg?timestamp=" + new Date().getTime());
        }
      });
    }

    function decoupe() {
      no =  $("#no" ).val();
      nb = 0;
      for(var i= 0; i < 20; i++)
            {
              $("#pdf" + (i+1)).attr("class", "cache");
            }
      $.post({
        url: "index.php?option=com_crampon&format=raw&Itemid=777&task=decoupe", 
        type: "POST",
        data: "no=" + no,        
        success: function(data,status,xhr) {
            console.log(data);            
            $("#decoupe").attr( "class", "btn btn-success" );
            $("#msg").attr( "class", "show" );            
            sp = data.split(";");
            for(var i= 0; i < sp.length; i++)
            {
              sp2 = sp[i].split(":");
              if (sp2[1].replace(/(\n)/gm, "").trim() == "true") {
                $("#pdf" + sp2[0]).attr("class", "visible");
                nb++;
              } else {
                $("#pdf" + sp2[0]).attr("class", "cache");
              }
            }
            $("#msg").text(nb + " fichiers pdf créés");                    
        },
        error: function(xhr, textStatus, error){
            console.log(xhr.statusText);
            console.log(textStatus);
            console.log(error);
            $("#msg").attr( "class", "show" );            
            $("#msg").text("Erreur découpe");                    
        }
        
      });
           
    }
    
});

function submitbutton(pressbutton)
{
	var form = document.adminForm;
  submitform( pressbutton );
}
   
