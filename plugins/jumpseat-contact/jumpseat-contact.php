<?php
/**
 * Plugin Name: JumpSeat Contact Manager
 * Description: Un plugin personalizado para gestionar los contactos de JumpSeat, creando una tabla MySQL al activarse.
 * Version: 1.0
 * Author: Tu Nombre/JumpSeat
 */

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Función que se ejecuta al activar el plugin.
 * Crea la tabla personalizada en la base de datos.
 */
function jumpseat_contact_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jumpseat_contacts';

    // Se requiere dbDelta para crear y actualizar tablas de forma segura
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $charset_collate = $wpdb->get_charset_collate();

    // Definición de la estructura de la tabla
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        title varchar(100),
        company varchar(100),
        message text NOT NULL,
        status varchar(20) DEFAULT 'pendiente' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Ejecutar la consulta SQL para crear la tabla
    dbDelta( $sql );
}

// Registrar la función de activación
register_activation_hook( __FILE__, 'jumpseat_contact_install' );

?>