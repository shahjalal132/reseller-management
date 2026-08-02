<?php
/**
 * Settings tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

use BOILERPLATE\Inc\Reseller_Helper;

$user_id     = get_current_user_id();
$user        = get_userdata( $user_id );
$slug        = Reseller_Helper::get_shop_slug( $user_id );
$shop_url    = Reseller_Helper::get_shop_url( $user_id );
$business    = (string) get_user_meta( $user_id, '_reseller_business_name', true );
$phone       = (string) get_user_meta( $user_id, '_reseller_phone', true );
$brand_color = Reseller_Helper::get_shop_brand_color( $user_id );
$logo_id     = Reseller_Helper::get_shop_logo_id( $user_id );
$logo_url    = $logo_id ? Reseller_Helper::get_shop_logo_url( $user_id, 'medium' ) : '';
?>
<div class="rm-settings">

	<header class="rm-settings-hero">
		<div class="rm-settings-hero-text">
			<p class="rm-settings-eyebrow"><?php esc_html_e( 'Account', 'reseller-management' ); ?></p>
			<h2 class="rm-settings-hero-title"><?php esc_html_e( 'Settings', 'reseller-management' ); ?></h2>
			<p class="rm-settings-hero-desc"><?php esc_html_e( 'Manage your public shop look, profile details, and password in one place.', 'reseller-management' ); ?></p>
		</div>
		<div class="rm-settings-hero-meta">
			<span class="rm-settings-pill is-live"><?php esc_html_e( 'Shop live', 'reseller-management' ); ?></span>
			<?php if ( $business ) : ?>
				<span class="rm-settings-pill is-soft"><?php echo esc_html( $business ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<section class="rm-settings-panel">
		<div class="rm-settings-panel-head">
			<div class="rm-settings-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
			</div>
			<div>
				<h3><?php esc_html_e( 'My Shop', 'reseller-management' ); ?></h3>
				<p><?php esc_html_e( 'Logo, brand color, and the public link customers use to order.', 'reseller-management' ); ?></p>
			</div>
		</div>

		<form id="rm-shop-slug-form" class="rm-settings-form" method="post" enctype="multipart/form-data">
			<div class="rm-settings-block">
				<label class="rm-settings-label"><?php esc_html_e( 'Shop logo', 'reseller-management' ); ?></label>
				<div class="rm-settings-media">
					<div class="rm-settings-logo-box <?php echo $logo_url ? 'has-logo' : ''; ?>">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="rm-shop-logo-preview" id="rm-shop-logo-preview">
						<?php else : ?>
							<img src="" alt="" class="rm-shop-logo-preview is-empty" id="rm-shop-logo-preview" hidden>
							<span class="rm-shop-logo-placeholder" id="rm-shop-logo-placeholder">
								<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
								<?php esc_html_e( 'No logo yet', 'reseller-management' ); ?>
							</span>
						<?php endif; ?>
					</div>
					<div class="rm-settings-media-actions">
						<label class="rm-settings-upload">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
							<span><?php esc_html_e( 'Upload logo', 'reseller-management' ); ?></span>
							<input type="file" name="shop_logo" id="rm-shop-logo-input" accept="image/jpeg,image/png,image/gif,image/webp">
						</label>
						<label class="rm-settings-remove" <?php echo $logo_url ? '' : 'hidden'; ?>>
							<input type="checkbox" name="remove_logo" id="rm-shop-logo-remove" value="1">
							<?php esc_html_e( 'Remove logo', 'reseller-management' ); ?>
						</label>
						<p class="rm-settings-hint"><?php esc_html_e( 'PNG or JPG works best. Appears in shop header & footer.', 'reseller-management' ); ?></p>
					</div>
				</div>
			</div>

			<div class="rm-settings-cols">
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-shop-slug-input"><?php esc_html_e( 'Shop slug', 'reseller-management' ); ?></label>
					<input type="text" name="shop_slug" id="rm-shop-slug-input" value="<?php echo esc_attr( $slug ); ?>" pattern="[a-z0-9\-]+" required>
					<p class="rm-settings-hint"><?php esc_html_e( 'Letters, numbers, and hyphens only.', 'reseller-management' ); ?></p>
				</div>
				<div class="rm-settings-block">
					<label class="rm-settings-label"><?php esc_html_e( 'Brand color', 'reseller-management' ); ?></label>
					<div class="rm-settings-color">
						<input type="color" id="rm-shop-brand-color-picker" value="<?php echo esc_attr( $brand_color ); ?>" aria-label="<?php esc_attr_e( 'Pick brand color', 'reseller-management' ); ?>">
						<input type="text" name="brand_color" id="rm-shop-brand-color" value="<?php echo esc_attr( $brand_color ); ?>" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#f97316">
						<span class="rm-brand-color-preview" id="rm-shop-brand-preview" style="background: <?php echo esc_attr( $brand_color ); ?>;"></span>
						<button type="button" class="rm-settings-ghost-btn" id="rm-shop-brand-reset"><?php esc_html_e( 'Reset', 'reseller-management' ); ?></button>
					</div>
					<p class="rm-settings-hint"><?php esc_html_e( 'Buttons, nav bar, and accents on My Shop.', 'reseller-management' ); ?></p>
				</div>
			</div>

			<div class="rm-settings-block">
				<label class="rm-settings-label" for="rm-shop-url-display"><?php esc_html_e( 'Public shop URL', 'reseller-management' ); ?></label>
				<div class="rm-settings-url">
					<input type="text" id="rm-shop-url-display" value="<?php echo esc_attr( $shop_url ); ?>" readonly>
					<button type="button" class="rm-settings-ghost-btn" id="rm-copy-shop-url"><?php esc_html_e( 'Copy', 'reseller-management' ); ?></button>
					<a class="rm-settings-primary-btn" id="rm-open-shop-url" href="<?php echo esc_url( $shop_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open shop', 'reseller-management' ); ?></a>
				</div>
			</div>

			<div class="rm-settings-footer">
				<button type="submit" class="rm-settings-primary-btn"><?php esc_html_e( 'Save shop settings', 'reseller-management' ); ?></button>
				<div class="rm-form-response" aria-live="polite"></div>
			</div>
		</form>
	</section>

	<section class="rm-settings-panel">
		<div class="rm-settings-panel-head">
			<div class="rm-settings-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</div>
			<div>
				<h3><?php esc_html_e( 'Profile', 'reseller-management' ); ?></h3>
				<p><?php esc_html_e( 'Your name, phone, and business information.', 'reseller-management' ); ?></p>
			</div>
		</div>

		<form id="rm-profile-form" class="rm-settings-form" method="post" enctype="multipart/form-data">
			<div class="rm-settings-block">
				<label class="rm-settings-label"><?php esc_html_e( 'Profile photo', 'reseller-management' ); ?></label>
				<div class="rm-settings-media">
					<div class="rm-settings-avatar-box">
						<?php echo get_avatar( $user_id, 96, '', '', [ 'class' => 'rm-profile-avatar-preview' ] ); ?>
					</div>
					<div class="rm-settings-media-actions">
						<label class="rm-settings-upload">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
							<span><?php esc_html_e( 'Change photo', 'reseller-management' ); ?></span>
							<input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
						</label>
						<p class="rm-settings-hint"><?php esc_html_e( 'JPG, PNG, GIF, or WebP.', 'reseller-management' ); ?></p>
					</div>
				</div>
			</div>

			<div class="rm-settings-cols">
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-profile-name"><?php esc_html_e( 'Name', 'reseller-management' ); ?></label>
					<input type="text" id="rm-profile-name" name="display_name" value="<?php echo esc_attr( $user ? $user->display_name : '' ); ?>" required>
				</div>
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-profile-phone"><?php esc_html_e( 'Phone', 'reseller-management' ); ?></label>
					<input type="text" id="rm-profile-phone" name="phone" value="<?php echo esc_attr( $phone ); ?>" required>
				</div>
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-profile-business"><?php esc_html_e( 'Business Name', 'reseller-management' ); ?></label>
					<input type="text" id="rm-profile-business" name="business_name" value="<?php echo esc_attr( $business ); ?>" required>
				</div>
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-profile-facebook"><?php esc_html_e( 'Facebook URL', 'reseller-management' ); ?></label>
					<input type="url" id="rm-profile-facebook" name="facebook_url" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_fb_url', true ) ); ?>" placeholder="https://">
				</div>
				<div class="rm-settings-block rm-settings-span-2">
					<label class="rm-settings-label" for="rm-profile-website"><?php esc_html_e( 'Website URL', 'reseller-management' ); ?></label>
					<input type="url" id="rm-profile-website" name="website_url" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_web_url', true ) ); ?>" placeholder="https://">
				</div>
			</div>

			<div class="rm-settings-footer">
				<button type="submit" class="rm-settings-primary-btn"><?php esc_html_e( 'Save Profile', 'reseller-management' ); ?></button>
				<div class="rm-form-response" aria-live="polite"></div>
			</div>
		</form>
	</section>

	<section class="rm-settings-panel">
		<div class="rm-settings-panel-head">
			<div class="rm-settings-icon is-amber" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
			</div>
			<div>
				<h3><?php esc_html_e( 'Password', 'reseller-management' ); ?></h3>
				<p><?php esc_html_e( 'Choose a strong password with at least 8 characters.', 'reseller-management' ); ?></p>
			</div>
		</div>

		<form id="rm-password-form" class="rm-settings-form">
			<div class="rm-settings-cols">
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-new-password"><?php esc_html_e( 'New Password', 'reseller-management' ); ?></label>
					<input type="password" id="rm-new-password" name="password" minlength="8" placeholder="<?php esc_attr_e( 'New password', 'reseller-management' ); ?>" required>
				</div>
				<div class="rm-settings-block">
					<label class="rm-settings-label" for="rm-confirm-password"><?php esc_html_e( 'Confirm Password', 'reseller-management' ); ?></label>
					<input type="password" id="rm-confirm-password" name="confirm_password" minlength="8" placeholder="<?php esc_attr_e( 'Confirm password', 'reseller-management' ); ?>" required>
				</div>
			</div>

			<div class="rm-settings-footer">
				<button type="submit" class="rm-settings-primary-btn"><?php esc_html_e( 'Update Password', 'reseller-management' ); ?></button>
				<div class="rm-form-response" aria-live="polite"></div>
			</div>
		</form>
	</section>

</div>
