<?php
/**
 * Categories visibility tab (Mohasagor-style Active / De-active).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

use BOILERPLATE\Inc\Reseller_Helper;

$user_id = get_current_user_id();

$terms = get_terms(
	[
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	]
);

$parents = $children = $grandchildren = [];
$by_id   = [];

if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	foreach ( $terms as $term ) {
		$by_id[ $term->term_id ] = $term;
	}

	foreach ( $terms as $term ) {
		$parent_id = (int) $term->parent;
		if ( 0 === $parent_id ) {
			$parents[] = $term;
			continue;
		}
		$grandparent_id = isset( $by_id[ $parent_id ] ) ? (int) $by_id[ $parent_id ]->parent : 0;
		if ( 0 === $grandparent_id ) {
			$children[] = $term;
		} else {
			$grandchildren[] = $term;
		}
	}
}

/**
 * Render a category toggle row.
 *
 * @param WP_Term $term    Term.
 * @param int     $user_id Reseller ID.
 */
$render_row = static function ( $term, $user_id ) {
	$active = Reseller_Helper::is_category_active( $user_id, $term->term_id );
	?>
	<div class="rm-cat-toggle-row" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
		<span class="rm-cat-name"><?php echo esc_html( $term->name ); ?></span>
		<div class="rm-cat-actions">
			<span class="rm-cat-badge <?php echo $active ? 'is-active' : 'is-deactive'; ?>">
				<?php echo $active ? esc_html__( 'Active', 'reseller-management' ) : esc_html__( 'De-active', 'reseller-management' ); ?>
			</span>
			<button type="button" class="rm-button rm-button-sm rm-toggle-category <?php echo $active ? 'is-active' : 'is-deactive'; ?>" data-term-id="<?php echo esc_attr( $term->term_id ); ?>" title="<?php echo $active ? esc_attr__( 'De-activate', 'reseller-management' ) : esc_attr__( 'Activate', 'reseller-management' ); ?>">
				<?php echo $active ? '✓' : '✕'; ?>
			</button>
		</div>
	</div>
	<?php
};
?>
<div class="rm-card">
	<h3><?php esc_html_e( 'Categories', 'reseller-management' ); ?></h3>
	<p class="rm-muted"><?php esc_html_e( 'Control which product categories appear on your My Shop. De-activated categories (and their products, unless also in another active category) are hidden from customers.', 'reseller-management' ); ?></p>

	<div class="rm-categories-grid">
		<div class="rm-cat-column">
			<h4><?php esc_html_e( 'Category', 'reseller-management' ); ?></h4>
			<div class="rm-cat-list">
				<?php
				if ( empty( $parents ) ) {
					echo '<p class="rm-muted">' . esc_html__( 'No categories found.', 'reseller-management' ) . '</p>';
				} else {
					foreach ( $parents as $term ) {
						$render_row( $term, $user_id );
					}
				}
				?>
			</div>
		</div>
		<div class="rm-cat-column">
			<h4><?php esc_html_e( 'Sub Category', 'reseller-management' ); ?></h4>
			<div class="rm-cat-list">
				<?php
				if ( empty( $children ) ) {
					echo '<p class="rm-muted">' . esc_html__( 'No sub categories.', 'reseller-management' ) . '</p>';
				} else {
					foreach ( $children as $term ) {
						$render_row( $term, $user_id );
					}
				}
				?>
			</div>
		</div>
		<div class="rm-cat-column">
			<h4><?php esc_html_e( 'Sub Sub Category', 'reseller-management' ); ?></h4>
			<div class="rm-cat-list">
				<?php
				if ( empty( $grandchildren ) ) {
					echo '<p class="rm-muted">' . esc_html__( 'No sub sub categories.', 'reseller-management' ) . '</p>';
				} else {
					foreach ( $grandchildren as $term ) {
						$render_row( $term, $user_id );
					}
				}
				?>
			</div>
		</div>
	</div>
	<div class="rm-form-response rm-categories-response" aria-live="polite"></div>
</div>
