<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.svg" type="image/svg+xml">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Header -->
    <header class="header">
        <div class="header__container">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo">LOGO</a>
            <nav class="header__nav">
                <a href="#services" class="header__link">OUR SERVICES</a>
                <a href="#beliefs" class="header__link">BELIEFS</a>
                <a href="#case-studies" class="header__link">CASE STUDIES</a>
                <a href="#about" class="header__link">ABOUT US</a>
                <a href="#contact" class="header__link">JOIN US</a>
            </nav>
            <button class="header__menu-btn" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
