<?php
/**
 * Global Footer template part.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$contact = \BOILERPLATE\Inc\Reseller_Helper::get_contact_settings( true );

$social_links = [
	'facebook'  => [
		'url'   => $contact['social_facebook'] ?? '',
		'label' => 'Facebook',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
	],
	'instagram' => [
		'url'   => $contact['social_instagram'] ?? '',
		'label' => 'Instagram',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
	],
	'twitter'   => [
		'url'   => $contact['social_twitter'] ?? '',
		'label' => 'Twitter',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>',
	],
	'youtube'   => [
		'url'   => $contact['social_youtube'] ?? '',
		'label' => 'YouTube',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02V8.98l5.75 3.02-5.75 3.02z"/></svg>',
	],
	'linkedin'  => [
		'url'   => $contact['social_linkedin'] ?? '',
		'label' => 'LinkedIn',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>',
	],
	'tiktok'    => [
		'url'   => $contact['social_tiktok'] ?? '',
		'label' => 'TikTok',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 0 0-.79-.05A6.34 6.34 0 0 0 3.16 15.2 6.34 6.34 0 0 0 9.5 21.54a6.34 6.34 0 0 0 6.34-6.34V8.7a8.19 8.19 0 0 0 4.76 1.52V6.84a4.84 4.84 0 0 1-.99-.15h-.02z"/></svg>',
	],
];

$active_social = array_filter(
	$social_links,
	static function ( $item ) {
		return ! empty( $item['url'] );
	}
);
?>
<footer class="rmhp-footer" id="rmhp-footer">
	<div class="rmhp-container">
		<div class="rmhp-footer-grid">

			<?php /* Brand column */ ?>
			<div class="rmhp-footer-col rmhp-footer-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rmhp-logo">
					<?php
					$logo_id = get_theme_mod( 'custom_logo' );
					if ( $logo_id ) {
						echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'rmhp-logo-img rmhp-footer-logo-img', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ] );
					} else {
						?>
						<span class="rmhp-logo-text"><?php bloginfo( 'name' ); ?></span>
						<?php
					}
					?>
				</a>
				<p class="rmhp-footer-tagline"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Bangladesh\'s leading reseller management platform.', 'reseller-management' ) ); ?></p>
				<?php if ( ! empty( $active_social ) ) : ?>
					<div class="rmhp-footer-social">
						<?php foreach ( $active_social as $social ) : ?>
							<a href="<?php echo esc_url( $social['url'] ); ?>" class="rmhp-social-link" aria-label="<?php echo esc_attr( $social['label'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo $social['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php /* Quick links */ ?>
			<div class="rmhp-footer-col">
				<h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Quick Links', 'reseller-management' ); ?></h4>
				<ul class="rmhp-footer-links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-home"       class="rmhp-scroll-link"><?php esc_html_e( 'Home', 'reseller-management' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-about"      class="rmhp-scroll-link"><?php esc_html_e( 'About Us', 'reseller-management' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-advantages" class="rmhp-scroll-link"><?php esc_html_e( 'Advantages', 'reseller-management' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-services"   class="rmhp-scroll-link"><?php esc_html_e( 'Services', 'reseller-management' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#rmhp-faq"        class="rmhp-scroll-link"><?php esc_html_e( 'FAQ', 'reseller-management' ); ?></a></li>
				</ul>
			</div>

			<?php /* Contact */ ?>
			<div class="rmhp-footer-col">
				<h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Contact Us', 'reseller-management' ); ?></h4>
				<ul class="rmhp-footer-contact">
					<?php if ( ! empty( $contact['contact_phone'] ) ) : ?>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.15 3.4 2 2 0 0 1 3.12 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16l.92.92z"/></svg>
							<a href="<?php echo esc_url( \BOILERPLATE\Inc\Reseller_Helper::get_tel_url( $contact['contact_phone'] ) ); ?>"><?php echo esc_html( $contact['contact_phone'] ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $contact['contact_email'] ) ) : ?>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
							<a href="<?php echo esc_url( 'mailto:' . $contact['contact_email'] ); ?>"><?php echo esc_html( $contact['contact_email'] ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $contact['contact_address'] ) ) : ?>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
							<?php echo esc_html( $contact['contact_address'] ); ?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $contact['contact_website'] ) ) : ?>
						<li>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
							<a href="<?php echo esc_url( $contact['contact_website'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( preg_replace( '#^https?://#i', '', $contact['contact_website'] ) ); ?></a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<?php /* Follow */ ?>
			<div class="rmhp-footer-col">
				<h4 class="rmhp-footer-col-title"><?php esc_html_e( 'Follow Us', 'reseller-management' ); ?></h4>
				<?php if ( ! empty( $active_social ) ) : ?>
					<div class="rmhp-footer-follow">
						<?php foreach ( $active_social as $key => $social ) : ?>
							<a href="<?php echo esc_url( $social['url'] ); ?>" class="rmhp-follow-btn rmhp-follow-<?php echo esc_attr( $key ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo $social['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
								<?php echo esc_html( $social['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="rmhp-footer-tagline"><?php esc_html_e( 'Social links will appear here once configured.', 'reseller-management' ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>

	<?php /* Copyright bar */ ?>
	<div class="rmhp-footer-bar">
		<div class="rmhp-container rmhp-footer-bar-inner">
			<span>
				<?php
				printf(
					/* translators: 1: year, 2: site name */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'reseller-management' ),
					esc_html( (string) gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</span>
			<span class="rmhp-footer-dev">
				<?php esc_html_e( 'Developed by', 'reseller-management' ); ?>
				<a href="https://grocoder.net" target="_blank" rel="noopener noreferrer">Grocoder Software Solution</a>
			</span>
		</div>
	</div>
</footer>

<?php
include __DIR__ . '/live-chat-widget.php';
