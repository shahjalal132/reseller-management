<?php
/**
 * Single Product View template for Reseller Dashboard.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
$product    = wc_get_product( $product_id );

if ( ! $product ) {
    echo '<p>' . esc_html__( 'Product not found.', 'reseller-management' ) . '</p>';
    return;
}

$is_variation      = $product->is_type( 'variation' );
$base_product_id   = $is_variation ? $product->get_parent_id() : $product_id;
$base_product      = $is_variation ? wc_get_product( $base_product_id ) : $product;
$product_post      = get_post( $product_id );
$base_product_post = get_post( $base_product_id );

if ( ! $product_post ) {
    $product_post = $base_product_post;
}

$image_id = (int) $product->get_image_id();
if ( ! $image_id && $base_product ) {
    $image_id = (int) $base_product->get_image_id();
}
$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
$regular     = $product->get_regular_price();
$recommended = get_post_meta( $product_id, '_reseller_recommended_price', true );

if ( '' === $recommended || null === $recommended ) {
    $recommended = get_post_meta( $base_product_id, '_reseller_recommended_price', true );
}

$variation_choices      = [];
$selected_variation_id  = (int) $product_id;

if ( $base_product && $base_product->is_type( 'variable' ) ) {
    foreach ( $base_product->get_available_variations() as $variation_data ) {
        $variation_id = (int) ( $variation_data['variation_id'] ?? 0 );
        $variation    = wc_get_product( $variation_id );
        if ( ! $variation ) {
            continue;
        }

        $variation_regular = $variation->get_regular_price();
        $variation_recommended = $variation->get_meta( '_reseller_recommended_price' );
        if ( '' === $variation_recommended || null === $variation_recommended ) {
            $variation_recommended = $variation->get_price();
        }

        $variation_label = wc_get_formatted_variation( $variation, true, false, true );
        if ( ! $variation_label ) {
            $label_parts = [];
            $raw_attrs   = $variation_data['attributes'] ?? [];

            foreach ( $raw_attrs as $raw_key => $raw_value ) {
                if ( '' === (string) $raw_value ) {
                    continue;
                }

                $taxonomy = str_replace( 'attribute_', '', (string) $raw_key );
                $attr_label = wc_attribute_label( $taxonomy );
                if ( ! $attr_label || $attr_label === $taxonomy ) {
                    $attr_label = ucwords( str_replace( [ 'pa_', '_' ], [ '', ' ' ], $taxonomy ) );
                }

                $display_value = (string) $raw_value;
                if ( taxonomy_exists( $taxonomy ) ) {
                    $term = get_term_by( 'slug', (string) $raw_value, $taxonomy );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $display_value = $term->name;
                    }
                }

                $label_parts[] = sprintf( '%s: %s', $attr_label, $display_value );
            }

            if ( ! empty( $label_parts ) ) {
                $variation_label = implode( ', ', $label_parts );
            } else {
                $variation_label = sprintf(
                    /* translators: %d: variation ID. */
                    __( 'Variation #%d', 'reseller-management' ),
                    $variation_id
                );
            }
        }

        $variation_choices[] = [
            'id'          => $variation_id,
            'label'       => wp_strip_all_tags( $variation_label ),
            'regular'     => $variation_regular ? $variation_regular : '0',
            'recommended' => $variation_recommended ? $variation_recommended : '0',
        ];
    }

    if ( ! $is_variation && ! empty( $variation_choices ) ) {
        $selected_variation_id = (int) $variation_choices[0]['id'];
    }
}

// Gallery images
$attachment_ids = $product->get_gallery_image_ids();
if ( empty( $attachment_ids ) && $base_product ) {
    $attachment_ids = $base_product->get_gallery_image_ids();
}
$all_images    = [];
if ( $image_url ) {
    $all_images[] = $image_url;
}
foreach ( $attachment_ids as $attachment_id ) {
    $gallery_img = wp_get_attachment_image_url( $attachment_id, 'large' );
    if ( $gallery_img ) {
        $all_images[] = $gallery_img;
    }
}
$all_images = array_unique( $all_images );

// Prepare copy text
$display_title = $product->get_name();
if ( ! $display_title && $product_post ) {
    $display_title = $product_post->post_title;
}

$copy_text = $display_title . "\n";
if ( $product->get_sku() ) {
    $copy_text .= "SKU: " . $product->get_sku() . "\n";
}
if ( $regular ) {
    $copy_text .= "Price: {$regular} TK\n";
}
if ( $recommended ) {
    $copy_text .= "Customer / Retail Price : {$recommended}\n";
}
$desc = wp_strip_all_tags( $product_post->post_content ?? '' );
if ( ! $desc && $base_product_post ) {
    $desc = wp_strip_all_tags( $base_product_post->post_content );
}
if ( $desc ) {
    $copy_text .= "\n" . $desc;
}

$back_url = remove_query_arg( 'product_id' );
?>

<div class="rm-single-product-container">
    <div class="rm-single-product-header">
        <a href="<?php echo esc_url( $back_url ); ?>" class="rm-back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <?php esc_html_e( 'Back to Products', 'reseller-management' ); ?>
        </a>
    </div>

    <div class="rm-single-product-layout">
        <div class="rm-single-product-images">
            <div class="rm-main-image">
                <?php if ( $image_url ) : ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $display_title ); ?>" id="rm-main-product-img">
                <?php else : ?>
                    <div class="rm-product-img-placeholder"></div>
                <?php endif; ?>
            </div>
            <?php if ( count( $all_images ) > 1 ) : ?>
                <div class="rm-image-gallery">
                    <?php foreach ( $all_images as $img_url ) : ?>
                        <div class="rm-gallery-item">
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="" onclick="document.getElementById('rm-main-product-img').src='<?php echo esc_url( $img_url ); ?>'">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="rm-single-product-details">
            <h1 class="rm-product-title"><?php echo esc_html( $display_title ); ?></h1>
            <?php if ( $product->get_sku() ) : ?>
                <div class="rm-product-sku">
                    <span class="rm-label"><?php esc_html_e( 'SKU:', 'reseller-management' ); ?></span>
                    <span class="rm-value"><?php echo esc_html( $product->get_sku() ); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="rm-product-price-box">

                <?php if ( $base_product && $base_product->is_type( 'variable' ) ) : ?>
                    <?php
                    $selected_choice = null;
                    foreach ( $variation_choices as $variation_choice ) {
                        if ( (int) $variation_choice['id'] === (int) $selected_variation_id ) {
                            $selected_choice = $variation_choice;
                            break;
                        }
                    }
                    if ( ! $selected_choice && ! empty( $variation_choices ) ) {
                        $selected_choice = $variation_choices[0];
                    }
                    ?>
                    <div class="rm-price-item rm-price-item-variation-select">
                        <span class="rm-label"><?php esc_html_e( 'Variation & Price:', 'reseller-management' ); ?></span>
                        <select id="rm-variation-price-select" class="rm-value rm-variation-select">
                            <?php foreach ( $variation_choices as $variation_choice ) : ?>
                                <option
                                    value="<?php echo esc_attr( (string) $variation_choice['id'] ); ?>"
                                    data-label="<?php echo esc_attr( $variation_choice['label'] ); ?>"
                                    data-regular="<?php echo esc_attr( (string) $variation_choice['regular'] ); ?>"
                                    data-recommended="<?php echo esc_attr( (string) $variation_choice['recommended'] ); ?>"
                                    <?php selected( (int) $variation_choice['id'], (int) $selected_variation_id ); ?>
                                >
                                    <?php echo esc_html( $variation_choice['label'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rm-price-item">
                        <span class="rm-label" id="rm-selected-variation-label"><?php echo esc_html( $selected_choice['label'] ?? '' ); ?>:</span>
                        <span class="rm-value" id="rm-selected-variation-regular"><?php echo esc_html( $selected_choice['regular'] ?? '0' ); ?> TK</span>
                    </div>
                    <div class="rm-price-item recommended">
                        <span class="rm-label"><?php esc_html_e( 'Customer / Retail Price:', 'reseller-management' ); ?></span>
                        <span class="rm-value" id="rm-selected-variation-recommended"><?php echo esc_html( $selected_choice['recommended'] ?? '0' ); ?> TK</span>
                    </div>
                <?php elseif ( $base_product && $base_product->is_type( 'variation' ) ) : ?>
                    <div class="rm-price-item">
                        <span class="rm-label"><?php esc_html_e( 'Variation:', 'reseller-management' ); ?></span>
                        <span class="rm-value"><?php echo esc_html( $display_title ); ?></span>
                    </div>
                    <div class="rm-price-item">
                        <span class="rm-label"><?php esc_html_e( 'Price:', 'reseller-management' ); ?></span>
                        <span class="rm-value"><?php echo esc_html( $regular ? $regular : '0' ); ?> TK</span>
                    </div>
                    <div class="rm-price-item recommended">
                        <span class="rm-label"><?php esc_html_e( 'Customer / Retail Price:', 'reseller-management' ); ?></span>
                        <span class="rm-value"><?php echo esc_html( $recommended ? $recommended : '0' ); ?> TK</span>
                    </div>
                <?php elseif ( ! empty( $variation_choices ) ) : ?>
                    <?php foreach ( $variation_choices as $variation_choice ) : ?>
                        <div class="rm-price-item">
                            <span class="rm-label"><?php echo esc_html( $variation_choice['label'] ); ?>:</span>
                            <span class="rm-value"><?php echo esc_html( $variation_choice['regular'] ); ?> TK</span>
                        </div>
                        <div class="rm-price-item recommended">
                            <span class="rm-label"><?php esc_html_e( 'Customer / Retail Price:', 'reseller-management' ); ?></span>
                            <span class="rm-value"><?php echo esc_html( $variation_choice['recommended'] ); ?> TK</span>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="rm-price-item">
                        <span class="rm-label"><?php esc_html_e( 'Price:', 'reseller-management' ); ?></span>
                        <span class="rm-value"><?php echo esc_html( $regular ? $regular : '0' ); ?> TK</span>
                    </div>
                    <div class="rm-price-item recommended">
                        <span class="rm-label"><?php esc_html_e( 'Customer / Retail Price:', 'reseller-management' ); ?></span>
                        <span class="rm-value"><?php echo esc_html( $recommended ? $recommended : '0' ); ?> TK</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rm-product-order-box">
                <div class="rm-quantity-selector">
                    <button type="button" class="rm-qty-btn minus" onclick="updateQty(-1)">-</button>
                    <input type="number" id="rm-product-qty" value="1" min="1" onchange="updateBtnLink()">
                    <button type="button" class="rm-qty-btn plus" onclick="updateQty(1)">+</button>
                </div>
                <a href="<?php echo esc_url( add_query_arg( [ 'tab' => 'orders', 'subtab' => 'add', 'product_id' => $product_id, 'qty' => 1 ], remove_query_arg( [ 'tab', 'product_id' ] ) ) ); ?>" class="rm-button rm-button-primary order-now-btn" id="rm-order-now-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    <?php esc_html_e( 'Order Now', 'reseller-management' ); ?>
                </a>
            </div>

            <div class="rm-product-actions">
                <button class="rm-button copy-btn" type="button" data-copy="<?php echo esc_attr( $copy_text ); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    <?php esc_html_e( 'Copy Details', 'reseller-management' ); ?>
                </button>
                <?php if ( ! empty( $all_images ) ) : ?>
                    <button class="rm-button download-btn" type="button" data-images='<?php echo esc_attr( wp_json_encode( array_values( $all_images ) ) ); ?>'>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <?php esc_html_e( 'Download Images', 'reseller-management' ); ?>
                    </button>
                <?php endif; ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const variationSelect = document.getElementById('rm-variation-price-select');
                });

                function updateQty(delta) {
                    const input = document.getElementById('rm-product-qty');
                    let val = parseInt(input.value) + delta;
                    if (val < 1) val = 1;
                    input.value = val;
                    updateBtnLink();
                }
                function updateBtnLink() {
                    const qty = document.getElementById('rm-product-qty').value;
                    const link = document.getElementById('rm-order-now-link');
                    const variationSelect = document.getElementById('rm-variation-price-select');
                    const url = new URL(link.href);
                    url.searchParams.set('qty', qty);
                    if (variationSelect && variationSelect.value) {
                        url.searchParams.set('product_id', variationSelect.value);

                        const selectedOpt = variationSelect.options[variationSelect.selectedIndex];
                        const selectedLabelEl = document.getElementById('rm-selected-variation-label');
                        const selectedRegularEl = document.getElementById('rm-selected-variation-regular');
                        const selectedRecommendedEl = document.getElementById('rm-selected-variation-recommended');

                        if (selectedOpt && selectedLabelEl && selectedRegularEl && selectedRecommendedEl) {
                            selectedLabelEl.textContent = (selectedOpt.getAttribute('data-label') || '') + ':';
                            selectedRegularEl.textContent = (selectedOpt.getAttribute('data-regular') || '0') + ' TK';
                            selectedRecommendedEl.textContent = (selectedOpt.getAttribute('data-recommended') || '0') + ' TK';
                        }
                    }
                    link.href = url.toString();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const variationSelect = document.getElementById('rm-variation-price-select');
                    if (variationSelect) {
                        variationSelect.addEventListener('change', updateBtnLink);
                    }
                    updateBtnLink();
                });
            </script>

            <div class="rm-product-description">
                <h3><?php esc_html_e( 'Description', 'reseller-management' ); ?></h3>
                <div class="rm-description-content">
                    <?php
                    $description_html = $product_post->post_content ?? '';
                    if ( ! $description_html && $base_product_post ) {
                        $description_html = $base_product_post->post_content;
                    }
                    echo wp_kses_post( wpautop( $description_html ) );
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Related Products logic
    $related_ids = wc_get_related_products( $base_product_id, 5 );
    if ( ! empty( $related_ids ) ) : ?>
        <div class="rm-related-products-section">
            <h2 class="rm-section-title rm-related-products-title">
                <?php esc_html_e( 'Related Products', 'reseller-management' ); ?>
            </h2>
            <div class="rm-product-grid">
                <?php foreach ( $related_ids as $rel_id ) : 
                    $rel_product = wc_get_product( $rel_id );
                    if ( ! $rel_product ) continue;
                    $rel_image_url = get_the_post_thumbnail_url( $rel_id, 'large' );
                    $rel_regular   = $rel_product->get_regular_price();
                    $rel_recommended = get_post_meta( $rel_id, '_reseller_recommended_price', true );
                    $rel_url       = add_query_arg( 'product_id', $rel_id );

                    // Gather all images for related product
                    $rel_all_images = [];
                    if ( $rel_image_url ) {
                        $rel_all_images[] = $rel_image_url;
                    }
                    $rel_attachment_ids = $rel_product->get_gallery_image_ids();
                    if ( ! empty( $rel_attachment_ids ) ) {
                        foreach ( $rel_attachment_ids as $att_id ) {
                            $gal_img = wp_get_attachment_image_url( $att_id, 'large' );
                            if ( $gal_img ) $rel_all_images[] = $gal_img;
                        }
                    }
                    $rel_all_images = array_values( array_unique( $rel_all_images ) );

                    // Prepare copy text for related product
                    $rel_copy_text = $rel_product->get_name() . "\n";
                    if ( $rel_product->get_sku() ) {
                        $rel_copy_text .= "SKU: " . $rel_product->get_sku() . "\n";
                    }
                    if ( $rel_regular ) {
                        $rel_copy_text .= "Price: {$rel_regular} TK\n";
                    }
                    if ( $rel_recommended ) {
                        $rel_copy_text .= "Customer / Retail Price : {$rel_recommended}\n";
                    }
                    $rel_desc = wp_strip_all_tags( $rel_product->get_short_description() ?: $rel_product->get_description() );
                    if ( $rel_desc ) {
                        $rel_copy_text .= "\n" . $rel_desc;
                    }
                    ?>
                    <article class="rm-product-card">
                        <div class="rm-product-image">
                            <a href="<?php echo esc_url( $rel_url ); ?>">
                                <?php if ( $rel_image_url ) : ?>
                                    <img src="<?php echo esc_url( $rel_image_url ); ?>" alt="<?php echo esc_attr( $rel_product->get_name() ); ?>">
                                <?php else : ?>
                                    <div class="rm-product-img-placeholder"></div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="rm-product-info">
                            <h4 class="rm-product-title" title="<?php echo esc_attr( $rel_product->get_name() ); ?>">
                                <a href="<?php echo esc_url( $rel_url ); ?>">
                                    <?php echo esc_html( $rel_product->get_name() ); ?>
                                </a>
                            </h4>
                            <div class="rm-product-price-details">
                                <span class="rm-price-reg">Price: <?php echo esc_html( $rel_regular ? $rel_regular : '0' ); ?> TK</span>
                                <span class="rm-price-ret"><b>Customer / Retail Price : <?php echo esc_html( $rel_recommended ? $rel_recommended : '0' ); ?></b></span>
                            </div>
                            <div class="rm-product-actions">
                                <?php if ( ! empty( $rel_all_images ) ) : ?>
                                    <button class="rm-p-btn download-btn" type="button" title="<?php esc_attr_e( 'Download Images', 'reseller-management' ); ?>" data-images='<?php echo esc_attr( wp_json_encode( $rel_all_images ) ); ?>'>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    </button>
                                <?php endif; ?>
                                <button class="rm-p-btn copy-btn" type="button" title="<?php esc_attr_e( 'Copy Details', 'reseller-management' ); ?>" data-copy="<?php echo esc_attr( $rel_copy_text ); ?>">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Single product: responsive layout (desktop → mobile) */
.rm-single-product-container {
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
    padding: clamp(12px, 2.5vw, 24px);
    padding-left: max(clamp(12px, 2.5vw, 24px), env(safe-area-inset-left));
    padding-right: max(clamp(12px, 2.5vw, 24px), env(safe-area-inset-right));
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow-x: hidden;
}
.rm-single-product-header {
    margin-bottom: clamp(16px, 2vw, 24px);
}
.rm-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 600;
    font-size: clamp(14px, 1.5vw, 15px);
    transition: color 0.2s;
    min-height: 44px;
    padding: 4px 0;
}
.rm-back-link:hover {
    color: #0f172a;
}
.rm-single-product-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
    gap: clamp(24px, 4vw, 48px);
    align-items: start;
}
.rm-single-product-images,
.rm-single-product-details {
    min-width: 0;
}
.rm-main-image {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
}
.rm-main-image img {
    width: 100%;
    height: auto;
    max-height: min(85vh, 720px);
    object-fit: contain;
    display: block;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-sizing: border-box;
}
.rm-product-img-placeholder {
    min-height: clamp(200px, 40vw, 360px);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.rm-image-gallery {
    display: flex;
    gap: clamp(8px, 1.5vw, 12px);
    margin-top: clamp(12px, 2vw, 16px);
    overflow-x: auto;
    padding-bottom: 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.rm-image-gallery::-webkit-scrollbar {
    height: 6px;
}
.rm-image-gallery::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.rm-gallery-item {
    width: clamp(64px, 14vw, 88px);
    height: clamp(64px, 14vw, 88px);
    flex-shrink: 0;
    cursor: pointer;
}
.rm-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: border-color 0.2s;
}
.rm-gallery-item img:hover {
    border-color: #0f172a;
}
.rm-single-product-details .rm-product-title {
    margin-top: 0;
    margin-bottom: 0.35em;
    font-size: clamp(1.05rem, 2.2vw, 1.5rem);
    line-height: 1.25;
    color: #0f172a;
    font-weight: 800;
    word-wrap: break-word;
    overflow-wrap: anywhere;
}
.rm-product-sku {
    margin-bottom: 10px;
    font-size: clamp(13px, 1.4vw, 15px);
}
.rm-product-price-box {
    margin: clamp(16px, 2.5vw, 24px) 0;
    padding: clamp(14px, 2.5vw, 20px);
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e8edf3;
}
.rm-variation-select,
#rm-variation-price-select.rm-variation-select {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    font-size: clamp(13px, 1.4vw, 15px);
    background: #fff;
    color: #0f172a;
}
.rm-price-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.rm-price-item:last-child {
    margin-bottom: 0;
}
.rm-price-item .rm-label {
    flex: 0 1 auto;
    min-width: min(100%, 140px);
}
.rm-price-item .rm-value:not(select) {
    text-align: right;
    flex: 1 1 auto;
    min-width: 0;
    word-break: break-word;
}
.rm-price-item-variation-select {
    flex-direction: column;
    align-items: stretch;
}
.rm-price-item-variation-select .rm-label {
    min-width: 0;
}
.rm-price-item-variation-select .rm-variation-select {
    max-width: 100%;
}
.rm-price-item.recommended .rm-value {
    color: #059669;
    font-weight: 800;
}
.rm-label {
    color: #64748b;
    font-weight: 600;
    font-size: clamp(13px, 1.35vw, 14px);
}
.rm-value {
    color: #0f172a;
    font-weight: 700;
    font-size: clamp(13px, 1.35vw, 15px);
}
.rm-single-product-details .rm-product-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: clamp(24px, 3vw, 32px);
}
.rm-single-product-details .rm-product-actions .rm-button {
    flex: 1 1 200px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 16px;
    min-height: 44px;
    box-sizing: border-box;
}
.rm-product-description h3 {
    margin-bottom: 12px;
    font-size: clamp(16px, 1.8vw, 18px);
    color: #0f172a;
    font-weight: 700;
}
.rm-description-content {
    line-height: 1.65;
    color: #475569;
    font-size: clamp(14px, 1.6vw, 16px) !important;
    overflow-wrap: anywhere;
}
.rm-description-content img {
    max-width: 100%;
    height: auto;
}
.rm-product-order-box {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: clamp(12px, 2vw, 20px);
    margin-bottom: clamp(18px, 2.5vw, 24px);
    padding: clamp(12px, 2vw, 16px);
    background: #f1f5f9;
    border-radius: 10px;
}
.rm-quantity-selector {
    display: flex;
    align-items: stretch;
    flex: 1 1 160px;
    min-width: 0;
    max-width: 100%;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.rm-qty-btn {
    flex: 0 0 auto;
    width: clamp(40px, 10vw, 44px);
    min-height: 44px;
    border: none;
    background: #fff;
    color: #0f172a;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.rm-qty-btn:hover {
    background: #f1f5f9;
}
.rm-quantity-selector input {
    flex: 1 1 auto;
    min-width: 44px;
    width: 50px;
    height: auto;
    min-height: 44px;
    border: none;
    border-left: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    text-align: center;
    font-weight: 600;
    font-size: 16px;
    box-sizing: border-box;
}
.rm-product-order-box .order-now-btn {
    flex: 1 1 200px;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-sizing: border-box;
}
.rm-quantity-selector input::-webkit-inner-spin-button,
.rm-quantity-selector input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.rm-quantity-selector input {
    -moz-appearance: textfield;
    appearance: textfield;
}

/* Laptop / small desktop */
@media (max-width: 1199px) {
    .rm-single-product-layout {
        gap: clamp(20px, 3vw, 32px);
    }
    .rm-main-image img {
        max-height: min(75vh, 560px);
    }
}

/* Tablet portrait & large phone landscape: single column */
@media (max-width: 900px) {
    .rm-single-product-layout {
        grid-template-columns: 1fr;
        gap: clamp(20px, 4vw, 28px);
    }
    .rm-main-image img {
        max-height: min(70vh, 520px);
    }
}

@media (max-width: 768px) {
    .rm-product-order-box {
        flex-direction: column;
        align-items: stretch;
    }
    .rm-quantity-selector {
        flex: none;
        width: 100%;
    }
    .rm-product-order-box .order-now-btn {
        width: 100%;
        flex: none;
    }
    .rm-single-product-details .rm-product-actions .rm-button {
        flex: 1 1 100%;
        width: 100%;
    }
    .rm-price-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .rm-price-item .rm-value:not(select) {
        text-align: left;
    }
}

/* Mobile */
@media (max-width: 480px) {
    .rm-single-product-container {
        border-radius: 8px;
        padding: 12px;
    }
    .rm-gallery-item {
        width: 56px;
        height: 56px;
    }
    .rm-product-price-box {
        padding: 12px;
    }
}

.rm-related-products-section {
    margin-top: clamp(32px, 6vw, 60px);
    padding-top: clamp(24px, 4vw, 40px);
    border-top: 1px solid #e2e8f0;
}
.rm-related-products-title {
    font-size: clamp(1.05rem, 2.4vw, 1.375rem);
    color: #0f172a;
    font-weight: 700;
    margin: 0 0 clamp(16px, 2.5vw, 24px);
    line-height: 1.3;
}
.rm-related-products-section .rm-product-grid {
    gap: clamp(12px, 2vw, 16px);
}
@media (max-width: 600px) {
    .rm-related-products-section .rm-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
