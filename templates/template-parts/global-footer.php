<?php
/**
 * Global Footer template part.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="rmhp-footer" id="rmhp-footer">
    <div class="rmhp-container">
        <div class="rmhp-footer-grid">

            <?php /* Brand column */ ?>
            <div class="rmhp-footer-col rmhp-footer-brand">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rmhp-logo">
                    <?php
                    $logo_id = get_theme_mod( 'custom_logo' );
                    if ( $logo_id ) {
                        echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'rmhp-logo-img rmhp-footer-logo-img', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ] );
                    } else {
                        ?>
                        <span class="rmhp-logo-text"><?php bloginfo( 'name' ); ?></span>
                        <?php
                    }
                    ?>
                </a>
                <p class="rmhp-footer-tagline"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Bangladesh\'s leading reseller management platform.', 'reseller-management' ) ); ?></p>
                <div class="rmhp-footer-social">
                    <a href="https://www.facebook.com/" class="rmhp-social-link" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://twitter.com/" class="rmhp-social-link" aria-label="Twitter" target="_blank" rel="noopener noreferrer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
                    </a>
                </div>
            </div>

            <?php /* Quick links */ ?>
            <div class="rmhp-footer-col">
                <h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Quick Links', 'reseller-management' ); ?></h4>
                <ul class="rmhp-footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-home"       class="rmhp-scroll-link"><?php esc_html_e( 'Home', 'reseller-management' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-about"      class="rmhp-scroll-link"><?php esc_html_e( 'About Us', 'reseller-management' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-advantages" class="rmhp-scroll-link"><?php esc_html_e( 'Advantages', 'reseller-management' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-services"   class="rmhp-scroll-link"><?php esc_html_e( 'Services', 'reseller-management' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-faq"        class="rmhp-scroll-link"><?php esc_html_e( 'FAQ', 'reseller-management' ); ?></a></li>
                </ul>
            </div>

            <?php /* Contact */ ?>
            <div class="rmhp-footer-col">
                <h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Contact Us', 'reseller-management' ); ?></h4>
                <ul class="rmhp-footer-contact">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.15 3.4 2 2 0 0 1 3.12 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16l.92.92z"/></svg>
                        <?php echo esc_html( get_option( 'admin_email', '' ) ); ?>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <?php echo esc_html( get_option( 'admin_email', '' ) ); ?>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <?php echo esc_html( home_url() ); ?>
                    </li>
                </ul>
            </div>

            <?php /* Follow */ ?>
            <div class="rmhp-footer-col">
                <h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Follow Us', 'reseller-management' ); ?></h4>
                <div class="rmhp-footer-follow">
                    <a href="https://www.facebook.com/" class="rmhp-follow-btn rmhp-follow-fb" target="_blank" rel="noopener noreferrer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        Facebook
                    </a>
                </div>
            </div>

        </div>
    </div>

    <?php /* Copyright bar */ ?>
    <div class="rmhp-footer-bar">
        <div class="rmhp-container rmhp-footer-bar-inner">
            <span>
                <?php
                printf(
                    /* translators: 1: year, 2: site name */
                    esc_html__( '© %1$s %2$s. All rights reserved.', 'reseller-management' ),
                    esc_html( (string) gmdate( 'Y' ) ),
                    esc_html( get_bloginfo( 'name' ) )
                );
                ?>
            </span>
            <span class="rmhp-footer-dev">
                <?php esc_html_e( 'Developed by', 'reseller-management' ); ?>
                <a href="https://grocoder.net" target="_blank" rel="noopener noreferrer">Grocoder Software Solution</a>
            </span>
        </div>
    </div>
</footer>
