<?php
/**
 * JumpSeat Theme functions and definitions
 */

if ( ! function_exists( 'jumpseat_setup' ) ) :
    function jumpseat_setup() {
        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        // Let WordPress manage the document title.
        add_theme_support( 'title-tag' );

        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(
            array(
                'menu-1' => esc_html__( 'Primary', 'jumpseat' ),
            )
        );

        // Switch default core markup for search form, comment form, and comments to output valid HTML5.
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
            )
        );
    }
endif;
add_action( 'after_setup_theme', 'jumpseat_setup' );

/**
 * Enqueue scripts and styles.
 */
function jumpseat_scripts() {
    // Enqueue Google Fonts
    wp_enqueue_style( 'google-fonts-jakarta', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap', array(), null );

    // Enqueue Main Style
    wp_enqueue_style( 'jumpseat-style', get_stylesheet_uri(), array(), '1.0.0' );

    // Enqueue Main JavaScript
    wp_enqueue_script( 'jumpseat-main', get_template_directory_uri() . '/main.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'jumpseat_scripts' );
