
$ = jQuery;

jQuery(document).ready( function(){
	$("#btnBuscar").on('click', function(){  
            $("#listaUsuarios").empty();
     //La llamada AJAX
       $.ajax({
           type : "get",
           url : 'http://marinasauce.com/wp-admin/admin-ajax.php', // Pon aquí tu URL
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
       
//        texto = $("#listaUsuarios option:selected").text();
//        $("#usuariosSeleccionados").append("<li id='usu_"+$("#listaUsuarios").val() +"'><button type='button' value='"+$("#listaUsuarios").val() +"' onClick='descartarUsuario( this )'>X</button>  "+texto+"</li>");
//        listaUsuarios.push(new Array( $("#listaUsuarios").val() )); 
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