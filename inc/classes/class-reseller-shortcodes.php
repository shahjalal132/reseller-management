<?php
/**
 * Handles the creation and registration of Reseller shortcodes.
 *
 * @package Reseller_Management
 */

namespace BOILERPLATE\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Reseller_Shortcodes' ) ) {

	class Reseller_Shortcodes {

		use Traits\Singleton;

		/**
		 * Constructor.
		 */
		protected function __construct() {
			add_action( 'init', array( $this, 'register_shortcodes' ) );
		}

		/**
		 * Register shortcodes.
		 */
		public function register_shortcodes() {
			add_shortcode( 'reseller_registration', array( $this, 'reseller_registration_shortcode' ) );
			add_shortcode( 'reseller_dashboard', array( $this, 'reseller_dashboard_shortcode' ) );
		}

		/**
		 * Reseller Registration Shortcode.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public function reseller_registration_shortcode( $atts ) {
			ob_start();
			// Placeholder for registration form.
			// You would typically include a template file here.
			echo '<h2>Reseller Registration Form</h2>';
			echo '<p>This is a placeholder for the reseller registration form.</p>';
			return ob_get_clean();
		}

		/**
		 * Reseller Dashboard Shortcode.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public function reseller_dashboard_shortcode( $atts ) {
			ob_start();
			// Placeholder for dashboard content.
			// You would typically include a template file here.
			echo '<h2>Reseller Dashboard</h2>';
			echo '<p>This is a placeholder for the reseller dashboard content.</p>';
			return ob_get_clean();
		}
	}
}