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

/** 
 * Inyectar variables AJAX directamente en el head 
 */ 
function jumpseat_contact_ajax_variables() { 
    ?> 
    <script type="text/javascript" > 
        var  jumpseatAjax = { 
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>' , 
            nonce: '<?php echo wp_create_nonce( 'jumpseat_contact_nonce' ); ?>' 
        }; 
    </script> 
    <?php 
} 
add_action( 'wp_head', 'jumpseat_contact_ajax_variables'  ); 

/**
 * Procesar y guardar los datos del formulario
 */
function jumpseat_handle_form_submission() {
    // Verificar seguridad
    check_ajax_referer( 'jumpseat_contact_nonce', 'security' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'jumpseat_contacts';

    // Sanitizar los datos recibidos
    $name      = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    // Mapeamos 'lastName' del HTML a 'last_name' de la BD
    $last_name = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $title     = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $company   = isset($_POST['company']) ? sanitize_text_field($_POST['company']) : '';
    $message   = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

    // Validación básica en backend
    if ( empty($name) || empty($last_name) || empty($message) ) {
        wp_send_json_error( 'Please fill out all required fields.' );
    }

    // Insertar en la tabla
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'name'       => $name,
            'last_name'  => $last_name,
            'title'      => $title,
            'company'    => $company,
            'message'    => $message,
            'status'     => 'pendiente'
        )
    );

    if ( $inserted ) {
        wp_send_json_success( 'Message sent successfully!' );
    } else {
        wp_send_json_error( 'Database error. Could not save message.' );
    }
}
add_action( 'wp_ajax_jumpseat_submit_form', 'jumpseat_handle_form_submission' );
add_action( 'wp_ajax_nopriv_jumpseat_submit_form', 'jumpseat_handle_form_submission' );

/** 
 * Crear el menú en el panel de administración 
 */ 
function jumpseat_contact_admin_menu() { 
    add_menu_page( 
        'JumpSeat Contacts',       // Título de la página 
        'Contacts (JumpSeat)',     // Título del menú lateral 
        'manage_options',          // Permiso requerido (Solo administradores) 
        'jumpseat-contacts',       // Slug 
        'jumpseat_contacts_page_html', // Función de renderizado 
        'dashicons-email-alt',     // Icono de sobre 
        25                         // Posición en el menú 
    ); 
} 
add_action( 'admin_menu', 'jumpseat_contact_admin_menu' ); 

/** 
 * Renderizar la tabla de contactos 
 */ 
function jumpseat_contacts_page_html() { 
    // Verificar seguridad 
    if ( ! current_user_can( 'manage_options' ) ) { 
        return; 
    } 

    global $wpdb; 
    $table_name = $wpdb->prefix . 'jumpseat_contacts'; 

    // Consultar los datos ordenados por los más recientes 
    $resultados = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" ); 
    ?> 
    <div class="wrap"> 
        <h1 class="wp-heading-inline">JumpSeat Inbox</h1> 
        <p>Aquí puedes revisar todos los mensajes recibidos desde la Landing Page.</p> 
        
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;"> 
            <thead> 
                <tr> 
                    <th style="width: 5%;">ID</th> 
                    <th style="width: 15%;">Name</th> 
                    <th style="width: 15%;">Last Name</th> 
                    <th style="width: 10%;">Title</th> 
                    <th style="width: 15%;">Company</th> 
                    <th style="width: 20%;">Message</th> 
                    <th style="width: 10%;">Status</th> 
                    <th style="width: 10%;">Date</th> 
                </tr> 
            </thead> 
            <tbody> 
                <?php if ( $resultados ) : ?> 
                    <?php foreach ( $resultados as $fila ) : ?> 
                        <tr> 
                            <td><?php echo esc_html( $fila->id ); ?></td> 
                            <td><strong><?php echo esc_html( $fila->name ); ?></strong></td> 
                            <td><?php echo esc_html( $fila->last_name ); ?></td> 
                            <td><?php echo esc_html( $fila->title ); ?></td> 
                            <td><?php echo esc_html( $fila->company ); ?></td> 
                            <td><?php echo esc_html( $fila->message ); ?></td> 
                            <td> 
                                <span style="background: #ffba00; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;"> 
                                    <?php echo esc_html( strtoupper($fila->status) ); ?> 
                                </span> 
                            </td> 
                            <td><?php echo esc_html( date( 'M j, Y', strtotime($fila->created_at) ) ); ?></td> 
                        </tr> 
                    <?php endforeach; ?> 
                <?php else : ?> 
                    <tr> 
                        <td colspan="8">No hay mensajes todavía.</td> 
                    </tr> 
                <?php endif; ?> 
            </tbody> 
        </table> 
    </div> 
    <?php 
}