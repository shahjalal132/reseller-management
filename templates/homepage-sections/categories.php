<?php
/**
 * Homepage – Categories section.
 */
defined( 'ABSPATH' ) || exit;

$categories = [
    [ 'label' => __( "Men's Fashion", 'reseller-management' ),      'color' => 'teal' ],
    [ 'label' => __( "Women's Fashion", 'reseller-management' ),    'color' => 'pink' ],
    [ 'label' => __( 'Home & Lifestyle', 'reseller-management' ),   'color' => 'amber' ],
    [ 'label' => __( 'Gadgets & Electronics', 'reseller-management' ), 'color' => 'blue' ],
    [ 'label' => __( 'Winter Collection', 'reseller-management' ),  'color' => 'indigo' ],
    [ 'label' => __( 'Special Offers', 'reseller-management' ),     'color' => 'orange' ],
    [ 'label' => __( "Other's", 'reseller-management' ),            'color' => 'purple' ],
    [ 'label' => __( 'Foods', 'reseller-management' ),              'color' => 'green' ],
    [ 'label' => __( 'Watch', 'reseller-management' ),              'color' => 'teal' ],
    [ 'label' => __( 'Islamic Items', 'reseller-management' ),      'color' => 'amber' ],
    [ 'label' => __( "Kids' Zone", 'reseller-management' ),         'color' => 'pink' ],
    [ 'label' => __( 'Customize Item', 'reseller-management' ),     'color' => 'blue' ],
    [ 'label' => __( 'Customize & Gift', 'reseller-management' ),   'color' => 'orange' ],
    [ 'label' => __( 'Ready to Boost', 'reseller-management' ),     'color' => 'indigo' ],
];

$cat_icons = [
    'teal'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7.5V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v2.5"/><rect x="1" y="7" width="22" height="10" rx="1"/><path d="M12 7v10"/></svg>',
    'pink'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
    'amber'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'blue'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
    'indigo' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'orange' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    'purple' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    'green'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
];
?>
<section class="rmhp-section rmhp-categories" id="rmhp-categories">
    <div class="rmhp-container">
        <div class="rmhp-section-header">
            <span class="rmhp-section-eyebrow"><?php esc_html_e( 'Browse Products', 'reseller-management' ); ?></span>
            <h2 class="rmhp-section-title">
                <?php esc_html_e( 'All', 'reseller-management' ); ?>
                <span class="rmhp-text-accent"><?php esc_html_e( 'Categories', 'reseller-management' ); ?></span>
            </h2>
            <p class="rmhp-section-sub"><?php esc_html_e( 'Browse thousands of products across various categories', 'reseller-management' ); ?></p>
        </div>

        <div class="rmhp-cat-grid">
            <?php foreach ( $categories as $cat ) :
                $icon = $cat_icons[ $cat['color'] ] ?? $cat_icons['teal'];
            ?>
            <div class="rmhp-cat-item">
                <div class="rmhp-cat-icon rmhp-icon-bg-<?php echo esc_attr( $cat['color'] ); ?>">
                    <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <span class="rmhp-cat-label"><?php echo esc_html( $cat['label'] ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
