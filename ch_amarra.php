<?php
/*
Plugin Name: CH_Amarras
Administrador de amarras y pagoscorrespondientes
Author: José Piazza
*/
use ch_marina\marina\CHMarinaInicio;
include "ch_marina_include.php";

$r = new CHMarinaInicio();



function crearEstructuraDeDatos(){

    global $wpdb;
    add_option( 'ch_amarra_version_db', '0.0.1' );
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );



$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_tipos_embarcacion(
id int NOT NULL AUTO_INCREMENT,
descripcion varchar(100),
UNIQUE KEY id (id)
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_tipo_pago(
id int NOT NULL AUTO_INCREMENT,
descripcion varchar(100),
UNIQUE KEY id (id)
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_embarcaciones(
id int NOT NULL AUTO_INCREMENT,
nombre varchar(100),
matricula varchar(100),
tipo int,
eslora varchar(20),
color varchar(20),
ubicacion varchar(120),
marcha varchar(100),
UNIQUE KEY id (id),
FOREIGN KEY (tipo) REFERENCES ".$wpdb->prefix."ch_tipos_embarcacion(id) ON DELETE RESTRICT
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_miembro_embarcacion(
id_user int,
id_embarcacion int,
FOREIGN KEY (id_embarcacion) REFERENCES ".$wpdb->prefix."ch_embarcaciones(id) ON DELETE RESTRICT
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_precio_embarcacion(
id int NOT NULL AUTO_INCREMENT,
id_embarcacion int,
precio DECIMAL(7,2),
hasta date,
UNIQUE KEY id (id),
FOREIGN KEY (id_embarcacion) REFERENCES ".$wpdb->prefix."ch_embarcaciones(id) ON DELETE RESTRICT
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_pago(
id int NOT NULL AUTO_INCREMENT,
fecha_alta date,
fecha_pago date,
monto decimal(7,2),
tipo_pago int,
UNIQUE KEY id (id),
FOREIGN KEY (tipo_pago) REFERENCES ".$wpdb->prefix."ch_tipo_pago(id) ON DELETE RESTRICT
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_miembro_embarcacion(
id_pago int,
id_embarcacion int,
FOREIGN KEY (id_embarcacion) REFERENCES ".$wpdb->prefix."ch_embarcaciones(id) ON DELETE RESTRICT,
FOREIGN KEY (id_pago) REFERENCES ".$wpdb->prefix."ch_pago(id) ON DELETE RESTRICT
);";
    dbDelta( $sql );

    
$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_estados(
id int NOT NULL AUTO_INCREMENT,
descripcion varchar(100),
UNIQUE KEY id (id)
);";
    dbDelta( $sql );

$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."ch_embarcacion_estado(
id_estado int,
id_embarcacion int,
fecha_desde date,
fecha_hasta date
);";
    dbDelta( $sql );


    
}

function cargarDatosIniciales(){
//    global $wpdb;
//    $wpdb->insert( "".$wpdb->prefix."ch_tipos_embarcacion", ["descripcion"=> "Nivel 1"]);
    	
}

register_activation_hook( __FILE__, 'crearEstructuraDeDatos' );