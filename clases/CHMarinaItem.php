<?php

namespace ch_marina\marina\clases;


use ch_marina\marina\clases\CHMarinaCore;

/**
 * Description of CHMarinaItem
 *
 * @author chicho
 */
class CHMarinaItem extends CHMarinaCore{
    protected function get_campo_id() {
        return "id";
    }

    public function get_lista($filtro, $pagina = 1) {
        global $wpdb;
        
        $where = "";
        foreach($filtro as $k=>$w){
            switch( $k ){
            case "id":
                $where .= " AND e.id = $w";
                break;
            case "tipo_pago":
                if( is_numeric( $w ) ){
                    $where .= " AND p.tipo_pago = $w";
                }else{
                    $where .= " AND p.tipo_pago $w ";
                }
                
                break;
            default:
                $where .= " AND $k = $w";
            }
        }
        
        $sql = "SELECT e.nombre, p.fecha_pago, month( p.fecha_desde ) as mes, i.importe, tp.descripcion, e.id as idEmbarcacion FROM ".$wpdb->prefix."ch_pago p
                INNER JOIN ".$wpdb->prefix."ch_pago_x_embarcacion i ON p.id = i.id_pago
                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m ON m.id = i.id_precio
                INNER JOIN ".$wpdb->prefix."ch_embarcaciones e ON e.id = m.id_embarcacion
                LEFT JOIN ".$wpdb->prefix."ch_tipo_pago tp ON tp.id = p.tipo_pago
                WHERE 1=1 $where
                ORDER BY p.fecha_desde DESC";
//        print $sql;
        $rta = $wpdb->get_results($sql);
//        var_dump($rta);
        return $rta;
    }

    public function get_tabla_html($filtro, $pagina = 1, $tdExtra=[]){
        $lista = $this->get_lista($filtro, $pagina = 1);
//        print_r($lista);
        $campoid= $this->get_campo_id();
                $rta = "<table class='wp-list-table widefat fixed striped posts'>"
                . "<thead>"
                . "<tr><td>Embarcación</td> <td>Fecha del pago</td> <td>Periodo válido</td> <td>Importe</td>  <td>Medio Pago</td></td></tr>"
                . "</thead>"
                . "<tbody id='the-list'>";
        foreach( $lista as $row ){ 
            if( empty($row->descripcion) ){
                $class = "class='rojo'";
                $row->descripcion = "Impago";
            }else{
                $class = "";
            }
            $rta .= "<tr $class>";
            foreach( $row as $k => $campo ){
                if( $k != $this->get_campo_id() ){
                    switch ($k){
                        case "idEmbarcacion":
                            break;
                        case "mes":
                            $meses = $this->getListadoMeses();
                            $rta .= "<td>  ".$meses[ $campo-1 ][1]."</td>";
                            break;
                        case "fecha_pago":
                            $mostrar = date("d/m/Y", strtotime( $campo ));
                            $rta .= "<td>  ".$mostrar."</td>";
                            break;
                        default:
                            $rta .= "<td>  ".$campo."</td>";
                    }
                }
            }  
            
//        $rta .= "<td style='width:80px'><a href='?page=ch_marina_menu_administrador&modo=edit&tbl=".$this->get_option_editar()."&id=".$row->$campoid."'>Editar </a></td>";

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
    
    protected function get_option_editar() {
        
    }

    protected function get_tabla() {
        
    }

}


