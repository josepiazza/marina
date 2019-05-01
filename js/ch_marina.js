
$ = jQuery;

jQuery(document).ready( function(){
    
	$("#btnBuscar").on('click', function(){  
            $("#listaUsuarios").empty();
     //La llamada AJAX
       $.ajax({
           type : "get",
           url : siteUrl+'/wp-admin/admin-ajax.php', // Pon aquí tu URL
           dataType: 'json',
           data : {
               action: "get_usuarios",
               busqueda: $("#textBusqueda").val()
           },
           error: function(response){
               console.log(response);
           },
           success: function(response) {
                
                for(i=0; i<response.rta.length;i++){
                    nombre = response.rta[i].last_name+", "+response.rta[i].first_name;
                    $("#listaUsuarios").append("<option value='"+response.rta[i].user_id+"'>"+nombre+"</option>");
                }
                
           }
       })

   });


 
   $("#listaUsuarios").dblclick(function(){
        agregarUsuario( $("#listaUsuarios").val(), $("#listaUsuarios option:selected").text() );
   });


    $("#formulariEmbarcacion").submit(function(){
        
        nombre = validar_vacio("#nombre");
        matricula = validar_vacio("#matricula");
        tipo = validar_vacio("#tipo");
        if ( nombre && matricula && tipo ){
//            agregarCamposAlForm();
            rta = true;
        }else{
            alert("Hay campos obligatorios incompletos.");
            rta = false;
        }
        return rta;
        
        
    });
   
   
   	$("#btnBuscarEmbarcacion").on('click', function(){  
            $("#listaEmbarcaciones").empty();
            
     //La llamada AJAX
       $.ajax({
           type : "get",
           url : siteUrl+'/wp-admin/admin-ajax.php', // Pon aquí tu URL
           dataType: 'json',
           data : {
               action: "get_embarcaciones",
               busqueda: $("#textBusqueda").val()
           },
           error: function(response){
               console.log(response);
           },
           success: function(response) {
                for(i=0; i<response.rta.length;i++){
                    nombre = response.rta[i].nombre+", "+response.rta[i].matricula;
                    $("#listaEmbarcaciones").append("<option value='"+response.rta[i].id+"'>"+nombre+"</option>");
                }
                
           }
       })

   });
   
   $("#listaEmbarcaciones").dblclick(function(){
        agregarEmbarcacion( $("#listaEmbarcaciones option:selected").val(), $("#listaEmbarcaciones option:selected").text() );
   });
   
});

   var listaUsuarios = new Array();
   
   var agregarUsuario = function(id, texto){
       
        if( listaUsuarios.indexOf(id) == -1 ){
//            alert( listaUsuarios.indexOf(id) );
            $("#usuariosSeleccionados").append("<li id='usu_"+id+"'><button type='button' value='"+id +"' onClick='descartarUsuario( this )'>X</button>  "+texto+"</li>");
            $("#formulariEmbarcacion").append( "<input type='hidden' name='usuarios[]' id='usuHidden_"+id+"' value='"+id+"' />" );
//            listaUsuarios.push( id ); 
        }

   };
   
   var descartarUsuario = function(boton){
       $("#usu_"+$( boton ).attr('value')).remove();
       $("#usuHidden_"+$( boton ).attr('value')).remove();
//       listaUsuarios.splice( listaUsuarios.indexOf( $( boton ).attr('value')), 1);

   };

    ;
   var agregarEmbarcacion = function(id, texto){
       
        if( listaUsuarios.indexOf(id) == -1 ){
       $.ajax({
           type : "GET",
           url : siteUrl+'/wp-admin/admin-ajax.php', // Pon aquí tu URL
           dataType:'json',
           data : {
               action: "get_embarcacion_precio",
               id_emb: id
           },
           error: function(response){
               console.log(response);
           },
           success: function(response) {
              
                    precio = response.rta.precio;
                    
                    $("#monto").val( parseInt($("#monto").val()) + precio ) 
                    
                    $("#embarcaciones tbody").append("<tr id='emb_"+id+"'><td>"+texto+"</td><td><input name='precio[]' onChange='recalcularMontoPago()' value='"+precio+"'></td><td><button type='button' value='"+id +"' onClick='descartarEmbarcacion( this )'>X</button></li></td></tr>");
                    $("#formularioPago").append("<input type='hidden' name='itemPago[]' id='item_"+response.id+"' value='{\"id\": "+response.id+", \"monto\": "+precio+" }'/>");
                    recalcularMontoPago();
                
           }
       });





//            $("#embarcaciones").append("<li id='emb_"+id+"'><button type='button' value='"+id +"' onClick='descartarEmbarcacion( this )'>X</button>  "+texto+"</li>");
//            $("#formulariEmbarcacion").append( "<input type='hidden' name='usuarios[]' id='usuHidden_"+id+"' value='"+id+"' />" );
//            listaUsuarios.push( id ); 
        }

   };
   var descartarEmbarcacion = function(boton){
       $("#emb_"+$( boton ).attr('value')).remove();
       $("#item_"+$( boton ).attr('value')).remove();
       recalcularMontoPago();
   };
   
   var recalcularMontoPago = function(){
       
//       alert( $("input[name='precio[]'").length );
        acum = 0;
        $("input[name='precio[]'").each(function(){
            acum = acum + parseInt( this.value );
        });
        jQuery("#monto").val( acum );
   }
   
   var agregarCamposAlForm = function(){
       
       
//       for(i=0; i<listaUsuarios.length; i++){
//           $("#formulariEmbarcacion").append( "<input type='hidden' name='usuarios[]' value='"+listaUsuarios[i]+"' />" );
//       }
       
   };
   
   var validar_vacio = function(campo){
       if( $(campo).val().length == 0 ){ 
           $(campo).addClass("campo_obligatorio"); 
           return false;
       }else{
           $(campo).removeClass("campo_obligatorio"); 
           return true;
       }
   };