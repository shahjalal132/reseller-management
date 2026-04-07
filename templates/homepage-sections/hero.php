<?php
/**
 * Homepage – Hero section.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="rmhp-hero" id="rmhp-home">
    <div class="rmhp-container">
        <div class="rmhp-hero-inner">
            <div class="rmhp-hero-content">
                <span class="rmhp-badge"><?php esc_html_e( 'Bangladesh\'s #1 Reseller Platform', 'reseller-management' ); ?></span>
                <h1 class="rmhp-hero-title">
                    <?php esc_html_e( 'Grow Your Business', 'reseller-management' ); ?><br>
                    <span class="rmhp-text-accent"><?php esc_html_e( 'as a Reseller', 'reseller-management' ); ?></span>
                </h1>
                <p class="rmhp-hero-desc">
                    <?php esc_html_e( 'Join thousands of resellers. Access wholesale products, manage your orders, track earnings, and withdraw anytime — all in one place.', 'reseller-management' ); ?>
                </p>
                <div class="rmhp-hero-actions">
                    <a href="<?php echo esc_url( home_url( '/reseller-registration/' ) ); ?>" class="rmhp-btn rmhp-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <?php esc_html_e( 'Register as Reseller', 'reseller-management' ); ?>
                    </a>
                    <a href="#rmhp-about" class="rmhp-btn rmhp-btn-outline rmhp-scroll-link">
                        <?php esc_html_e( 'Learn More', 'reseller-management' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="rmhp-hero-stats">
                    <div class="rmhp-hero-stat">
                        <span class="rmhp-stat-num">10,000+</span>
                        <span class="rmhp-stat-label"><?php esc_html_e( 'Active Resellers', 'reseller-management' ); ?></span>
                    </div>
                    <div class="rmhp-hero-stat-divider"></div>
                    <div class="rmhp-hero-stat">
                        <span class="rmhp-stat-num">500+</span>
                        <span class="rmhp-stat-label"><?php esc_html_e( 'Products', 'reseller-management' ); ?></span>
                    </div>
                    <div class="rmhp-hero-stat-divider"></div>
                    <div class="rmhp-hero-stat">
                        <span class="rmhp-stat-num">100%</span>
                        <span class="rmhp-stat-label"><?php esc_html_e( 'Secure Payments', 'reseller-management' ); ?></span>
                    </div>
                </div>
            </div>
            <div class="rmhp-hero-visual">
                <div class="rmhp-hero-card rmhp-hero-card-1">
                    <div class="rmhp-hcard-icon rmhp-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div>
                        <p class="rmhp-hcard-title"><?php esc_html_e( 'No Inventory Needed', 'reseller-management' ); ?></p>
                        <p class="rmhp-hcard-sub"><?php esc_html_e( 'Start without stock', 'reseller-management' ); ?></p>
                    </div>
                </div>
                <div class="rmhp-hero-card rmhp-hero-card-2">
                    <div class="rmhp-hcard-icon rmhp-icon-amber">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <p class="rmhp-hcard-title"><?php esc_html_e( 'Zero Investment', 'reseller-management' ); ?></p>
                        <p class="rmhp-hcard-sub"><?php esc_html_e( 'Free to join', 'reseller-management' ); ?></p>
                    </div>
                </div>
                <div class="rmhp-hero-card rmhp-hero-card-3">
                    <div class="rmhp-hcard-icon rmhp-icon-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div>
                        <p class="rmhp-hcard-title"><?php esc_html_e( 'Full Delivery', 'reseller-management' ); ?></p>
                        <p class="rmhp-hcard-sub"><?php esc_html_e( 'We handle shipping', 'reseller-management' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rmhp-hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f8fafc"/></svg>
    </div>
</section>
