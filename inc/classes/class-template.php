<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Credentials_Options;
use BOILERPLATE\Inc\Traits\Program_Logs;
use BOILERPLATE\Inc\Traits\Singleton;

class Template {

    use Singleton;
    use Program_Logs;
    use Credentials_Options;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_filter( 'template_include', [ $this, 'maybe_use_404_template' ], 99 );
    }

    /**
     * Swap to the 404 full-page template when a 404 error occurs.
     *
     * @param string $template
     * @return string
     */
    public function maybe_use_404_template( $template ) {
        if ( is_404() ) {
            $custom_404 = PLUGIN_BASE_PATH . '/templates/404-layout.php';
            if ( file_exists( $custom_404 ) ) {
                return $custom_404;
            }
        }
        return $template;
    }

}