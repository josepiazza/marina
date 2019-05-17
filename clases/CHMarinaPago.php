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
    protected $fecha_desde;
    protected $fecha_hasta;
    protected $identificador_pago;
    
    public function getIdentificador_pago() {
        return $this->identificador_pago;
    }

    public function setIdentificador_pago($identificador_pago) {
        $this->identificador_pago = $identificador_pago;
    }

        
    public function getFecha_desde() {
        return $this->fecha_desde;
    }

    public function getFecha_hasta() {
        return $this->fecha_hasta;
    }

    public function setFecha_desde($fecha_desde) {
        $this->fecha_desde = $fecha_desde;
    }

    public function setFecha_hasta($fecha_hasta) {
        $this->fecha_hasta = $fecha_hasta;
    }

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
        
        $sql = "SELECT e.nombre, i.importe FROM ".$wpdb->prefix."ch_pago_x_embarcacion i
                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m ON m.id = i.id_precio
                INNER JOIN ".$wpdb->prefix."ch_embarcaciones e ON e.id = m.id_embarcacion
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
    
    public function get_lista($filtro=[], $pagina = 1) {
        global $wpdb;
        $where = " WHERE 1=1 ";
        foreach($filtro as $k=>$w){
            switch( $k ){
            case "tipo_pago":
                if( is_numeric( $w ) ){
                    $where .= " AND tipo_pago = $w";
                }else{
                    $where .= " AND tipo_pago $w ";
                }
                
                break;
            default:
                $where .= " AND $k = $w";
            }
        }
        
        $sql = "SELECT * FROM ".$wpdb->prefix."ch_pago $where ORDER BY fecha_desde DESC";
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
        
        return "ch_pago";
    }

    public function get_tabla_html($filtro, $pagina = 1, $tdExtra=[]){
        $lista = $this->get_lista($filtro, $pagina = 1);

        $campoid= $this->get_campo_id();
        $rta = "<table class='wp-list-table widefat fixed striped posts'>"
                . "<thead>"
                . "<tr><td>Alta Pago</td>  <td>Fecha del pago</td>  <td>Importe</td>  <td>Medio Pago</td>  <td>Valido Desde</td>  <td>Valido Hasta</td> <td></td></tr>"
                . "</thead>"
                . "<tbody id='the-list'>";
        foreach( $lista as $row ){ 
            $rta .= "<tr>";
            foreach( $row as $k => $campo ){
                if( $k != $this->get_campo_id() ){
                    switch ($k){
                        case "fecha_alta":
                        case "fecha_pago":
                        case "fecha_desde":
                        case "fecha_hasta":
                            $mostrar = date("d/m/Y", strtotime( $campo ));
                            $rta .= "<td>  ".$mostrar."</td>";
                            break;
                        default:
                            $rta .= "<td>  ".$campo."</td>";
                    }
                    
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
        
        
        if( !empty( $this->identificador_pago ) ){ $datos["identificador_pago"] = $this->identificador_pago; $format[] = "%s"; }
        
        
        if( !empty( $this->fecha_desde ) ){ $datos["fecha_desde"] = $this->fecha_desde; $format[] = "%s"; }
        if( !empty( $this->fecha_hasta ) ){ $datos["fecha_hasta"] = $this->fecha_hasta; $format[] = "%s"; }

////        if( !empty( $this->estado ) ){ $datos["estado"] = $this->estado; $format[] = "%d"; }// int,
//        
//        if( !empty( $this->id ) ){
//            $where = ["id"=>$this->id];
//            $wpdb->update($wpdb->prefix.$this->get_tabla(), $datos, $where, $format);            
//        }else{
            $wpdb->insert($wpdb->prefix.$this->get_tabla(), $datos, $format);
            $this->id = $wpdb->insert_id;
            $mesPago = date("m", strtotime( $this->fecha_desde ));
            $anioPago = date("Y", strtotime( $this->fecha_desde ));
            foreach( $this->items as $itemPago ){
                $sql = "SELECT id FROM ".$wpdb->prefix."ch_precio_embarcacion WHERE id_embarcacion = ".$itemPago->id." and hasta is null";
                $rta = $wpdb->get_results( $sql );
                
                $datoItem["id_pago"] = $this->id;
                $datoItem["id_precio"] = $rta[0]->id;
                $datoItem["importe"] = $itemPago->monto;
                $wpdb->insert($wpdb->prefix."ch_pago_x_embarcacion", $datoItem, ["%d","%d","%d"]);
                
                $this->borrarDeuda($itemPago->id, $mesPago, $anioPago);
               
            }
            
            
            
//        }
//
//        if( !empty( $this->estado  ) ){ $this->cambiarEstado( $this->estado ); };
//        
        return $this->id;
    }
    
        
    public function crearCuotas($mes,$anio){
        global $wpdb;
        $sql = "SELECT e.id, m.precio
                FROM ".$wpdb->prefix."ch_embarcaciones e 
                INNER JOIN ".$wpdb->prefix."ch_embarcacion_estado ee ON e.id = ee.id_embarcacion AND ee.fecha_hasta is null
                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m  ON e.id = m.id_embarcacion
                LEFT JOIN (".$wpdb->prefix."ch_pago_x_embarcacion as i 
                INNER JOIN ".$wpdb->prefix."ch_pago as p ON p.id = i.id_pago ) ON m.id = i.id_precio
                AND month( p.fecha_hasta ) = $mes AND year( p.fecha_hasta ) = $anio
                WHERE m.hasta is null AND p.fecha_hasta is null";
        $lista = $wpdb->get_results( $sql );
        
        $my_date = new \DateTime();
        $nmes = date("F", strtotime($anio."/".$mes."/1") );

        $my_date->modify('first day of '.$nmes.' '.$_REQUEST["anio"]);
        $primerDia = $my_date->format('Y/m/d');

        $my_date->modify('last day of '.$nmes.' '.$_REQUEST["anio"]);
        $ultimoDia = $my_date->format('Y/m/d');
        
        foreach($lista as $item){
            $this->generarDeuda($item->id, $item->precio, $primerDia, $ultimoDia);
        }
    }
    
    private function generarDeuda($idEmbarcacion, $monto, $primerDia, $ultimoDia){
        $pago = new CHMarinaPago();
        $pago->agregarItem( json_decode('{"id": '.$idEmbarcacion.', "monto":'.$monto.'}') );
        $pago->setFecha_desde($primerDia);
        $pago->setFecha_hasta($ultimoDia);
        $pago->setFecha_alta(date("Y-m-d"));
        $pago->setFecha_pago( $primerDia );
        $pago->setMonto($monto);
        $id = $pago->guardar();
    }
    
    private function borrarDeuda( $idEmbarcacion, $mes, $anio ){
        global $wpdb;
        $sql = "SELECT e.id as idEbarcacion, p.id as idPago
                FROM ".$wpdb->prefix."ch_pago p 
                INNER JOIN ".$wpdb->prefix."ch_pago_x_embarcacion i ON p.id = i.id_pago 
                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m ON m.id = i.id_precio 
                INNER JOIN ".$wpdb->prefix."ch_embarcaciones e ON e.id = m.id_embarcacion 
                WHERE p.tipo_pago is null 
                AND month( p.fecha_hasta ) = $mes
                AND year( p.fecha_hasta ) = $anio
                AND e.id = $idEmbarcacion ";
        
        $lista = $wpdb->get_results( $sql );
        
        $wpdb->delete($wpdb->prefix."ch_pago_x_embarcacion", [ "id_pago"=>$lista[0]->idPago ]);
        $wpdb->delete($wpdb->prefix."ch_pago", ["id"=>$lista[0]->idPago ] );
    }
    
}
