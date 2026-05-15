    <footer class="footer">
        <div class="footer__container">
            <div class="footer__col footer__col--logo">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__logo">LOGO</a>
            </div>
            
            <div class="footer__col footer__col--social">
                <a href="https://linkedin.com" class="footer__social" aria-label="LinkedIn" target="_blank" rel="noopener">
                    <?php 
                    $linkedin_icon = get_field('linkedin');
                    $icon_url = '';

                    if ($linkedin_icon) {
                        if (is_array($linkedin_icon)) {
                            $icon_url = $linkedin_icon['url'];
                        } elseif (is_numeric($linkedin_icon)) {
                            $icon_url = wp_get_attachment_url($linkedin_icon);
                        } else {
                            $icon_url = $linkedin_icon;
                        }
                    }

                    if ($icon_url): ?>
                        <img src="<?php echo esc_url($icon_url); ?>" alt="LinkedIn" class="footer__social-icon">
                    <?php else: ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    <?php endif; ?>
                </a>
            </div>

            <div class="footer__col footer__col--contact">
                <span class="footer__heading">CONTACT US</span>
                <address class="footer__address">
                    3116 W Cortland<br>
                    Chicago, IL 60647
                </address>
                <p class="footer__phone">630.870.2141</p>
                <a href="mailto:letsfly@web.com" class="footer__email">letsfly@web.com</a>
            </div>

            <div class="footer__col footer__col--nav">
                <a href="#services" class="footer__link">OUR SERVICES</a>
                <a href="#beliefs" class="footer__link">BELIEFS</a>
                <a href="#about" class="footer__link">ABOUT US</a>
                <a href="#contact" class="footer__link">JOIN US</a>
            </div>

            <div class="footer__col footer__col--stamps">
                <?php 
                $massive_graphic = get_field('svgmasive');
                $graphic_url = '';

                if ($massive_graphic) {
                    if (is_array($massive_graphic)) {
                        $graphic_url = $massive_graphic['url'];
                    } elseif (is_numeric($massive_graphic)) {
                        $graphic_url = wp_get_attachment_url($massive_graphic);
                    } elseif (is_string($massive_graphic)) {
                        $graphic_url = $massive_graphic;
                    }
                }

                if (!empty($graphic_url)): ?>
                    <img src="<?php echo esc_url($graphic_url); ?>" alt="Travel Stamps" class="footer__stamps-img">
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>