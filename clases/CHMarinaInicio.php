<?php
namespace ch_marina\marina;
/**
 * Description of CHMarinaInicio
 *
 * @author chicho
 */

use ch_marina\marina\clases\CHMarinaEmbarcacion;
use ch_marina\marina\clases\CHMarinaUsuario;
use ch_marina\marina\clases\CHMarinaPago;

class CHMarinaInicio {
    
    public function __construct() {
        add_action("admin_menu", array($this, "crearMenu"));
//        add_action( 'wp_enqueue_scripts', [$this, 'ajax_enqueue_scripts'] );
        add_action( 'wp_ajax_nopriv_get_usuarios', [$this, 'notify_button_click' ] );
        // Hook para usuarios logueados
        add_action( 'wp_ajax_get_usuarios', [ $this, 'ajax_get_usuarios' ]);
        add_action( 'wp_ajax_get_embarcaciones', [ $this, 'ajax_get_embarcaciones' ]);
        add_action( 'wp_ajax_get_embarcacion_precio', [ $this, 'ajax_get_embarcacion_precio' ]);
    }
    
    public function crearMenu(){
        
        add_menu_page("CH_Marina", "Marina", "publish_pages", "ch_marina_menu_administrador", [$this, "listado_marina" ]);
        add_submenu_page("ch_marina_menu_administrador", "Pagos", "Pagos", "manage_options", "listado_marina", [$this, "listadoPagos"]);


    }
  
    public function ajax_enqueue_scripts(){
        wp_enqueue_script("jquery");
        
    }


    // Función que procesa la llamada AJAX
    public function ajax_get_usuarios(){
        $usuarios = new CHMarinaUsuario();
        $lista = $usuarios->get_lista(["parametrosBusqueda"=>$_GET["busqueda"]]);    
        wp_send_json(["busqueda"=>$_GET["busqueda"], "rta"=>$lista], 200);
    }
    
    public function ajax_get_embarcacion_precio(){
        $em = new CHMarinaEmbarcacion();
        $em->inicializar( intval( $_GET["id_emb"] ) );
        $precio2 = floatval($em->getPrecio());
        $precio = $precio2;
        $rta = ["id"=>$_GET["id_emb"] , "rta"=>["precio"=> $precio2, "nombre"=>665] ];
        wp_send_json( $rta, 200);
    }
    
    public function ajax_get_embarcaciones(){
        $em = new CHMarinaEmbarcacion();
        $lista = $em->get_lista(["parametrosBusqueda"=>$_GET["busqueda"]]);
        wp_send_json(["busqueda"=>$_GET["busqueda"], "rta"=>$lista], 200);

    }
    
    public function listado_marina(){
        print "<h1>Marina</h1>";
         $usuarios = new CHMarinaUsuario();
        $lista = $usuarios->get_lista();       
        switch($_REQUEST["modo"]){
            case "altaEmb":
                $this->formulario_embarcacion_admin();
                break;
            case "procesar":
                $id_embarcacion = $this->procesar_alta($_REQUEST);

        
                if( $_REQUEST["guardar"] == "guardar" ){        
                    $this->formulario_agregar_usuario( $id_embarcacion );
                }else{
                    $this->mostrar_listado_embarcaciones();
                }
                
                break;
            case "procesarUsuario":
                
                if($_REQUEST["guardar"] != "Salir"){
                    $guardarUsuariosRta = $this->procesar_alta_usuario($_REQUEST);
                    if( ! is_wp_error( $guardarUsuariosRta ) ){

                        if($_REQUEST["guardar"] == "GuardarYSalir"){
                            $this->mostrar_listado_embarcaciones();
                        }else if( $_REQUEST["guardar"] == "Guardar" ){
                            $this->formulario_agregar_usuario( $_REQUEST["id_embarcacion"] );
                        }else{

                        }
                    }else{
                        $this->formulario_agregar_usuario( $id_embarcacion, $_REQUEST );
                        print "<script> alert('".$guardarUsuariosRta->get_error_message()."'); </script>";
                    }
                }else{
                    $this->mostrar_listado_embarcaciones();
                }
                break;
            case "edit":
                switch( $_REQUEST["tbl"] ){
                    case "embarcacion":
                        $this->formulario_embarcacion_admin( addslashes( $_REQUEST["id"]) );
                        break;
                }
                break;
            case "pagos":
                $this->getListaPagos( $_REQUEST );
                break;
            default:
                $this->correrTest();
                $this->mostrar_listado_embarcaciones();
                
        }

    }
    
    public function correrTest(){
        $em = new CHMarinaEmbarcacion();
        $em->inicializar(3);
        print "<h1>".$em->getNombre()."</h1>";
    }
    
    public function mostrar_listado_embarcaciones(){
                $emb = new CHMarinaEmbarcacion();
                print "<a href='?page=ch_marina_menu_administrador&modo=altaEmb'>Nuevo</a>";
                print $emb->get_tabla_html($_REQUEST);          
    }
    
    public function formulario_embarcacion_admin( $id_embacacion = null ){
        $emb = new CHMarinaEmbarcacion();
        $tipo_embarcacion = $emb->get_tipo_embarcacion();
        $tipos_estado = $emb->get_tipos_estado();
        $siteUrl = get_site_url();
        echo "<script>var siteUrl = '".$siteUrl."'</script>";
        wp_enqueue_script( 'ch_marina', plugins_url( 'marina/js/ch_marina.js'), array('jquery'),'1.1', true );
        wp_enqueue_style( 'ch_marina_css', plugins_url( 'marina/css/ch_marina.css'),array(), NULL);
        $formId = "";
        if( !empty( $id_embacacion ) ){
            $emb->inicializar($id_embacacion);
            $color = $emb->getColor();
            $eslora = $emb->getEslora();
            $marca = $emb->getMarca();
            $matricula = $emb->getMatricula();
            $tipo = $emb->getTipo();
            $ubicacion = $emb->getUbicacion();
            $nombre = $emb->getNombre();
            $estado = $emb->getEstado();
            $precio = $emb->getPrecio();
//            print "<h1>id: ".$emb->getId()."</h1>";
            $formId = "<input type='hidden' name='id_embarcacion' value='$id_embacacion'/>";
            
            $listaUsuarios = $emb->getListaUsuarios();
            
            $scriptUsu = "";
            foreach($listaUsuarios as $usu){
                $scriptUsu.="agregarUsuario('".$usu->id_user."', '".$usu->nombre."');\n ";
            }
            
        }


        ?>
<div>
<form id="formulariEmbarcacion" action="?page=ch_marina_menu_administrador&modo=procesar" method="post" onkeypress="return event.keyCode != 13;">
<?=$formId?>
    <div>
        <p>
            <label for="nombre"><?php _e( 'Nombre Embarcación' ) ?><br />
                <input type="text" name="nombre" id="nombre" class="input" value="<?php echo esc_attr(  $nombre  ); ?>" size="25"  /></label>
        </p>
        <p>
            <label for="matricula"><?php _e( 'Matrícula' ) ?><br />
                <input type="text" name="matricula" id="matricula" class="input" value="<?php echo esc_attr(  $matricula  ); ?>" size="25" /></label>
        </p>      

        
        <p>
            <label for="estado"><?php _e( 'Estado administrativo' ) ?>
                <br />
                <select name="estado" id="estado">
                    <option value="">Seleccionar</option>
                    <?php foreach($tipos_estado as $te){ ?>
                    <option value="<?php echo $te->id ?>" ><?php echo $te->descripcion ?></option>
                    <?php  } ?>
                </select>
                <script>
                    jQuery("#estado").val( <?=$estado ?> );
                </script>
            </label>
        </p>
        

        <p>
            <label for="tipo_embarcacion"><?php _e( 'Tipo de Embarcación' ) ?>
                <br />
                <select name="tipo" id="tipo">
                    <option value="">Seleccionar</option>
                    <?php foreach($tipo_embarcacion as $te){ ?>
                    <option value="<?php echo $te->id ?>" ><?php echo $te->descripcion ?></option>
                    <?php  } ?>
                </select>
                <script>
                    jQuery("#tipo").val( <?=$tipo ?> );
                </script>
            </label>
        </p>
<!--        <p>
            <label for="eslora"><?php _e( 'Eslora' ) ?><br />
                <input type="text" name="eslora" id="eslora" class="input" value="<?php echo esc_attr(  $eslora  ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="color"><?php _e( 'Color' ) ?><br />
                <input type="text" name="color" id="color" class="input" value="<?php echo esc_attr(  $color  ); ?>" size="20" /></label>
        </p>-->
<!--        <p>
            <label for="ubicacion"><?php _e( 'Ubicación' ) ?><br />
                <input type="text" name="ubicacion" id="ubicacion" class="input" value="<?php echo esc_attr(  $ubicacion  ); ?>" size="120" /></label>
        </p>-->
        <p>
            <label for="marca"><?php _e( 'Marca' ) ?><br />
                <input type="text" name="marca" id="marca" class="input" value="<?php echo esc_attr(  $marca  ); ?>" size="100" /></label>
        </p>
        
        <div>
            <label for="precio"><?php _e( 'Cuota Mensual vigente' ) ?><br />
                <input type="text" name="precio" id="precio" class="input" value="<?php echo esc_attr(  $precio  ); ?>" size="10" /></label>
                <blockquote>
                    <?php _e( 'Precions anteriores:' ) ?><br/>
                    <table>
                        <tr>
                            <td>Desde</td>
                            <td>Hata</td>
                            <td>Valor</td>
                        </tr>
                    </table>
                </blockquote>
        </div>
        
        <div>
            <div style="">
                Buscar:<br/>
                <input type="text" name="busqueda" id="textBusqueda">
                <button name="buscar" id="btnBuscar" type="button" >Buscar</button><br/>
                <select name="listaUsuarios" id="listaUsuarios" multiple  style="width: 400px; height: 100px">
                    
                </select>

            </div>
            <div style="width: 100px">
                                <ul id="usuariosSeleccionados" style="width: 200px">
                </ul>
            </div>
        </div>
        <script>
            
            jQuery(document).ready( function(){
                <?=$scriptUsu?>
            });
            
        </script>

        
        <p><button name="guardar" id="btnGuardar" value="guardar">Guardar</button></p>
        <p><button name="guardar" id="btnGuardar" value="guardarysalir">Guardar y salir</button></p>
        </div>
        </form>
    </div>
        <?php
    }
    
    public function formulario_agregar_usuario($id_embarcacion, $reques = null){
        
        if( !empty($reques) ){
            $nombre = $reques["nombre"];
            $apellido = $reques["apellido"];
            $dni = $reques["dni"];
            $user_email = $reques["user_email"];
            $telefono = $reques["telefono"];
        }
       ?>
<h2>Usuario</h2>
<form action="?page=ch_marina_menu_administrador&modo=procesarUsuario" method="post">
    <input type="hidden" name="id_embarcacion" value="<?php echo( $id_embarcacion ); ?>" />
        <p>
            <label for="nombre"><?php _e( 'Nombre' ) ?><br />
                <input type="text" name="nombre" id="nombre" class="input" value="<?php echo esc_attr(  $nombre  ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="apellido"><?php _e( 'Apellido' ) ?><br />
                <input type="text" name="apellido" id="apellido" class="input" value="<?php echo esc_attr(  $apellido  ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="dni"><?php _e( 'DNI' ) ?><br />
                <input type="text" name="dni" id="dni" class="input" value="<?php echo esc_attr(  $dni  ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="user_email"><?php _e( 'Email' ) ?><br />
                <input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(  $user_email  ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="telefono"><?php _e( 'Telefono' ) ?><br />
                <input type="text" name="telefono" id="telefono" class="input" value="<?php echo esc_attr(  $telefono  ); ?>" size="25" /></label>
        </p>
        <p><input type="submit" name="guardar" value="Guardar" />
        <input type="submit" name="guardar" value="GuardarYSalir" />
        <input type="submit" name="guardar" value="Salir"/>
        </p>
</form>        

        <?php
    }
    
    public function procesar_alta($post){
        
        $emb = new CHMarinaEmbarcacion();
        
        if( !empty($post["id_embarcacion"] )){
            $emb->inicializar( addslashes($post["id_embarcacion"]) );
        }
        
        if( !empty($post["color"])) $emb->setColor(addslashes($post["color"]));
        if( !empty($post["eslora"])) $emb->setEslora(addslashes($post["eslora"]));
        if( !empty($post["marca"])) $emb->setMarca(addslashes($post["marca"]));
        if( !empty($post["nombre"])) $emb->setNombre(addslashes($post["nombre"]));
        if( !empty($post["tipo"])) $emb->setTipo(addslashes($post["tipo"]));
        if( !empty($post["ubicacion"])) $emb->setUbicacion(addslashes($post["ubicacion"]));
        if( !empty($post["matricula"])) $emb->setMatricula(addslashes($post["matricula"]));
        if( !empty($post["estado"])) $emb->setEstado(addslashes($post["estado"]));
        
        
        
        $id_embarcacion = $emb->guardar();
        
        if( !empty( $post["precio"] ) ){
            $emb->agregarPrecio( $post["precio"] );
        }
        
        $emb->limpiarUsuarios();
        if( !empty( $post["usuarios"] ) ){
            foreach(  $post["usuarios"] as $user_id ){
                $emb->agregarUsuario($user_id);
            }
        }
        return $id_embarcacion;
    }
    
    public function procesar_alta_usuario($post){
        $user = new CHMarinaUsuario();
        $user->setApellido($post["apellido"]);
        
        $user->setNombre ($post["nombre"]);
        $user->setDni ($post["dni"]);
        $user->setUser_email ($post["user_email"]);
        $user->setTelefono ($post["telefono"]);
        
        if( $user->guardar()){
            $user->agregar_embarcacion($post["id_embarcacion"]);
            return true;
        }else{
            return $user->getError();
        }
    }

    public function getListaPagos(){
        
        switch($_REQUEST["modoPago"]){

            case "nuevoPago":
                $this->getFormularioPago();
                break;
            case "guardarPago":
                if($_REQUEST["guardar"] == "Guardar"){
                    $this->guardarPago();
                }else{
                   $this->listadoPagos(); 
                }
                
                break;
            case "verPago":
                $this->getFormularioPago($_REQUEST["id"], true);
                break;
            default:
                $this->listadoPagos();
        }
        
        
    }
    
    public function listadoPagos(){
        
        wp_enqueue_style( 'ch_marina_css', plugins_url( 'marina/css/ch_marina.css'),array(), NULL);
        $pago = new CHMarinaPago();
        $tabla = $pago->get_tabla_html( null );
        
        $rta = <<<RTA
 
    <div class="row">
        <button onClick='location.href="?page=ch_marina_menu_administrador&modo=pagos&id=$id_embacacion&modoPago=nuevoPago"'>Nuevo pago</button>
    </div>
    <div class="row">
                <h2>Listado</h2>
         $tabla       
    </div>
RTA;
        
        print $rta;
        
        
        
    }
    
    public function guardarPago(){
//        print_r($_REQUEST);
        
        $pago = new CHMarinaPago();
        $items = $_REQUEST["itemPago"];
        $monto = 0;
        foreach($items as $itemPago){
            $itemPago = str_replace("\\", "",  $itemPago) ;
            $itemPago = json_decode($itemPago);
            $monto += $itemPago->monto;
            $pago->agregarItem($itemPago);
        }
        
        $fechaPago = strtotime($_REQUEST["datepicker"]);
        $fechaPago = date("Y-m-d", $fechaPago);
        $pago->setFecha_alta(date("Y-m-d"));
        $pago->setFecha_pago( $fechaPago );
        $pago->setMonto($monto);
        $pago->setTipo_pago($_REQUEST["tipo_pago"]);
//        exit();
        $pago->guardar();
                
                
//        id_pago] => [tipo_pago] => 1 [datepicker] => 04/29/2019
    }
    
    public function cabeceraEmbarcacion( $id_embacacion ){
        
        wp_enqueue_style( 'ch_marina_css', plugins_url( 'marina/css/ch_marina.css'),array(), NULL);
        
        $emb = new CHMarinaEmbarcacion();
        $emb->inicializar($id_embacacion);
        $marca = $emb->getMarca();
        $matricula = $emb->getMatricula();
        $tipo = $emb->getTipo();
        $ubicacion = $emb->getUbicacion();
        $nombre = $emb->getNombre();
        $estado = $emb->getEstado();
        $precio = $emb->getPrecio();
        
        $rta = <<<RTA
   
   <div class="row">
        <div class="cajaDato">
                <div class="negrita">Nombre:</div>
                <div>$nombre</div>
        </div>
        <div class="cajaDato">
                <div class="negrita">Matricula</div>
                <div>$matricula</div>
        </div>
   
   </div>
    <div class="row">
        <button onClick='location.href="?page=ch_marina_menu_administrador&modo=pagos&id=$id_embacacion&modoPago=nuevoPago"'>Nuevo pago</button>
    </div>
RTA;
        
        return $rta;
        
        
    }
    
    public function getFormularioPago( $id=null, $modoVer = false ){
            // Load the datepicker script (pre-registered in WordPress).
        wp_enqueue_script( 'jquery-ui-datepicker' );

        // You need styling for the datepicker. For simplicity I've linked to Google's hosted jQuery UI CSS.
        wp_register_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );  
        
        wp_enqueue_script( 'ch_marina', plugins_url( 'marina/js/ch_marina.js'), array(),NULL );
        wp_enqueue_style( 'ch_marina_css', plugins_url( 'marina/css/ch_marina.css'),array(), NULL);
        $siteUrl = get_site_url();
        echo "<script>var siteUrl = '".$siteUrl."'</script>";
        
        $pago = new CHMarinaPago();
        
        $tiposPago = $pago->getTiposPago();
        
        
        if( isset($id) ){
            $pago->inicializar($id);
            $fechaPago = $pago->getFecha_pago();
            $fechaAlta = $pago->getFecha_alta();
            $monto = $pago->getMonto();
            $tipoPago = $pago->getTipo_pago();
            $items = $pago->getItems();
            foreach($items as $i){
                $itemHTML .= "<tr><td>$i->nombre</td><td>$i->importe</td></tr>";
            }
        }else{
            $monto = 0;
            $id_pago = "";
            $tipoPago = "";
        
        }
        $disabled = "";
        if($modoVer == true){
            $disabled = "disabled";
        }
        
       ?>
<h2>Usuario</h2>
<form action="?page=ch_marina_menu_administrador&modo=pagos&modoPago=guardarPago" method="post" id="formularioPago">
    <!--<input type="hidden" name="id_pago" value="" />-->
            <label for="tipo_pago"><?php _e( 'Tipo de Pago' ) ?>
                <br />
                <select name="tipo_pago" id="tipo_pago" <?=$disabled?> >
                    <option value="">Seleccionar</option>
                    <?php foreach($tiposPago as $te){ ?>
                    <option value="<?php echo $te->id ?>" ><?php echo $te->descripcion ?></option>
                    <?php  } ?>
                </select>
            </label>
        <p>
            <label for="fecha_pago"><?php _e( 'Fecha Pago' ) ?><br />
                <input type="text" name="datepicker" id="datepicker" class="input" value="<?=$fechaPago?>" size="25" <?=$disabled?> /></label>
        </p>
        <p>
            <label for="monto"><?php _e( 'Monto a pagar' ) ?><br />
                <input type="text" name="monto" id="monto" class="input" value="<?=$monto?>" size="25" disabled /></label>
        </p>

        
        <div>
            <?php if($modoVer == false){ ?>
            <div class="row">
                Buscar:<br/>
                <input type="text" name="busqueda" id="textBusqueda">
                <button name="btnBuscarEmbarcacion" id="btnBuscarEmbarcacion" type="button" >Buscar</button><br/>
                <select name="listaEmbarcaciones" id="listaEmbarcaciones" multiple  style="width: 400px; height: 100px">
                    
                </select>

            </div>
            <?php } ?>
            <div  class="row">
                
                <table id="embarcaciones"   style="width: 450px">
                    <thead><tr>
                            <td style="width: 200px">Nombre</td>
                            <td style="width: 200px">Precio</td>
                            <?php if($modoVer == false){ ?><td style="width: 50px">Quitar</td><?php } ?>
                        </tr></thead>
                    <tbody>
                        <?=$itemHTML?>
                    </tbody>
                </table>

            </div>
        </div>
        <?php if($modoVer == false){ ?>
        <p><input type="submit" name="guardar" value="Guardar" />
        <input type="submit" name="guardar" value="Salir"/>
        </p>
        <?php }else{ ?>
        <p><input type="submit" name="guardar" value="Borrar" />
        <input type="submit" name="guardar" value="Salir"/>
        <?php } ?>
</form>        
<script>
    
    jQuery("#tipo_pago").val( <?=$tipoPago ?> );
    
    jQuery(document).ready(function($) {
        $("#datepicker").datepicker();
    });
    
    jQuery("#monto").val( <?=$monto?> );
    
</script>
        <?php
        
    }
    
    public function listado_pagos(  ){
        ?>
    <div class="row">
        <button onClick='location.href="?page=ch_marina_menu_administrador&modo=pagos&id=$id_embacacion&modoPago=nuevoPago"'>Nuevo pago</button>
    </div>
<?php
    }
}

