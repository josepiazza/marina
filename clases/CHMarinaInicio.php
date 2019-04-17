<?php
namespace ch_marina\marina;
/**
 * Description of CHMarinaInicio
 *
 * @author chicho
 */

use ch_marina\marina\clases\CHMarinaEmbarcacion;
use ch_marina\marina\clases\CHMarinaUsuario;



class CHMarinaInicio {
    
    public function __construct() {
        add_action("admin_menu", array($this, "crearMenu"));
//        add_action( 'wp_enqueue_scripts', [$this, 'ajax_enqueue_scripts'] );
        add_action( 'wp_ajax_nopriv_get_usuarios', [$this, 'notify_button_click' ] );
        // Hook para usuarios logueados
        add_action( 'wp_ajax_get_usuarios', [ $this, 'notify_button_click' ]);
    }
    
    public function crearMenu(){
        
        add_menu_page("CH_Marina", "Marina", "publish_pages", "ch_marina_menu_administrador", [$this, "listado_marina" ]);
        add_submenu_page("ch_marina_menu_administrador", "Pagos", "Embarcaciones", "manage_options", "listado_marina", [$this, "listado_marina"]);


    }
  
    public function ajax_enqueue_scripts(){
        wp_enqueue_script("jquery");
        
    }


    // Función que procesa la llamada AJAX
    public function notify_button_click(){
        // Check parameters
        
        $usuarios = new CHMarinaUsuario();
        $lista = $usuarios->get_lista(["parametrosBusqueda"=>$_GET["busqueda"]]);
        
        wp_send_json(["busqueda"=>$_GET["busqueda"], "rta"=>$lista], 200);
        
//        $message  = isset( $_GET['message'] ) ? $_GET['message'] : false;
//        if( !$message ){
//            wp_send_json( array('message' => __('Message not received :(', 'wpduf') ) );
//        }
//        else{
//            wp_send_json( array('message' => __('Message received, greetings from server!', 'wpduf') ) );
//        }
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
}

