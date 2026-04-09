<?php
/**
 * Homepage – Services section.
 */
defined( 'ABSPATH' ) || exit;

$services = [
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        'color' => 'teal',
        'title' => __( 'Sales-Ready Content', 'reseller-management' ),
        'desc'  => __( 'We prepare Ready-to-Sell content for dropshippers — product descriptions, images and marketing materials.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>',
        'color' => 'blue',
        'title' => __( 'Product Update Collection', 'reseller-management' ),
        'desc'  => __( 'We constantly work on new products to keep collection up-to-date for maximum sales.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'color' => 'green',
        'title' => __( 'High Profit Margin', 'reseller-management' ),
        'desc'  => __( 'All products are directly sourced and factory-priced, so you can sell for very good profit.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'color' => 'amber',
        'title' => __( 'Cash on Delivery', 'reseller-management' ),
        'desc'  => __( 'Resellers can place orders with no advance payment. Take full advance or partial payment from customers.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        'color' => 'orange',
        'title' => __( 'Hot Selling Products', 'reseller-management' ),
        'desc'  => __( 'The best-selling products and marketing content to help you sell them effectively.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'color' => 'purple',
        'title' => __( 'Dedicated Support Team', 'reseller-management' ),
        'desc'  => __( 'The most important thing for a dropshipper is getting quick answers before buying — our team is here.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'color' => 'indigo',
        'title' => __( 'Stock Update Alert', 'reseller-management' ),
        'desc'  => __( 'We always keep products live. When a product is out of stock you get an instant update.', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
        'color' => 'pink',
        'title' => __( 'Product Selling Guideline', 'reseller-management' ),
        'desc'  => __( 'We support product selling guidelines with each product to maximize out-of-stock sales.', 'reseller-management' ),
    ],
];
?>
<section class="rmhp-section rmhp-services" id="rmhp-services">
    <div class="rmhp-container">
        <div class="rmhp-section-header">
            <span class="rmhp-section-eyebrow"><?php esc_html_e( 'What We Offer', 'reseller-management' ); ?></span>
            <h2 class="rmhp-section-title">
                <?php esc_html_e( 'Our', 'reseller-management' ); ?>
                <span class="rmhp-text-accent"><?php esc_html_e( 'Services', 'reseller-management' ); ?></span>
            </h2>
            <p class="rmhp-section-sub"><?php esc_html_e( 'Everything we offer to grow your reseller business', 'reseller-management' ); ?></p>
        </div>

        <div class="rmhp-services-grid">
            <?php foreach ( $services as $svc ) : ?>
            <div class="rmhp-svc-card">
                <div class="rmhp-svc-icon rmhp-icon-bg-<?php echo esc_attr( $svc['color'] ); ?>">
                    <?php echo $svc['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <h3 class="rmhp-svc-title"><?php echo esc_html( $svc['title'] ); ?></h3>
                <p class="rmhp-svc-desc"><?php echo esc_html( $svc['desc'] ); ?></p>
                <a href="#rmhp-home" class="rmhp-svc-link rmhp-scroll-link"><?php esc_html_e( 'Learn more', 'reseller-management' ); ?> &rarr;</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
