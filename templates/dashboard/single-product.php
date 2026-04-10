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

$product_post = get_post( $product_id );
$image_url    = get_the_post_thumbnail_url( $product_id, 'large' );
$regular      = $product->get_regular_price();
$recommended  = get_post_meta( $product_id, '_reseller_recommended_price', true );

// Gallery images
$attachment_ids = $product->get_gallery_image_ids();
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
$copy_text = $product_post->post_title . "\n";
if ( $product->get_sku() ) {
    $copy_text .= "SKU: " . $product->get_sku() . "\n";
}
if ( $regular ) {
    $copy_text .= "Price: {$regular} TK\n";
}
if ( $recommended ) {
    $copy_text .= "Customer / Retail Price : {$recommended}\n";
}
$desc = wp_strip_all_tags( $product_post->post_content );
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
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product_post->post_title ); ?>" id="rm-main-product-img">
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
            <h1 class="rm-product-title"><?php echo esc_html( $product_post->post_title ); ?></h1>
            <?php if ( $product->get_sku() ) : ?>
                <div class="rm-product-sku" style="margin-bottom: 10px;">
                    <span class="rm-label"><?php esc_html_e( 'SKU:', 'reseller-management' ); ?></span>
                    <span class="rm-value"><?php echo esc_html( $product->get_sku() ); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="rm-product-price-box">
                <div class="rm-price-item">
                    <span class="rm-label"><?php esc_html_e( 'Price:', 'reseller-management' ); ?></span>
                    <span class="rm-value"><?php echo esc_html( $regular ? $regular : '0' ); ?> TK</span>
                </div>
                <div class="rm-price-item recommended">
                    <span class="rm-label"><?php esc_html_e( 'Customer / Retail Price:', 'reseller-management' ); ?></span>
                    <span class="rm-value"><?php echo esc_html( $recommended ? $recommended : '0' ); ?> TK</span>
                </div>
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
                    const url = new URL(link.href);
                    url.searchParams.set('qty', qty);
                    link.href = url.toString();
                }
            </script>

            <div class="rm-product-description">
                <h3><?php esc_html_e( 'Description', 'reseller-management' ); ?></h3>
                <div class="rm-description-content">
                    <?php echo wp_kses_post( wpautop( $product_post->post_content ) ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Related Products logic
    $related_ids = wc_get_related_products( $product_id, 5 );
    if ( ! empty( $related_ids ) ) : ?>
        <div class="rm-related-products-section" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid #e2e8f0;">
            <h2 class="rm-section-title" style="font-size: 22px; color: #0f172a; font-weight: 700; margin-bottom: 24px;">
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
.rm-single-product-container {
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.rm-single-product-header {
    margin-bottom: 24px;
}
.rm-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 600;
    transition: color 0.2s;
}
.rm-back-link:hover {
    color: #0f172a;
}
.rm-single-product-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}
@media (max-width: 768px) {
    .rm-single-product-layout {
        grid-template-columns: 1fr;
    }
}
.rm-main-image img {
    width: 100%;
    height: auto;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.rm-image-gallery {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    overflow-x: auto;
    padding-bottom: 10px;
}
.rm-gallery-item {
    width: 80px;
    height: 80px;
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
    font-size: 16px;
    color: #0f172a;
    font-weight: 800;
}
.rm-product-price-box {
    margin: 24px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e8edf3;
}
.rm-price-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.rm-price-item:last-child {
    margin-bottom: 0;
}
.rm-price-item.recommended .rm-value {
    color: #059669;
    font-weight: 800;
}
.rm-label {
    color: #64748b;
    font-weight: 600;
}
.rm-value {
    color: #0f172a;
    font-weight: 700;
}
.rm-single-product-details .rm-product-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 32px;
}
.rm-single-product-details .rm-product-actions .rm-button {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
}
.rm-product-description h3 {
    margin-bottom: 16px;
    font-size: 18px;
    color: #0f172a;
    font-weight: 700;
}
.rm-description-content {
    line-height: 1.6;
    color: #475569;
    font-size: 16px !important;
}
.rm-product-order-box {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding: 15px;
    background: #f1f5f9;
    border-radius: 10px;
}
.rm-quantity-selector {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.rm-qty-btn {
    width: 36px;
    height: 36px;
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
    width: 50px;
    height: 36px;
    border: none;
    border-left: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    text-align: center;
    font-weight: 600;
}
.rm-quantity-selector input::-webkit-inner-spin-button,
.rm-quantity-selector input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.rm-quantity-selector input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

@media (max-width: 640px) {
    .rm-single-product-container {
        padding: 15px;
    }
    .rm-single-product-layout {
        gap: 24px;
    }
    .rm-product-order-box {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .rm-quantity-selector {
        width: 100%;
        justify-content: space-between;
    }
    .rm-quantity-selector input {
        flex: 1;
    }
    .rm-single-product-details .rm-product-actions {
        flex-direction: column;
    }
    .rm-single-product-details .rm-product-actions .rm-button {
        width: 100%;
    }
    .rm-price-item {
        flex-direction: column;
        gap: 4px;
    }
}
</style>
