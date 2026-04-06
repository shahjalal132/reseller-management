<?php
/**
 * Bootstraps the plugin. load class.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Autoloader {
    use Singleton;

    protected function __construct() {
        // Load plugin services.
        I18n::get_instance();
        Reseller_Setup::get_instance();
        Enqueue_Assets::get_instance();
        Admin_Top_Menu::get_instance();
        APIS::get_instance();
        Reseller_Auth::get_instance();
        Reseller_Registration::get_instance();
        Reseller_Dashboard::get_instance();
        Reseller_Orders::get_instance();
        Reseller_Finance::get_instance();
        Reseller_Product_Meta::get_instance();
        Reseller_User_Profile_Admin::get_instance();
        Reseller_Wc_Order_Admin::get_instance();
        Steadfast_Webhook::get_instance();
    }
}