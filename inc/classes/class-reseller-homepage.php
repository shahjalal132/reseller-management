<?php
/**
 * Reseller landing page shortcode and template.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Homepage {
    use Singleton;

    protected function __construct() {
        add_shortcode( 'reseller_homepage', [ $this, 'render' ] );
        add_filter( 'template_include', [ $this, 'maybe_use_homepage_template' ] );
        add_action( 'init', [ $this, 'maybe_create_page' ] );
    }

    /**
     * Idempotently create the reseller-homepage page if it doesn't exist.
     *
     * @return void
     */
    public function maybe_create_page() {
        if ( get_page_by_path( 'reseller-homepage' ) ) {
            return;
        }

        wp_insert_post( [
            'post_title'   => __( 'Reseller Homepage', 'reseller-management' ),
            'post_content' => '[reseller_homepage]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_name'    => 'reseller-homepage',
        ] );
    }

    /**
     * Swap to the homepage full-page template when this shortcode is on the page.
     *
     * @param string $template
     * @return string
     */
    public function maybe_use_homepage_template( $template ) {
        if ( ! is_singular() ) {
            return $template;
        }

        global $post;

        if ( ! $post instanceof \WP_Post || ! has_shortcode( (string) $post->post_content, 'reseller_homepage' ) ) {
            return $template;
        }

        return PLUGIN_BASE_PATH . '/templates/homepage-layout.php';
    }

    /**
     * Shortcode fallback – used only when the template swap is unavailable.
     *
     * @return string
     */
    public function render() {
        ob_start();
        $this->render_homepage();
        return (string) ob_get_clean();
    }

    /**
     * Render the full homepage.
     *
     * @return void
     */
    public function render_homepage() {
        $sections_dir = PLUGIN_BASE_PATH . '/templates/homepage-sections/';

        $sections = [
            'hero',
            'about',
            'advantages',
            'categories',
            'company-info',
            'services',
            'faq',
        ];

        foreach ( $sections as $section ) {
            $file = $sections_dir . $section . '.php';
            if ( file_exists( $file ) ) {
                include $file;
            }
        }
    }
}
