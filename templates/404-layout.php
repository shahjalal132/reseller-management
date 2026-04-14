<?php
/**
 * Custom 404 Page layout template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'rm-404-body' ); ?>>
<?php wp_body_open(); ?>

<?php include __DIR__ . '/template-parts/global-header.php'; ?>

<main class="rm-404-main">
    <div class="rmhp-container">
        <div class="rm-404-content">
            <div class="rm-404-visual">
                <h1 class="rm-404-title">404</h1>
                <div class="rm-404-blob"></div>
                <div class="rm-404-blob-alt"></div>
            </div>
            
            <div class="rm-404-text-card">
                <h2><?php esc_html_e( 'Oops! Page Not Found', 'reseller-management' ); ?></h2>
                <p><?php esc_html_e( 'It seems the page you are looking for has been moved or doesn\'t exist. No worries, we\'ll help you find your way back.', 'reseller-management' ); ?></p>
                
                <div class="rm-404-actions">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rmhp-btn rmhp-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <?php esc_html_e( 'Back to Home', 'reseller-management' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/template-parts/global-footer.php'; ?>

<?php wp_footer(); ?>
</body>
</html>
