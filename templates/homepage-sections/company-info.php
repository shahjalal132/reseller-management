<?php
/**
 * Homepage – Company Information section.
 */
defined( 'ABSPATH' ) || exit;

$stats = [
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'value' => '50+',
        'label' => __( 'Employees', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'value' => '8',
        'label' => __( 'Years Experience', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'value' => '15,252',
        'label' => __( 'Dropshippers', 'reseller-management' ),
    ],
    [
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        'value' => '5',
        'label' => __( 'Investors', 'reseller-management' ),
    ],
];
?>
<section class="rmhp-section rmhp-company-info" id="rmhp-company">
    <div class="rmhp-container">
        <div class="rmhp-section-header">
            <span class="rmhp-section-eyebrow"><?php esc_html_e( 'Who We Are', 'reseller-management' ); ?></span>
            <h2 class="rmhp-section-title">
                <?php esc_html_e( 'Company', 'reseller-management' ); ?>
                <span class="rmhp-text-accent"><?php esc_html_e( 'Information', 'reseller-management' ); ?></span>
            </h2>
            <p class="rmhp-section-sub"><?php esc_html_e( 'Some important facts about us', 'reseller-management' ); ?></p>
        </div>

        <div class="rmhp-stats-grid">
            <?php foreach ( $stats as $stat ) : ?>
            <div class="rmhp-stat-card">
                <div class="rmhp-stat-icon">
                    <?php echo $stat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <span class="rmhp-stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
                <span class="rmhp-stat-lbl"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
