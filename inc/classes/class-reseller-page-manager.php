<?php
/**
 * Handles the creation and management of Reseller pages.
 *
 * @package Reseller_Management
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Reseller_Page_Manager' ) ) {

	class Reseller_Page_Manager {

		use Singleton;

		/**
		 * Pages to create.
		 *
		 * @var array
		 */
		private $pages = array(
			'reseller_registration' => array(
				'title'     => 'Reseller Registration',
				'content'   => '[reseller_registration]',
				'page_name' => 'reseller-registration',
			),
			'reseller_dashboard'    => array(
				'title'     => 'Reseller Dashboard',
				'content'   => '[reseller_dashboard]',
				'page_name' => 'reseller-dashboard',
			),
		);

		/**
		 * Constructor.
		 */
		protected function __construct() {
			// This class will be initialized during plugin activation.
		}

		/**
		 * Check and create necessary pages on plugin activation.
		 */
		public function check_and_create_pages() {
			foreach ( $this->pages as $key => $page_data ) {
				$page_id = $this->get_page_id_by_slug( $page_data['page_name'] );

				if ( ! $page_id ) {
					$this->create_page( $page_data );
				}
			}
		}

		/**
		 * Get page ID by slug.
		 *
		 * @param string $slug The page slug.
		 * @return int|bool The page ID if found, otherwise false.
		 */
		private function get_page_id_by_slug( $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				return $page->ID;
			}
			return false;
		}

		/**
		 * Create a new page.
		 *
		 * @param array $page_data The page data including title, content, and slug.
		 */
		private function create_page( $page_data ) {
			$new_page = array(
				'post_title'   => $page_data['title'],
				'post_content' => $page_data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_name'    => $page_data['page_name'],
			);

			// Insert the page into the database.
			wp_insert_post( $new_page );
		}
	}
}
