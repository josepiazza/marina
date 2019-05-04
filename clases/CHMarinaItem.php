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
        $sql = "SELECT p.fecha_pago, i.importe, tp.descripcion FROM ".$wpdb->prefix."ch_pago p
                INNER JOIN ".$wpdb->prefix."ch_pago_x_embarcacion i ON p.id = i.id_pago
                INNER JOIN ".$wpdb->prefix."ch_precio_embarcacion as m ON m.id = i.id_precio
                INNER JOIN ".$wpdb->prefix."ch_embarcaciones e ON e.id = m.id_embarcacion
                INNER JOIN ".$wpdb->prefix."ch_tipo_pago tp ON tp.id = p.tipo_pago
                WHERE e.id= $filtro";
//        print $sql;
        $rta = $wpdb->get_results($sql);
        return $rta;
    }

    public function get_tabla_html($filtro, $pagina = 1, $tdExtra=[]){
        $lista = $this->get_lista($filtro, $pagina = 1);
//        print_r($lista);
        $campoid= $this->get_campo_id();
        $rta = "<table class='wp-list-table widefat fixed striped posts'><tbody id='the-list'>";
        foreach( $lista as $row ){ 
            $rta .= "<tr>";
            foreach( $row as $k => $campo ){
                if( $k != $this->get_campo_id() ){
                    $rta .= "<td>  ".$campo."</td>";
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


