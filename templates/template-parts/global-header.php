<?php
/**
 * Global Header template part.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="rmhp-header" id="rmhp-header">
    <div class="rmhp-container rmhp-header-inner">

        <?php /* Logo */ ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rmhp-logo">
            <?php
            $logo_id = get_theme_mod( 'custom_logo' );
            if ( $logo_id ) {
                echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'rmhp-logo-img', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ] );
            } else {
                ?>
                <span class="rmhp-logo-text"><?php bloginfo( 'name' ); ?></span>
                <?php
            }
            ?>
        </a>

        <?php /* Navigation */ ?>
        <nav class="rmhp-nav" id="rmhp-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'reseller-management' ); ?>">
            <ul class="rmhp-nav-list">
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-home"       class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'Home', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-about"      class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'About Us', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-advantages" class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'Advantages', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-categories" class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'Categories', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-company"    class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'Company', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-services"   class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'Services', 'reseller-management' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-faq"        class="rmhp-nav-link rmhp-scroll-link"><?php esc_html_e( 'FAQ', 'reseller-management' ); ?></a></li>
                <?php if ( ! is_user_logged_in() ) : ?>
                    <li class="rmhp-nav-cta-item"><a href="<?php echo esc_url( wp_login_url() ); ?>" class="rmhp-nav-link"><?php esc_html_e( 'Login', 'reseller-management' ); ?></a></li>
                    <li class="rmhp-nav-cta-item"><a href="<?php echo esc_url( home_url( '/reseller-registration/' ) ); ?>" class="rmhp-nav-link rmhp-nav-link-primary"><?php esc_html_e( 'Register', 'reseller-management' ); ?></a></li>
                <?php else : ?>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <li class="rmhp-nav-cta-item"><a href="<?php echo esc_url( admin_url() ); ?>" class="rmhp-nav-link"><?php esc_html_e( 'Admin Dashboard', 'reseller-management' ); ?></a></li>
                    <?php else : ?>
                        <li class="rmhp-nav-cta-item"><a href="<?php echo esc_url( home_url( '/reseller-dashboard/' ) ); ?>" class="rmhp-nav-link"><?php esc_html_e( 'Dashboard', 'reseller-management' ); ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>

        <?php /* CTA buttons */ ?>
        <div class="rmhp-header-cta">
            <?php if ( ! is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wp_login_url() ); ?>" class="rmhp-btn rmhp-btn-ghost">
                    <?php esc_html_e( 'Login', 'reseller-management' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/reseller-registration/' ) ); ?>" class="rmhp-btn rmhp-btn-primary">
                    <?php esc_html_e( 'Register', 'reseller-management' ); ?>
                </a>
            <?php else : ?>
                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <a href="<?php echo esc_url( admin_url() ); ?>" class="rmhp-btn rmhp-btn-ghost">
                        <?php esc_html_e( 'Admin Dashboard', 'reseller-management' ); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/reseller-dashboard/' ) ); ?>" class="rmhp-btn rmhp-btn-ghost">
                        <?php esc_html_e( 'Dashboard', 'reseller-management' ); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php /* Mobile hamburger */ ?>
        <button class="rmhp-hamburger" id="rmhp-hamburger" aria-label="<?php esc_attr_e( 'Toggle menu', 'reseller-management' ); ?>">
            <span></span><span></span><span></span>
        </button>

    </div>
</header>
