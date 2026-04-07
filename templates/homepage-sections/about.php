<?php
/**
 * Homepage – About Us section.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="rmhp-section rmhp-about" id="rmhp-about">
    <div class="rmhp-container">
        <div class="rmhp-about-inner">

            <div class="rmhp-about-content">
                <span class="rmhp-section-eyebrow"><?php esc_html_e( 'About Us', 'reseller-management' ); ?></span>
                <h2 class="rmhp-section-title rmhp-title-left">
                    <?php esc_html_e( 'What is this platform & how does it simplify dropshipping?', 'reseller-management' ); ?>
                </h2>
                <p class="rmhp-section-sub rmhp-section-sub-left">
                    <?php esc_html_e( 'Bangladesh\'s first and leading dropshipping platform', 'reseller-management' ); ?>
                </p>

                <div class="rmhp-about-grid">
                    <div class="rmhp-about-card">
                        <div class="rmhp-about-card-icon rmhp-icon-bg-green">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        </div>
                        <div>
                            <h4><?php esc_html_e( 'No Inventory Required', 'reseller-management' ); ?></h4>
                            <p><?php esc_html_e( 'Start your business without needing to stock products. We ship directly according to your orders.', 'reseller-management' ); ?></p>
                        </div>
                    </div>
                    <div class="rmhp-about-card">
                        <div class="rmhp-about-card-icon rmhp-icon-bg-amber">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <div>
                            <h4><?php esc_html_e( 'No Capital Needed', 'reseller-management' ); ?></h4>
                            <p><?php esc_html_e( 'Take advance payment from customers, then pay us. Start with zero investment.', 'reseller-management' ); ?></p>
                        </div>
                    </div>
                    <div class="rmhp-about-card">
                        <div class="rmhp-about-card-icon rmhp-icon-bg-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        </div>
                        <div>
                            <h4><?php esc_html_e( 'Use Our Photos', 'reseller-management' ); ?></h4>
                            <p><?php esc_html_e( 'Use our high-resolution product photos, videos and marketing materials for free.', 'reseller-management' ); ?></p>
                        </div>
                    </div>
                    <div class="rmhp-about-card">
                        <div class="rmhp-about-card-icon rmhp-icon-bg-teal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        </div>
                        <div>
                            <h4><?php esc_html_e( 'Full Delivery Service', 'reseller-management' ); ?></h4>
                            <p><?php esc_html_e( 'From order processing, packaging to delivery — we manage everything for you.', 'reseller-management' ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="rmhp-about-badges">
                    <span class="rmhp-trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php esc_html_e( '100% Secure Payment', 'reseller-management' ); ?>
                    </span>
                    <span class="rmhp-trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php esc_html_e( '24/7 Customer Support', 'reseller-management' ); ?>
                    </span>
                    <span class="rmhp-trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <?php esc_html_e( 'Real-Time Tracking', 'reseller-management' ); ?>
                    </span>
                </div>

                <div class="rmhp-about-cta">
                    <a href="<?php echo esc_url( home_url( '/reseller-registration/' ) ); ?>" class="rmhp-btn rmhp-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <?php esc_html_e( 'Register as Reseller', 'reseller-management' ); ?>
                    </a>
                    <span class="rmhp-about-count">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <?php esc_html_e( '10,000+ successful resellers joined us', 'reseller-management' ); ?>
                    </span>
                </div>
            </div>

            <div class="rmhp-about-visual">
                <div class="rmhp-about-img-wrap">
                    <div class="rmhp-about-img-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        <span><?php esc_html_e( 'Platform Preview', 'reseller-management' ); ?></span>
                    </div>
                    <div class="rmhp-about-floating rmhp-floating-1">
                        <span class="rmhp-floating-num">10K+</span>
                        <span class="rmhp-floating-txt"><?php esc_html_e( 'Resellers', 'reseller-management' ); ?></span>
                    </div>
                    <div class="rmhp-about-floating rmhp-floating-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php esc_html_e( 'Order Delivered', 'reseller-management' ); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
