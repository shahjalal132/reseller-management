<?php
/**
 * Reseller fields on the core user edit screen (wp-admin/user-edit.php).
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_User_Profile_Admin {
	use Singleton;

	/**
	 * Register hooks.
	 */
	protected function __construct() {
		add_action( 'edit_user_profile', [ $this, 'render_reseller_fields' ], 15 );
		add_action( 'edit_user_profile_update', [ $this, 'save_reseller_fields' ], 15, 1 );
	}

	/**
	 * Output reseller status controls when an admin edits a reseller user.
	 *
	 * @param \WP_User $user User being edited.
	 *
	 * @return void
	 */
	public function render_reseller_fields( $user ) {
		if ( ! $user instanceof \WP_User || ! Reseller_Helper::is_reseller( $user ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$stored_status = Reseller_Helper::get_reseller_status( $user->ID );

		$banned_until = (int) get_user_meta( $user->ID, '_reseller_banned_until', true );
		$ban_date     = $banned_until ? gmdate( 'Y-m-d', $banned_until ) : '';
		?>
		<h2><?php esc_html_e( 'Reseller account', 'reseller-management' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Control approval and temporary bans for this reseller.', 'reseller-management' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="rm_reseller_status"><?php esc_html_e( 'Reseller status', 'reseller-management' ); ?></label>
				</th>
				<td>
					<?php wp_nonce_field( 'rm_reseller_user_profile_' . $user->ID, 'rm_reseller_user_profile_nonce' ); ?>
					<select name="rm_reseller_status" id="rm_reseller_status">
						<?php foreach ( Reseller_Helper::get_statuses() as $status ) : ?>
							<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $stored_status, $status ); ?>>
								<?php echo esc_html( ucfirst( $status ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Only approved resellers can use the frontend dashboard. Banned also respects the ban-until date below.', 'reseller-management' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="rm_reseller_banned_until"><?php esc_html_e( 'Ban until', 'reseller-management' ); ?></label>
				</th>
				<td>
					<input type="date" name="rm_reseller_banned_until" id="rm_reseller_banned_until" value="<?php echo esc_attr( $ban_date ); ?>">
					<label style="margin-left: 12px;">
						<input type="checkbox" name="rm_reseller_clear_ban" value="1">
						<?php esc_html_e( 'Clear ban date', 'reseller-management' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If set, login is blocked until the end of that day (site timezone). Clear the ban to remove the restriction.', 'reseller-management' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Persist reseller fields from the user edit form.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function save_reseller_fields( $user_id ) {
		if ( ! isset( $_POST['rm_reseller_user_profile_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rm_reseller_user_profile_nonce'] ) ), 'rm_reseller_user_profile_' . $user_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
			return;
		}

		if ( ! empty( $_POST['rm_reseller_clear_ban'] ) ) {
			delete_user_meta( $user_id, '_reseller_banned_until' );
		} else {
			$banned_input = isset( $_POST['rm_reseller_banned_until'] ) ? sanitize_text_field( wp_unslash( $_POST['rm_reseller_banned_until'] ) ) : '';
			if ( '' === $banned_input ) {
				delete_user_meta( $user_id, '_reseller_banned_until' );
			} else {
				$timestamp = strtotime( $banned_input . ' 23:59:59' );
				if ( false !== $timestamp ) {
					update_user_meta( $user_id, '_reseller_banned_until', $timestamp );
				}
			}
		}

		$status = isset( $_POST['rm_reseller_status'] ) ? sanitize_key( wp_unslash( $_POST['rm_reseller_status'] ) ) : '';
		if ( in_array( $status, Reseller_Helper::get_statuses(), true ) ) {
			update_user_meta( $user_id, '_reseller_status', $status );
		}
	}
}
