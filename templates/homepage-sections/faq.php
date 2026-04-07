<?php
/**
 * Homepage – FAQ section.
 */
defined( 'ABSPATH' ) || exit;

$faqs = [
    [
        'q' => __( 'How do I start as a reseller?', 'reseller-management' ),
        'a' => __( 'Simply fill out the registration form on our platform. After admin approval your account is activated and you can start placing orders immediately.', 'reseller-management' ),
    ],
    [
        'q' => __( 'Is there any registration fee?', 'reseller-management' ),
        'a' => __( 'No. Registration is completely free. There is zero investment required to join as a reseller.', 'reseller-management' ),
    ],
    [
        'q' => __( 'How do I place an order?', 'reseller-management' ),
        'a' => __( 'After logging in, go to your dashboard, browse products, and place an order with customer details. We handle packaging and delivery.', 'reseller-management' ),
    ],
    [
        'q' => __( 'When will I receive my profit/earnings?', 'reseller-management' ),
        'a' => __( 'Your profit is added to your account balance after each successful order delivery. You can withdraw anytime from the Account section.', 'reseller-management' ),
    ],
    [
        'q' => __( 'What payment methods are supported for withdrawal?', 'reseller-management' ),
        'a' => __( 'We support bKash, Nagad, Rocket, and bank transfer. You can manage your payment methods from the dashboard.', 'reseller-management' ),
    ],
    [
        'q' => __( 'What happens if an order is returned?', 'reseller-management' ),
        'a' => __( 'Returns are handled within 24 hours. If approved, any profit deduction is reflected transparently in your ledger.', 'reseller-management' ),
    ],
];
?>
<section class="rmhp-section rmhp-faq" id="rmhp-faq">
    <div class="rmhp-container">
        <div class="rmhp-section-header">
            <span class="rmhp-section-eyebrow"><?php esc_html_e( 'Got Questions?', 'reseller-management' ); ?></span>
            <h2 class="rmhp-section-title">
                <?php esc_html_e( 'Frequently Asked', 'reseller-management' ); ?>
                <span class="rmhp-text-accent"><?php esc_html_e( 'Questions', 'reseller-management' ); ?></span>
            </h2>
            <p class="rmhp-section-sub"><?php esc_html_e( 'Everything you need to know to get started', 'reseller-management' ); ?></p>
        </div>

        <div class="rmhp-faq-list">
            <?php foreach ( $faqs as $i => $faq ) : ?>
            <div class="rmhp-faq-item" id="rmhp-faq-<?php echo esc_attr( (string) $i ); ?>">
                <button class="rmhp-faq-question" aria-expanded="false" aria-controls="rmhp-faq-ans-<?php echo esc_attr( (string) $i ); ?>">
                    <span><?php echo esc_html( $faq['q'] ); ?></span>
                    <span class="rmhp-faq-chevron">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div class="rmhp-faq-answer" id="rmhp-faq-ans-<?php echo esc_attr( (string) $i ); ?>" hidden>
                    <p><?php echo esc_html( $faq['a'] ); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="rmhp-faq-cta">
            <p><?php esc_html_e( 'Still have questions?', 'reseller-management' ); ?></p>
            <a href="<?php echo esc_url( wp_login_url() ); ?>" class="rmhp-btn rmhp-btn-outline">
                <?php esc_html_e( 'Contact Support', 'reseller-management' ); ?>
            </a>
        </div>
    </div>
</section>
