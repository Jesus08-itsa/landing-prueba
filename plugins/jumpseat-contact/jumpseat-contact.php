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
 * Renderizar la tabla de contactos y manejar acciones CRUD 
 */ 
function jumpseat_contacts_page_html() { 
    if ( ! current_user_can( 'manage_options' ) ) return; 

    global $wpdb; 
    $table_name = $wpdb->prefix . 'jumpseat_contacts'; 

    // --- 1. PROCESAR GUARDADO DE EDICIÓN --- 
    if ( isset($_POST['jumpseat_update_contact']) && check_admin_referer('jumpseat_update_nonce') ) { 
        $id = intval($_POST['id']); 
        $wpdb->update( 
            $table_name, 
            array( 
                'name'      => sanitize_text_field($_POST['name']), 
                'last_name' => sanitize_text_field($_POST['last_name']), 
                'company'   => sanitize_text_field($_POST['company']), 
                'message'   => sanitize_textarea_field($_POST['message']) 
            ), 
            array('id' => $id) 
        ); 
        echo '<div class="notice notice-success is-dismissible"><p>Contacto editado y guardado correctamente.</p></div>'; 
    } 

    // --- 2. PROCESAR ACCIONES RÁPIDAS (Eliminar, Estados) --- 
    if ( isset($_GET['action']) && isset($_GET['id']) && isset($_GET['_wpnonce']) ) { 
        if ( wp_verify_nonce($_GET['_wpnonce'], 'jumpseat_action_nonce') ) { 
            $action = sanitize_text_field($_GET['action']); 
            $id = intval($_GET['id']); 

            if ( $action === 'delete' ) { 
                $wpdb->delete($table_name, array('id' => $id)); 
                echo '<div class="notice notice-success is-dismissible"><p>Contacto eliminado correctamente.</p></div>'; 
            } elseif ( $action === 'status_contactado' ) { 
                $wpdb->update($table_name, array('status' => 'contactado'), array('id' => $id)); 
                echo '<div class="notice notice-success is-dismissible"><p>Estado actualizado a: Contactado.</p></div>'; 
            } elseif ( $action === 'status_descartado' ) { 
                $wpdb->update($table_name, array('status' => 'descartado'), array('id' => $id)); 
                echo '<div class="notice notice-warning is-dismissible"><p>Estado actualizado a: Descartado.</p></div>'; 
            } elseif ( $action === 'status_pendiente' ) { 
                $wpdb->update($table_name, array('status' => 'pendiente'), array('id' => $id)); 
                echo '<div class="notice notice-info is-dismissible"><p>Estado revertido a: Pendiente.</p></div>'; 
            } 
        } 
    } 

    // --- 3. VISTA DE EDICIÓN --- 
    if ( isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) ) { 
        $id = intval($_GET['id']); 
        $contacto = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) ); 
        if($contacto): 
        ?> 
        <div class="wrap"> 
            <h1 class="wp-heading-inline">Editar Contacto</h1> 
            <a href="<?php echo admin_url('admin.php?page=jumpseat-contacts'); ?>" class="page-title-action">Volver a la lista</a> 
            <form method="post" action="<?php echo admin_url('admin.php?page=jumpseat-contacts'); ?>" style="margin-top: 20px; max-width: 600px;"> 
                <?php wp_nonce_field('jumpseat_update_nonce'); ?> 
                <input type="hidden" name="jumpseat_update_contact" value="1"> 
                <input type="hidden" name="id" value="<?php echo esc_attr($contacto->id); ?>"> 
                
                <table class="form-table"> 
                    <tr> 
                        <th><label for="name">Name</label></th> 
                        <td><input type="text" name="name" id="name" value="<?php echo esc_attr($contacto->name); ?>" class="regular-text"></td> 
                    </tr> 
                    <tr> 
                        <th><label for="last_name">Last Name</label></th> 
                        <td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($contacto->last_name); ?>" class="regular-text"></td> 
                    </tr> 
                    <tr> 
                        <th><label for="company">Company</label></th> 
                        <td><input type="text" name="company" id="company" value="<?php echo esc_attr($contacto->company); ?>" class="regular-text"></td> 
                    </tr> 
                    <tr> 
                        <th><label for="message">Message</label></th> 
                        <td><textarea name="message" id="message" rows="5" class="large-text"><?php echo esc_textarea($contacto->message); ?></textarea></td> 
                    </tr> 
                </table> 
                <p class="submit"> 
                    <input type="submit" class="button button-primary" value="Guardar Cambios"> 
                </p> 
            </form> 
        </div> 
        <?php 
        endif; 
        return; // Detenemos la ejecución aquí para no mostrar la tabla debajo 
    } 

    // --- 4. VISTA DE TABLA PRINCIPAL --- 
    $resultados = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" ); 
    ?> 
    <div class="wrap"> 
        <h1 class="wp-heading-inline">JumpSeat Inbox</h1> 
        <p>Gestiona los mensajes recibidos. Puedes editar, cambiar su estado o eliminarlos.</p> 
        
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;"> 
            <thead> 
                <tr> 
                    <th style="width: 5%;">ID</th> 
                    <th style="width: 15%;">Name</th> 
                    <th style="width: 15%;">Company</th> 
                    <th style="width: 20%;">Message</th> 
                    <th style="width: 10%;">Status</th> 
                    <th style="width: 10%;">Date</th> 
                    <th style="width: 25%;">Actions</th> 
                </tr> 
            </thead> 
            <tbody> 
                <?php if ( $resultados ) : ?> 
                    <?php foreach ( $resultados as $fila ) : 
                        // Generar URLs seguras con Nonce 
                        $base_url = admin_url('admin.php?page=jumpseat-contacts&id=' . $fila->id); 
                        $edit_url = $base_url . '&action=edit'; 
                        $delete_url = wp_nonce_url($base_url . '&action=delete', 'jumpseat_action_nonce'); 
                        $pendiente_url = wp_nonce_url($base_url . '&action=status_pendiente', 'jumpseat_action_nonce'); 
                        $contactado_url = wp_nonce_url($base_url . '&action=status_contactado', 'jumpseat_action_nonce'); 
                        $descartado_url = wp_nonce_url($base_url . '&action=status_descartado', 'jumpseat_action_nonce'); 
                        
                        // Colores de estado 
                        $status_color = '#ffba00'; // Pendiente 
                        if($fila->status == 'contactado') $status_color = '#46b450'; // Verde 
                        if($fila->status == 'descartado') $status_color = '#dc3232'; // Rojo 
                    ?> 
                        <tr> 
                            <td><?php echo esc_html( $fila->id ); ?></td> 
                            <td><strong><?php echo esc_html( $fila->name . ' ' . $fila->last_name ); ?></strong></td> 
                            <td><?php echo esc_html( $fila->company ); ?></td> 
                            <td><?php echo esc_html( $fila->message ); ?></td> 
                            <td> 
                                <span style="background: <?php echo $status_color; ?>; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;"> 
                                    <?php echo esc_html( strtoupper($fila->status) ); ?> 
                                </span> 
                            </td> 
                            <td><?php echo esc_html( date( 'M j, Y', strtotime($fila->created_at) ) ); ?></td> 
                            <td> 
                                <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">Editar</a> 
                                
                                <?php if($fila->status !== 'pendiente'): ?> 
                                    <a href="<?php echo esc_url($pendiente_url); ?>" class="button button-small" style="color: #ffba00; border-color: #ffba00;">Pendiente</a> 
                                <?php endif; ?> 
                                <?php if($fila->status !== 'contactado'): ?> 
                                    <a href="<?php echo esc_url($contactado_url); ?>" class="button button-small" style="color: #46b450; border-color: #46b450;">Contactado</a> 
                                <?php endif; ?> 
                                <?php if($fila->status !== 'descartado'): ?> 
                                    <a href="<?php echo esc_url($descartado_url); ?>" class="button button-small" style="color: #dc3232; border-color: #dc3232;">Descartado</a> 
                                <?php endif; ?> 
                                
                                <a href="<?php echo esc_url($delete_url); ?>" class="button button-small" onclick="return confirm('¿Eliminar registro de forma permanente?');">Eliminar</a> 
                            </td> 
                        </tr> 
                    <?php endforeach; ?> 
                <?php else : ?> 
                    <tr> 
                        <td colspan="7">No hay mensajes todavía.</td> 
                    </tr> 
                <?php endif; ?> 
            </tbody> 
        </table> 
    </div> 
    <?php 
}