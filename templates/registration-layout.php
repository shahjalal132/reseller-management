<?php
/**
 * Registration layout template.
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
<body <?php body_class( 'rm-registration-body rmhp-body' ); ?>>
<?php wp_body_open(); ?>

<?php include __DIR__ . '/template-parts/global-header.php'; ?>

<main class="rmhp-main" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="rmhp-container">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</main>

<?php include __DIR__ . '/template-parts/global-footer.php'; ?>

<?php wp_footer(); ?>
</body>
</html>
