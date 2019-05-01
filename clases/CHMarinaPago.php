<?php
namespace ch_marina\marina\clases;


use ch_marina\marina\clases\CHMarinaCore;
/**
 * Description of CHMarinaPago
 *
 * @author chicho
 */
class CHMarinaPago extends CHMarinaCore {

    protected $fecha_alta;
    protected $fecha_pago;
    protected $monto;
    protected $tipo_pago;
    
    protected $items;
    
    public function getFecha_alta() {
        return $this->fecha_alta;
    }

    public function getFecha_pago() {
        return $this->fecha_pago;
    }

    public function getMonto() {
        return $this->monto;
    }

    public function getTipo_pago() {
        return $this->tipo_pago;
    }

    public function getItems() {
        global $wpdb;
        
        $sql = "SELECT e.nombre, i.importe FROM marina_ch_pago_x_embarcacion i
                INNER JOIN marina_ch_precio_embarcacion as m ON m.id = i.id_precio
                INNER JOIN marina_ch_embarcaciones e ON e.id = m.id_embarcacion
                WHERE i.id_pago = ".$this->id;
        
        return $wpdb->get_results( $sql );
    }

    public function setFecha_alta($fecha_alta) {
        $this->fecha_alta = $fecha_alta;
    }

    public function setFecha_pago($fecha_pago) {
        $this->fecha_pago = $fecha_pago;
    }

    public function setMonto($monto) {
        $this->monto = $monto;
    }

    public function setTipo_pago($tipo_pago) {
        $this->tipo_pago = $tipo_pago;
    }

    public function agregarItem($items) {
        $this->items[] = $items;
    }

    
    protected function get_campo_id() {
        return "id";
    }

    public function getTiposPago(){
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_tipo_pago";
        $rs = $wpdb->get_results( $sql); // $rs = $wpdb->get_results( $sql , ARRAY_A);
        return $rs;
    }
    
    public function get_lista($filtro, $pagina = 1) {
        global $wpdb;
        
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_pago";
//        as p
//                INNER JOIN ".$wpdb->prefix."ch_pago_x_embarcacion as i ON p.id = i.id_pago
//                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m ON m.id = i.id_precio
//                INNER JOIN ".$wpdb->prefix."ch_embarcaciones e ON e.id = m.id_embarcacion";
//        print $sql;
        return $wpdb->get_results( $sql );
    }

    protected function get_option_editar() {
        
    }

    protected function get_tabla() {
        global $wpdb;
        return "ch_pago";
    }

    public function get_tabla_html($filtro, $pagina = 1, $tdExtra=[]){
        $lista = $this->get_lista($filtro, $pagina = 1);

        $campoid= $this->get_campo_id();
        $rta = "<table class='wp-list-table widefat fixed striped posts'><tbody id='the-list'>";
        foreach( $lista as $row ){ 
            $rta .= "<tr>";
            foreach( $row as $k => $campo ){
                if( $k != $this->get_campo_id() ){
                    $rta .= "<td>  ".$campo."</td>";
                }
            }  
            
        $rta .= "<td style='width:80px'><a href='?page=ch_marina_menu_administrador&modo=pagos&modoPago=verPago&id=".$row->$campoid."'>Ver </a></td>";

        if( !empty($tdExtra) ){
            foreach( $tdExtra as $td ){
                $td = str_replace("%id", $row->$campoid, $td);
                $rta .= "<td style='width:80px'> $td </td>";
            }
        }
        $rta .= "</tr>";
//        $rta .= "<td style='width:80px'>Borrrar</td></tr>";
        }
        $rta .= "</tbody></table>";
        return $rta;
    }
    
    
    public function guardar(){
//        $this->validar_alta();
        global $wpdb;
        $datos = [];
        $format = [];

        if( !empty( $this->fecha_alta ) ){ $datos["fecha_alta"] = $this->fecha_alta; $format[] = "%s"; }
        if( !empty( $this->fecha_pago ) ){ $datos["fecha_pago"] = $this->fecha_pago; $format[] = "%s"; }
        if( !empty( $this->monto ) ){ $datos["monto"] = $this->monto; $format[] = "%d"; }
        if( !empty( $this->tipo_pago ) ){ $datos["tipo_pago"] = $this->tipo_pago; $format[] = "%d"; }

////        if( !empty( $this->estado ) ){ $datos["estado"] = $this->estado; $format[] = "%d"; }// int,
//        
//        if( !empty( $this->id ) ){
//            $where = ["id"=>$this->id];
//            $wpdb->update($wpdb->prefix.$this->get_tabla(), $datos, $where, $format);            
//        }else{
            $wpdb->insert($wpdb->prefix.$this->get_tabla(), $datos, $format);
            $this->id = $wpdb->insert_id;
            foreach( $this->items as $itemPago ){
                $sql = "SELECT id FROM ".$wpdb->prefix."ch_precio_embarcacion WHERE id_embarcacion = ".$itemPago->id." and hasta is null";
                $rta = $wpdb->get_results( $sql );
                
                $datoItem["id_pago"] = $this->id;
                $datoItem["id_precio"] = $rta[0]->id;
                $datoItem["importe"] = $itemPago->monto;
                $wpdb->insert($wpdb->prefix."ch_pago_x_embarcacion", $datoItem, ["%d","%d","%d"]);
            }
            
//        }
//
//        if( !empty( $this->estado  ) ){ $this->cambiarEstado( $this->estado ); };
//        
//        return $this->id;
    }
    
}
