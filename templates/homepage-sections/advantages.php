<?php
/**
 * Homepage – Advantages section.
 */
defined( 'ABSPATH' ) || exit;

$advantages = [
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'color' => 'teal',
        'title' => __( 'Nationwide Shipping', 'reseller-management' ),
        'desc'  => __( 'Deliver to your customer\'s doorstep within 2–5 business days, anywhere in the country.', 'reseller-management' ),
        'badge' => __( 'Express Delivery', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        'color' => 'amber',
        'title' => __( 'Secure Payment System', 'reseller-management' ),
        'desc'  => __( 'Encrypted SSL security for all transactions — cards, mobile banking & digital wallets.', 'reseller-management' ),
        'badge' => __( '100% Secure', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'color' => 'purple',
        'title' => __( '24/7 Customer Support', 'reseller-management' ),
        'desc'  => __( 'Our dedicated team is always ready to answer any question via phone, email & live chat.', 'reseller-management' ),
        'badge' => __( 'Always Available', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/></svg>',
        'color' => 'green',
        'title' => __( 'Easy Return Policy', 'reseller-management' ),
        'desc'  => __( 'Easy return & refund within 24 hours. No complex process, completely stress-free.', 'reseller-management' ),
        'badge' => __( 'No Hassle Return', 'reseller-management' ),
    ],
];
?>
<section class="rmhp-section rmhp-advantages" id="rmhp-advantages">
    <div class="rmhp-container">
        <div class="rmhp-section-header">
            <span class="rmhp-section-eyebrow"><?php esc_html_e( 'Why Choose Us', 'reseller-management' ); ?></span>
            <h2 class="rmhp-section-title">
                <?php esc_html_e( 'Our Special', 'reseller-management' ); ?>
                <span class="rmhp-text-accent"><?php esc_html_e( 'Advantages', 'reseller-management' ); ?></span>
            </h2>
            <p class="rmhp-section-sub"><?php esc_html_e( 'We are your preferred online shopping partner committed to trust and quality.', 'reseller-management' ); ?></p>
        </div>

        <div class="rmhp-adv-grid">
            <?php foreach ( $advantages as $adv ) : ?>
            <div class="rmhp-adv-card">
                <div class="rmhp-adv-icon rmhp-icon-bg-<?php echo esc_attr( $adv['color'] ); ?>">
                    <?php echo $adv['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <h3 class="rmhp-adv-title"><?php echo esc_html( $adv['title'] ); ?></h3>
                <p class="rmhp-adv-desc"><?php echo esc_html( $adv['desc'] ); ?></p>
                <span class="rmhp-adv-badge rmhp-badge-<?php echo esc_attr( $adv['color'] ); ?>"><?php echo esc_html( $adv['badge'] ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
