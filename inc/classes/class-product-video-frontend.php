<?php
/**
 * Display product video on public storefront.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Product_Video_Frontend {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'woocommerce_before_single_product_summary', [ $this, 'display_video' ], 25 );
        add_action( 'wp_footer', [ $this, 'render_video_modal_js' ] );
    }

    /**
     * Display watch video button or overlay on single product page.
     *
     * @return void
     */
    public function display_video() {
        global $product;

        if ( ! $product ) {
            return;
        }

        $video_url = get_post_meta( $product->get_id(), '_reseller_product_video_url', true );

        if ( ! $video_url ) {
            return;
        }

        ?>
        <div class="rm-public-video-wrapper" style="margin-bottom: 20px;">
            <button type="button" class="button" onclick="openProductVideo('<?php echo esc_url( $video_url ); ?>')" style="display: flex; align-items: center; gap: 8px; background: #000; color: #fff;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                <?php esc_html_e( 'Watch Product Video', 'reseller-management' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render the JS needed for the video modal.
     *
     * @return void
     */
    public function render_video_modal_js() {
        if ( ! is_product() ) {
            return;
        }
        ?>
        <script>
            function openProductVideo(url) {
                if (!url) return;
                
                let embedUrl = url;
                if (url.includes('youtube.com/shorts/')) {
                    embedUrl = url.replace('youtube.com/shorts/', 'youtube.com/embed/');
                } else if (url.includes('youtube.com/watch?v=')) {
                    embedUrl = url.replace('watch?v=', 'embed/');
                } else if (url.includes('youtu.be/')) {
                    embedUrl = url.replace('youtu.be/', 'youtube.com/embed/');
                } else if (url.includes('vimeo.com/')) {
                    embedUrl = url.replace('vimeo.com/', 'player.vimeo.com/video/');
                }
                
                const modal = document.createElement('div');
                modal.className = 'rm-video-modal-overlay';
                modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:10000;display:flex;align-items:center;justify-content:center;';
                
                const container = document.createElement('div');
                container.style.cssText = 'position:relative;width:90%;max-width:960px;aspect-ratio:16/9;background:#000;border-radius:12px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.4);';
                
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '&times;';
                closeBtn.style.cssText = 'position:absolute;top:10px;right:15px;background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:32px;cursor:pointer;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:2;';
                closeBtn.onclick = () => modal.remove();
                
                const iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.style.cssText = 'width:100%;height:100%;border:none;';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;
                
                container.appendChild(closeBtn);
                container.appendChild(iframe);
                modal.appendChild(container);
                modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
                document.body.appendChild(modal);
            }
        </script>
        <?php
    }
}
