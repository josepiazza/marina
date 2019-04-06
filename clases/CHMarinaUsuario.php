<?php
namespace ch_marina\marina\clases;

/**
 * Description of CHMarinaUsuario
 *
 * @author chicho
 */


use ch_marina\marina\clases\CHMarinaCore;
use WP_User_Query;


class CHMarinaUsuario extends CHMarinaCore{

    protected $user_login;
    protected $user_email;
    protected $user_pass;
    protected $nombre;
    protected $apellido;
    protected $dni;
    protected $telefono;
    protected $user_id;

    protected $error;

    public function guardar(){
        
        $this->validar_alta();
        $userdata = ['user_login'=>$this->dni,
                    'user_email'=>$this->user_email, 
                    'user_pass'=>$this->dni,
        ];
        $user_id = wp_insert_user($userdata);
        
        if ( ! is_wp_error( $user_id ) ) {
            $this->user_id = $user_id;
        
            if( !empty( $this->nombre ) ) update_user_meta( $this->user_id, 'first_name', sanitize_text_field( $this->nombre ) );
            if( !empty( $this->apellido ) ) update_user_meta( $this->user_id, 'last_name', sanitize_text_field( $this->apellido ) );

            if( !empty( $this->dni ) ) update_user_meta( $this->user_id, 'dni', sanitize_text_field( $this->dni ) );
            if( !empty( $this->telefono ) ) update_user_meta( $this->user_id, 'telefono', sanitize_text_field( $this->telefono ) );
            return true;
        }else{
            $this->error = $user_id;
            return false;
        }
        
        
    }
    
    public function agregar_embarcacion($id_embarcacion){
        global $wpdb;
        //todo: validar que el usuario no tenga la embarcción ya asignada
        
        $datos = [];
        $format = [];
        $datos["id_user"] = $this->user_id; $format[] = "%d";
        $datos["id_embarcacion"] = $id_embarcacion; $format[] = "%d";
        
        $this->user_id = $wpdb->insert($wpdb->prefix."ch_miembro_embarcacion", $datos, $format);
        return $this->user_id;
    }
    
    public function validar_alta(){
        /*
         * todo: 
         * Validar que el documento no exista ya que se usa como usuario
         * agregar en el alga de usuario una lista de miembros para asociar
         */
    }
    
    public function get_lista($filtro=[], $page=1){

        global $wpdb;

        $lista = $this->getUsuarios($filtro);
        $rta = [];
        foreach($lista as $l){
            $item["first_name"] = get_user_meta($l->ID ,"first_name", true);
            $item["last_name"] = get_user_meta($l->ID,"last_name",true);
            $item["dni"] = get_user_meta($l->ID,"dni",true);
            $item["telefono"] = get_user_meta($l->ID,"telefono",true);
            $item["user_id"] = $l->ID;
            $rta[] = $item;
        }
        
        return $rta;
    }

    private function getUsuarios($search_term = []){

        $meta_query = [];
        foreach ($search_term as $key => $value) {
            if( $key == "parametrosBusqueda" ){
                $a = explode(" ", $value);
                foreach( $a as $v ){

                   $meta_query = array(
                       array(
                           'key'     => 'first_name',
                           'value'   => $v,
                           'compare' => 'LIKE'
                       ),
                       array(
                           'key'     => 'last_name',
                           'value'   => $v,
                           'compare' => 'LIKE'
                       ),
                       array(
                           'key'     => 'dni',
                           'value'   => $v,
                           'compare' => '='
                       )

                   ); 
                }
                $meta_query["relation"] = "OR";
            }
        }
        $args = ["meta_query" => $meta_query];  


    // Create the WP_User_Query object
    $wp_user_query = new WP_User_Query( $args );

    // Get the results
    $rta = $wp_user_query->get_results();   
    return $rta;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

        
    public function getUser_id() {
        return $this->user_id;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }
    
    function getUser_login() {
        return $this->user_login;
    }

    function getUser_email() {
        return $this->user_email;
    }

    function getUser_pass() {
        return $this->user_pass;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getApellido() {
        return $this->apellido;
    }

    function getDni() {
        return $this->dni;
    }

    function setUser_login($user_login) {
        $this->user_login = $user_login;
    }

    function setUser_email($user_email) {
        $this->user_email = $user_email;
    }


    function setUser_pass($user_pass) {
        $this->user_pass = $user_pass;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setApellido($apellido) {
        $this->apellido = $apellido;
    }

    function setDni($dni) {
        $this->dni = $dni;
    }

    function getError(){
        var_dump($this->error);
        return $this->error;
    }
    
    protected function get_campo_id() {
        return "id";
    }

    protected function get_tabla() {
        global $wpdb;
        return $wpdb->prefix."users";
    }

    protected function get_option_editar() {
        return "usuario";
    }
}
