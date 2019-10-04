<?php
namespace ch_marina\marina\clases;


use ch_marina\marina\clases\CHMarinaCore;

/**
 * Description of CHMarinaPagoCorteMensual
 *
 * @author chicho
 */

class CHMarinaPagoCorteMensual extends CHMarinaCore{
    //put your code here
    protected function get_campo_id() {
        return "id";
    }

    public function get_lista($filtro, $pagina = 1) {
        global $wpdb;
        $sql = "select month(fecha_pago), year(fecha_pago), sum(monto) FROM marina.marina_ch_pago
                group by month(fecha_pago),year(fecha_pago)
                ORDER BY 2 DESC, 1 DESC";
        return $wpdb->get_results( $sql );
    }

    protected function get_option_editar() {
        return false;
    }

    protected function get_tabla() {
        return "";
    }

}

