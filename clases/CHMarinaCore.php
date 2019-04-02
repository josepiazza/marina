<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ch_marina\marina\clases;

/**
 * Description of CHMarinaCore
 *
 * @author chicho
 */
abstract class CHMarinaCore {
    //put your code here
    abstract protected function get_tabla();
    abstract protected function get_campo_id();
    abstract protected function get_option_editar();

        abstract public function get_lista( $filtro, $pagina = 1 );
    
    
    public function get_tabla_html($filtro, $pagina = 1){
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
            
        $rta .= "<td style='width:80px'><a href='?page=ch_marina_menu_administrador&modo=edit&tbl=".$this->get_option_editar()."&id=".$row->$campoid."'>Editar </a></td>";
//        $rta .= "<td style='width:80px'>Borrrar</td></tr>";
        }
        $rta .= "</tbody></table>";
        return $rta;
    }
    
    public function inicializar($id){
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix.$this->get_tabla()." WHERE ".$this->get_campo_id()." = ".$id;
        $rs = $wpdb->get_results( $sql , ARRAY_A);
        
        $class = get_class( $this );
        
//        $prop = $this->getPropiedades();
        foreach( $rs[0] as $p=>$v ){
            if(property_exists($class, $p) ){
                $this->$p = $v;
            }
        }
    }
    
    public function borrar($filtro){
        global $wpdb;
        return $wpdb->delete($wpdb->prefix.$this->get_tabla(), $filtro);
        
    }
    
    public function getPropiedades(){
        return get_object_vars($this);
    }
}
