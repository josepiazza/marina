<?php

namespace ch_marina\marina\clases;

/**
 * Description of CHMarinaEmbarcacion
 *
 * @author chicho
 */

use ch_marina\marina\clases\CHMarinaCore;
use ch_marina\marina\clases\CHMarinaUsuario;


class CHMarinaEmbarcacion extends CHMarinaCore {
    protected $id;
    protected $nombre;// varchar(100),
    protected $matricula;// varchar(100),
    protected $tipo;// int,
    protected $eslora;// varchar(20),
    protected $color;// varchar(20),
    protected $ubicacion;// varchar(120),
    protected $marca;// varchar(100),
    protected $estado;//int


    protected $tabla = "ch_embarcaciones";
    


    public function setEstado($estado) {
        
        $this->estado = $estado;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

        
    function getNombre() {
        return $this->nombre;
    }

    function getMatricula() {
        return $this->matricula;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getEslora() {
        return $this->eslora;
    }

    function getColor() {
        return $this->color;
    }

    function getUbicacion() {
        return $this->ubicacion;
    }

    function getMarca() {
        return $this->marca;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setMatricula($matricula) {
        $this->matricula = $matricula;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setEslora($eslora) {
        $this->eslora = $eslora;
    }

    function setColor($color) {
        $this->color = $color;
    }

    function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    function setMarca($marca) {
        $this->marca = $marca;
    }


    protected function get_campo_id() {
        return "id";
    }

    protected function get_option_editar(){
        return "embarcacion";
    }


    public function get_lista($filtro, $pagina = 1) {
        global $wpdb;
        
        $where = " WHERE 1=1  ";
        if(!empty($filtro["parametrosBusqueda"])){
            $valor = $filtro["parametrosBusqueda"];
            $where .= " AND (e.nombre like '%{$valor}%' OR e.matricula like '%{$valor}%' OR e.marca like '%{$valor}%')";
           
        }
        
        
        
        $sql = "SELECT e.id, nombre, matricula, t.descripcion as tipoBarco, es.descripcion FROM ".$wpdb->prefix."ch_embarcaciones e INNER JOIN "
                . "".$wpdb->prefix."ch_tipos_embarcacion t ON e.tipo = t.id LEFT JOIN "
                . "".$wpdb->prefix."ch_embarcacion_estado ee ON ee.id_embarcacion = e.id AND ee.fecha_hasta IS NULL LEFT JOIN "
                . "".$wpdb->prefix."ch_estados es ON ee.id_estado = es.id "
                . "$where";
//print $sql;
        $rs = $wpdb->get_results( $sql );
        return $rs;
    }

    protected function get_tabla() {
       return $this->tabla;
    }

    public function get_tipo_embarcacion() {
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_tipos_embarcacion";
        $rta = $wpdb->get_results($sql);
        return $rta;
    }
    
    public function get_tipos_estado(){
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_estados";
        $rta = $wpdb->get_results($sql);
        return $rta;        
    }
    
    public function getEstado() {
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_embarcacion_estado WHERE id_embarcacion = ".$this->id." AND fecha_hasta is null";
        $rs = $wpdb->get_results( $sql );
        if( !empty($rs) ){
            $this->estado = $rs[0]->id_estado;
        }else{
            $this->estado= null;
        }
        return $this->estado;
    }
    
    public function cambiarEstado( $id_estado ){
        global $wpdb;
        
        $estado = $this->getEstado();
        if( $estado != $id_estado ){
            $hoy = date("Y-m-d");
            if( !empty( $estado ) ){

                $updatePrepare = $wpdb->prepare("UPDATE ".$wpdb->prefix."ch_embarcacion_estado "
                        . "SET fecha_hasta = %s "
                        . "WHERE id_estado = %d AND id_embarcacion = %d AND fecha_hasta IS NULL", 
                        $hoy, $estado, $this->id);
                $uprta = $wpdb->query( $updatePrepare );
            }

            $datos = ["fecha_desde"=>$hoy,
                        "id_embarcacion"=>$this->id,
                        "id_estado"=>$id_estado];
            $format = ["%s"];
            $wpdb->insert($wpdb->prefix."ch_embarcacion_estado", $datos, $format);

        }
    }
    
    public function guardar(){
        $this->validar_alta();
        global $wpdb;
        $datos = [];
        $format = [];
//        if($this->)
        if( !empty( $this->nombre ) ){ $datos["nombre"] = $this->nombre; $format[] = "%s"; }// varchar(100),
        if( !empty( $this->matricula ) ){ $datos["matricula"] = $this->matricula; $format[] = "%s"; }// varchar(100),
        if( !empty( $this->tipo ) ){ $datos["tipo"] = $this->tipo; $format[] = "%d"; }// int,
        if( !empty( $this->eslora ) ){ $datos["eslora"] = $this->eslora; $format[] = "%s"; }// varchar(20),
        if( !empty( $this->color ) ){ $datos["color"] = $this->color; $format[] = "%s"; }// varchar(20),
        if( !empty( $this->ubicacion ) ){ $datos["ubicacion"] = $this->ubicacion; $format[] = "%s"; }// varchar(120),
        if( !empty( $this->marca) ) { $datos["marca"] = $this->marca; $format[] = "%s"; }// varchar(100),  
//        if( !empty( $this->estado ) ){ $datos["estado"] = $this->estado; $format[] = "%d"; }// int,
        
        if( !empty( $this->id ) ){
            $where = ["id"=>$this->id];
            $wpdb->update($wpdb->prefix.$this->get_tabla(), $datos, $where, $format);            
        }else{
            $wpdb->insert($wpdb->prefix.$this->get_tabla(), $datos, $format);
            $this->id = $wpdb->insert_id;
        }

        if( !empty( $this->estado  ) ){ $this->cambiarEstado( $this->estado ); };
        
        return $this->id;
    }
    
    public function agregarUsuario($user_id){
        $usu = new CHMarinaUsuario();
        $usu->setUser_id($user_id);
        $usu->agregar_embarcacion($this->id);
    }
    
    public function limpiarUsuarios(){
        $filtro = ["id_embarcacion" =>$this->id];
        global $wpdb;
        return $wpdb->delete($wpdb->prefix."ch_miembro_embarcacion", $filtro);
    }
    
    public function getListaUsuarios(){
        global $wpdb;
        $sql = "SELECT id_user FROM ".$wpdb->prefix."ch_miembro_embarcacion WHERE id_embarcacion = ".$this->getId();
        $rta = $wpdb->get_results($sql);
        foreach(array_keys($rta) as $k){
            $first_name = get_user_meta($rta[$k]->id_user ,"first_name", true);
            $last_name = get_user_meta($rta[$k]->id_user,"last_name",true);
            $rta[$k]->nombre = $last_name.", ".$first_name;
        }
//        var_dump($rta);
        return $rta;
    }
    
    public function get_tabla_html($filtro, $pagina = 1, $tdExtra=[]) {
        
        $parametrosBusqueda = (!empty($filtro["parametrosBusqueda"]))?$filtro["parametrosBusqueda"]:"";
      
        $rta=<<<FIL
                <form name="filtro" method="post" action="?page=ch_marina_menu_administrador&filtro=true">
   Clave Búsqueda: <input name="parametrosBusqueda" value="{$parametrosBusqueda}"> 
   <input type="submit" name="btn_filtrar" value="Filtrar"><input name="btn_limpiar" type="submit" value="limpiar">

   </form><hr/>
FIL;
        $tdExtra[] = "<a href='?page=ch_marina_menu_administrador&modo=embarcacionPagos&id=%id'>Pagos</a>";
        
        return $rta.parent::get_tabla_html($filtro, $pagina, $tdExtra);
    }
    
    public function getPrecio(){
        global $wpdb;
        $sql = "SELECT precio FROM ".$wpdb->prefix."ch_precio_embarcacion WHERE id_embarcacion = ".$this->id." AND hasta is null";
        $rta = $wpdb->get_results($sql);
        if( !empty($rta) ){
            return $rta[0]->precio;
        }else{
            return 0;
        }
    }
    
    public function agregarPrecio($valor){
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_precio_embarcacion WHERE id_embarcacion = ".$this->id." AND hasta is null "; 
        $rta = $wpdb->get_results($sql);
        if( $rta[0]->precio != $valor ){
            $hoy = date("Y-m-d");
            print $rta[0]->desde." == ".$hoy; 
            if( $rta[0]->desde == 1 ){
                $updatePrepare = $wpdb->prepare("UPDATE ".$wpdb->prefix."ch_precio_embarcacion "
                        . "SET precio = %d "
                        . "WHERE id_embarcacion = %d AND hasta IS NULL", 
                        $valor, $this->id);
                $uprta = $wpdb->query( $updatePrepare );
            }else{
                $updatePrepare = $wpdb->prepare("UPDATE ".$wpdb->prefix."ch_precio_embarcacion "
                        . "SET hasta = %s "
                        . "WHERE id_embarcacion = %d AND  hasta IS NULL", 
                        $hoy, $this->id);
                $uprta = $wpdb->query( $updatePrepare );
                $datos = ["desde"=>$hoy,
                            "id_embarcacion"=>$this->id,
                            "precio"=>$valor];
                $format = ["%s", "%d", "%d"];
                $wpdb->insert($wpdb->prefix."ch_precio_embarcacion", $datos, $format);
            }
        }else{
            return 0;
        }
        
    }
    
    public function getTablaDeuda(){
        
    }
    
    protected function validar_alta(){
        //valido que tenga tipo de embarcacion
        // que la matrícula no exista
        // que tenga dueño
        
    }
}
