<?php
/**
 * Shared helpers for reseller management flows.
 */

namespace BOILERPLATE\Inc;

class Reseller_Helper {

    /**
     * Get the reseller role slug.
     *
     * @return string
     */
    public static function get_role_slug() {
        return 'reseller';
    }

    /**
     * Get reseller ledger table name.
     *
     * @return string
     */
    public static function get_ledger_table_name() {
        global $wpdb;

        return $wpdb->prefix . 'reseller_ledger';
    }

    /**
     * Get reseller withdrawals table name.
     *
     * @return string
     */
    public static function get_withdrawals_table_name() {
        global $wpdb;

        return $wpdb->prefix . 'reseller_withdrawals';
    }

    /**
     * Get reseller payment methods table name.
     *
     * @return string
     */
    public static function get_payment_methods_table_name() {
        global $wpdb;

        return $wpdb->prefix . 'reseller_payment_methods';
    }

    /**
     * Ensure the payment_methods table exists (runtime upgrade for existing installs).
     *
     * @return void
     */
    public static function maybe_create_payment_methods_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table           = self::get_payment_methods_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            reseller_id bigint(20) unsigned NOT NULL,
            method_name varchar(20) NOT NULL,
            number varchar(64) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'personal',
            method_details longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY reseller_id (reseller_id)
        ) {$charset_collate};";

        dbDelta( $sql );

        // One-time runtime upgrades for installs created before method_details / wider number column.
        if ( '1' !== get_option( 'rm_pm_extra_columns', '' ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is internal.
            $has_details = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'method_details'" );
            if ( empty( $has_details ) ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN method_details longtext DEFAULT NULL AFTER type" );
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "ALTER TABLE `{$table}` MODIFY number varchar(64) NOT NULL" );
            update_option( 'rm_pm_extra_columns', '1', true );
        }
    }

    /**
     * Build the account line(s) shown on withdrawal requests for a saved payment method row.
     *
     * @param object $method Row from reseller_payment_methods.
     *
     * @return string
     */
    public static function format_payment_method_for_withdrawal( $method ) {
        $key = strtolower( (string) ( $method->method_name ?? '' ) );
        if ( 'bank' === $key ) {
            $details = json_decode( (string) ( $method->method_details ?? '' ), true );
            if ( ! is_array( $details ) ) {
                $details = [];
            }
            $lines   = [];
            $holder  = isset( $details['holder'] ) ? trim( (string) $details['holder'] ) : '';
            $bank    = isset( $details['bank_name'] ) ? trim( (string) $details['bank_name'] ) : '';
            $branch  = isset( $details['branch'] ) ? trim( (string) $details['branch'] ) : '';
            $acct    = trim( (string) ( $method->number ?? '' ) );
            if ( $holder !== '' ) {
                $lines[] = sprintf(
                    /* translators: %s: account holder name */
                    __( 'Account holder: %s', 'reseller-management' ),
                    $holder
                );
            }
            if ( $bank !== '' ) {
                $lines[] = sprintf(
                    /* translators: %s: bank name */
                    __( 'Bank name: %s', 'reseller-management' ),
                    $bank
                );
            }
            if ( $acct !== '' ) {
                $lines[] = sprintf(
                    /* translators: %s: bank account number */
                    __( 'Account number: %s', 'reseller-management' ),
                    $acct
                );
            }
            if ( $branch !== '' ) {
                $lines[] = sprintf(
                    /* translators: %s: branch name */
                    __( 'Branch: %s', 'reseller-management' ),
                    $branch
                );
            }
            return implode( "\n", $lines );
        }

        return trim( (string) ( $method->number ?? '' ) );
    }

    /**
     * Add ledger.reference column on existing installs (dbDelta only runs on activation).
     *
     * @return void
     */
    public static function maybe_upgrade_ledger_reference_column() {
        if ( '1' === get_option( 'rm_ledger_has_reference_column', '' ) ) {
            return;
        }

        global $wpdb;

        $table = self::get_ledger_table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is internal.
        $exists = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'reference'" );

        if ( empty( $exists ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is internal.
            $wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN reference varchar(191) NOT NULL DEFAULT '' AFTER description" );
        }

        update_option( 'rm_ledger_has_reference_column', '1', true );
    }

    /**
     * Parse admin datetime input into MySQL datetime (site timezone).
     *
     * @param string $raw Empty string uses current time. Accepts HTML datetime-local (Y-m-d\TH:i) or Y-m-d H:i:s.
     *
     * @return string|null MySQL datetime or null if invalid.
     */
    public static function parse_ledger_datetime_input( $raw ) {
        $raw = trim( (string) $raw );

        if ( '' === $raw ) {
            return current_time( 'mysql' );
        }

        $tz = wp_timezone();

        try {
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $raw ) ) {
                $dt = \DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $raw, $tz );
            } elseif ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $raw ) ) {
                $dt = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $raw, $tz );
            } else {
                return null;
            }

            if ( false === $dt ) {
                return null;
            }

            $errors = \DateTime::getLastErrors();
            if ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) {
                return null;
            }

            return $dt->format( 'Y-m-d H:i:s' );
        } catch ( \Exception $e ) {
            return null;
        }
    }

    /**
     * Get saved payment methods for a reseller.
     *
     * @param int $user_id Reseller ID.
     *
     * @return array<int, object>
     */
    public static function get_payment_methods( $user_id ) {
        global $wpdb;

        $table = self::get_payment_methods_table_name();

        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE reseller_id = %d ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    /**
     * Ensure the reseller role exists.
     *
     * @return void
     */
    public static function maybe_register_role() {
        $slug = self::get_role_slug();
        $role = get_role( $slug );

        if ( ! $role ) {
            add_role(
                $slug,
                __( 'Reseller', 'reseller-management' ),
                [
                    'read'         => true,
                    'upload_files' => true,
                ]
            );

            return;
        }

        if ( ! $role->has_cap( 'upload_files' ) ) {
            $role->add_cap( 'upload_files' );
        }
    }

    /**
     * Get supported reseller statuses.
     *
     * @return array<string>
     */
    public static function get_statuses() {
        return [ 'pending', 'approved', 'rejected', 'banned' ];
    }

    /**
     * Check whether a user is a reseller.
     *
     * @param \WP_User|int|null $user User instance or ID.
     *
     * @return bool
     */
    public static function is_reseller( $user ) {
        if ( is_numeric( $user ) ) {
            $user = get_user_by( 'id', (int) $user );
        }

        if ( ! $user instanceof \WP_User ) {
            return false;
        }

        return in_array( self::get_role_slug(), (array) $user->roles, true );
    }

    /**
     * Get the reseller status.
     *
     * @param int $user_id User ID.
     *
     * @return string
     */
    public static function get_reseller_status( $user_id ) {
        $status = (string) get_user_meta( $user_id, '_reseller_status', true );

        if ( ! in_array( $status, self::get_statuses(), true ) ) {
            $status = 'pending';
        }

        if ( self::is_currently_banned( $user_id ) ) {
            return 'banned';
        }

        return $status;
    }

    /**
     * Check if reseller is approved.
     *
     * @param int $user_id User ID.
     *
     * @return bool
     */
    public static function is_reseller_approved( $user_id ) {
        return 'approved' === self::get_reseller_status( $user_id );
    }

    /**
     * Check if reseller has an active ban.
     *
     * @param int $user_id User ID.
     *
     * @return bool
     */
    public static function is_currently_banned( $user_id ) {
        $banned_until = (int) get_user_meta( $user_id, '_reseller_banned_until', true );

        return $banned_until > time();
    }

    /**
     * Get the current balance from the ledger.
     *
     * @param int $user_id User ID.
     *
     * @return float
     */
    public static function get_current_balance( $user_id ) {
        global $wpdb;

        $table = self::get_ledger_table_name();
        $balance = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE reseller_id = %d",
                $user_id
            )
        );

        return (float) $balance;
    }

    /**
     * Insert a ledger row.
     *
     * @param array<string, mixed> $data Ledger data.
     *
     * @return bool|int
     */
    public static function insert_ledger_entry( array $data ) {
        global $wpdb;

        $amount = isset( $data['amount'] ) ? round( (float) $data['amount'], 2 ) : 0;
        $created = current_time( 'mysql' );
        if ( ! empty( $data['created_at'] ) && is_string( $data['created_at'] ) ) {
            $parsed = self::parse_ledger_datetime_input( $data['created_at'] );
            if ( null !== $parsed ) {
                $created = $parsed;
            }
        }

        $reference = '';
        if ( isset( $data['reference'] ) ) {
            $reference = sanitize_text_field( (string) $data['reference'] );
            if ( strlen( $reference ) > 191 ) {
                $reference = substr( $reference, 0, 191 );
            }
        }

        $inserted = $wpdb->insert(
            self::get_ledger_table_name(),
            [
                'reseller_id' => (int) $data['reseller_id'],
                'order_id'    => ! empty( $data['order_id'] ) ? (int) $data['order_id'] : 0,
                'type'        => sanitize_key( $data['type'] ),
                'amount'      => $amount,
                'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
                'reference'   => $reference,
                'created_at'  => $created,
            ],
            [ '%d', '%d', '%s', '%f', '%s', '%s', '%s' ]
        );

        return $inserted ? (int) $wpdb->insert_id : false;
    }

    /**
     * Get monthly profit rows for dashboard summaries.
     *
     * @param int $user_id User ID.
     *
     * @return array<int, object>
     */
    public static function get_monthly_profit_summary( $user_id ) {
        global $wpdb;

        $table = self::get_ledger_table_name();

        // Get actual data from DB (limit to 12 record-holding months for efficiency, though we generate 12 calendar months)
        $results = (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT(created_at, '%%Y-%%m') AS month_key, COALESCE(SUM(amount), 0) AS total
                FROM {$table}
                WHERE reseller_id = %d
                GROUP BY DATE_FORMAT(created_at, '%%Y-%%m')
                ORDER BY month_key DESC
                LIMIT 12",
                $user_id
            )
        );

        $mapped_data = [];
        foreach ( $results as $row ) {
            $mapped_data[ $row->month_key ] = (float) $row->total;
        }

        // Generate last 12 months including current
        $final_data = [];
        for ( $i = 0; $i < 12; $i++ ) {
            $month_key = date( 'Y-m', strtotime( "-$i months" ) );
            $final_data[] = (object) [
                'month_key' => $month_key,
                'total'     => $mapped_data[ $month_key ] ?? 0.0,
            ];
        }

        return $final_data;
    }

    /**
     * Minimum account balance resellers must keep after a withdrawal (from admin settings).
     *
     * @return float
     */
    public static function get_minimum_balance_reserve() {
        $settings = get_option( 'rm_settings', [] );
        $raw      = $settings['minimum_balance'] ?? 0;
        $val      = is_numeric( $raw ) ? (float) $raw : 0.0;

        return max( 0.0, round( $val, 2 ) );
    }

    /**
     * Maximum amount a reseller may request to withdraw given current balance and minimum reserve.
     *
     * @param float $current_balance Current ledger balance.
     *
     * @return float
     */
    public static function get_max_withdrawable_amount( $current_balance ) {
        $reserve = self::get_minimum_balance_reserve();

        return max( 0.0, round( (float) $current_balance - $reserve, 2 ) );
    }

    /**
     * Whether packaging cost deduction is enabled in admin settings.
     *
     * @return bool
     */
    public static function is_packaging_cost_enabled() {
        $settings = get_option( 'rm_settings', [] );

        return ( $settings['packaging_cost_enabled'] ?? 'no' ) === 'yes';
    }

    /**
     * Fixed packaging cost amount from admin settings (0 when disabled or invalid).
     *
     * @return float
     */
    public static function get_packaging_cost_amount() {
        if ( ! self::is_packaging_cost_enabled() ) {
            return 0.0;
        }

        $settings = get_option( 'rm_settings', [] );
        $raw      = $settings['packaging_cost_input1'] ?? 0;
        $val      = is_numeric( $raw ) ? (float) $raw : 0.0;

        return max( 0.0, round( $val, 2 ) );
    }

    /**
     * Admin-defined shipping charge presets for the reseller order form.
     *
     * @return array<int, array{title: string, charge: float}>
     */
    public static function get_shipping_presets() {
        $settings = get_option( 'rm_settings', [] );
        $raw      = $settings['shipping_presets'] ?? [];

        if ( ! is_array( $raw ) ) {
            return [];
        }

        $out = [];
        foreach ( $raw as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $title = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';
            if ( '' === $title ) {
                continue;
            }
            $charge = isset( $row['charge'] ) && is_numeric( $row['charge'] )
                ? max( 0.0, round( (float) $row['charge'], 2 ) )
                : 0.0;
            $out[]  = [
                'title'  => $title,
                'charge' => $charge,
            ];
        }

        return $out;
    }

    /**
     * Get reseller dashboard tabs.
     *
     * @return array<string, string>
     */
    public static function get_dashboard_tabs() {
        return [
            'dashboard' => [
                'label' => __( 'Dashboard', 'reseller-management' ),
                'icon'  => 'dashboard',
            ],
            'orders'    => [
                'label'    => __( 'Orders', 'reseller-management' ),
                'icon'     => 'orders',
                'children' => [
                    'all' => __( 'All Orders', 'reseller-management' ),
                    'add' => __( 'Add New Order', 'reseller-management' ),
                ],
            ],
            'products'  => [
                'label' => __( 'Products', 'reseller-management' ),
                'icon'  => 'products',
            ],
            'account'   => [
                'label'    => __( 'Account', 'reseller-management' ),
                'icon'     => 'account',
                'children' => [
                    'withdrawals'     => __( 'Withdrawals', 'reseller-management' ),
                    'payment-methods' => __( 'Payment Methods', 'reseller-management' ),
                    'transactions'    => __( 'Transaction Statement', 'reseller-management' ),
                ],
            ],
            'settings'  => [
                'label' => __( 'Settings', 'reseller-management' ),
                'icon'  => 'settings',
            ],
            'customers' => [
                'label' => __( 'Customers', 'reseller-management' ),
                'icon'  => 'customers',
            ],
        ];
    }

    /**
     * Get user meta field map.
     *
     * @return array<string, string>
     */
    public static function get_profile_meta_map() {
        return [
            'phone'         => '_reseller_phone',
            'business_name' => '_reseller_business_name',
            'facebook_url'  => '_reseller_fb_url',
            'website_url'   => '_reseller_web_url',
            'nid_front_id'  => '_reseller_nid_front_id',
            'nid_back_id'   => '_reseller_nid_back_id',
        ];
    }

    /**
     * Branding color field definitions (internal key => meta).
     *
     * @return array<string, array{label: string, default: string}>
     */
    public static function get_branding_color_fields() {
        return [
            'primary_color'      => [
                'label'   => __( 'Primary Color', 'reseller-management' ),
                'default' => '#005b4e',
            ],
            'accent_color'       => [
                'label'   => __( 'Accent Color', 'reseller-management' ),
                'default' => '#f59e0b',
            ],
            'header_bg'          => [
                'label'   => __( 'Header Background', 'reseller-management' ),
                'default' => '#ffffff',
            ],
            'header_text'        => [
                'label'   => __( 'Header Text Color', 'reseller-management' ),
                'default' => '#0f172a',
            ],
            'footer_bg'          => [
                'label'   => __( 'Footer Background', 'reseller-management' ),
                'default' => '#0f172a',
            ],
            'footer_text'        => [
                'label'   => __( 'Footer Text Color', 'reseller-management' ),
                'default' => '#cbd5e1',
            ],
            'link_color'         => [
                'label'   => __( 'Link Color', 'reseller-management' ),
                'default' => '#005b4e',
            ],
            'button_color'       => [
                'label'   => __( 'Button Color', 'reseller-management' ),
                'default' => '#005b4e',
            ],
            'button_hover_color' => [
                'label'   => __( 'Button Hover Color', 'reseller-management' ),
                'default' => '#004d40',
            ],
            'text_color'         => [
                'label'   => __( 'Text Color', 'reseller-management' ),
                'default' => '#0f172a',
            ],
            'heading_color'      => [
                'label'   => __( 'Heading Color', 'reseller-management' ),
                'default' => '#0f172a',
            ],
        ];
    }

    /**
     * Default branding values.
     *
     * @return array<string, string>
     */
    public static function get_branding_defaults() {
        $defaults = [
            'body_font'    => 'Arial',
            'heading_font' => 'Arial',
        ];

        foreach ( self::get_branding_color_fields() as $key => $field ) {
            $defaults[ $key ] = $field['default'];
        }

        return $defaults;
    }

    /**
     * Curated font choices for branding settings.
     *
     * @return array<string, string> slug => label
     */
    public static function get_font_choices() {
        return [
            'Arial'            => 'Arial',
            'Helvetica'        => 'Helvetica',
            'system-ui'        => 'System UI',
            'Georgia'          => 'Georgia',
            'Verdana'          => 'Verdana',
            'Inter'            => 'Inter',
            'Roboto'           => 'Roboto',
            'Open Sans'        => 'Open Sans',
            'Lato'             => 'Lato',
            'Poppins'          => 'Poppins',
            'Nunito'           => 'Nunito',
            'Montserrat'       => 'Montserrat',
            'Source Sans 3'    => 'Source Sans 3',
            'Playfair Display' => 'Playfair Display',
            'Merriweather'     => 'Merriweather',
        ];
    }

    /**
     * Fonts that ship with the OS and do not need Google Fonts.
     *
     * @return string[]
     */
    public static function get_system_fonts() {
        return [ 'Arial', 'Helvetica', 'system-ui', 'Georgia', 'Verdana', 'Times New Roman', 'Courier New', 'Trebuchet MS' ];
    }

    /**
     * Resolved branding settings with defaults.
     *
     * @return array<string, string>
     */
    public static function get_branding_settings() {
        $defaults = self::get_branding_defaults();
        $settings = get_option( 'rm_settings', [] );
        $fonts    = array_keys( self::get_font_choices() );
        $out      = [];

        foreach ( self::get_branding_color_fields() as $key => $field ) {
            $hex = sanitize_hex_color( $settings[ 'branding_' . $key ] ?? '' );
            $out[ $key ] = $hex ? $hex : $defaults[ $key ];
        }

        $body    = sanitize_text_field( $settings['branding_body_font'] ?? '' );
        $heading = sanitize_text_field( $settings['branding_heading_font'] ?? '' );

        $out['body_font']    = in_array( $body, $fonts, true ) ? $body : $defaults['body_font'];
        $out['heading_font'] = in_array( $heading, $fonts, true ) ? $heading : $defaults['heading_font'];

        return $out;
    }

    /**
     * Sanitize branding fields from a settings POST payload.
     *
     * @param array $post Raw POST data (already unslashed preferred).
     *
     * @return array<string, string> Keys prefixed with branding_ for rm_settings.
     */
    public static function sanitize_branding_from_post( array $post ) {
        $defaults = self::get_branding_defaults();
        $fonts    = array_keys( self::get_font_choices() );
        $out      = [];

        foreach ( array_keys( self::get_branding_color_fields() ) as $key ) {
            $hex = sanitize_hex_color( $post[ 'branding_' . $key ] ?? '' );
            $out[ 'branding_' . $key ] = $hex ? $hex : $defaults[ $key ];
        }

        $body    = sanitize_text_field( $post['branding_body_font'] ?? '' );
        $heading = sanitize_text_field( $post['branding_heading_font'] ?? '' );

        $out['branding_body_font']    = in_array( $body, $fonts, true ) ? $body : $defaults['body_font'];
        $out['branding_heading_font'] = in_array( $heading, $fonts, true ) ? $heading : $defaults['heading_font'];

        return $out;
    }

    /**
     * Build a CSS font-family stack for a chosen font name.
     *
     * @param string $font Font name from get_font_choices().
     *
     * @return string
     */
    public static function get_font_stack( $font ) {
        $serif    = [ 'Georgia', 'Playfair Display', 'Merriweather', 'Times New Roman' ];
        $fallback = in_array( $font, $serif, true ) ? 'serif' : 'sans-serif';

        if ( 'system-ui' === $font ) {
            return 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        }

        $safe = preg_replace( "/[^a-zA-Z0-9 \\-]/", '', (string) $font );

        return "'" . $safe . "', " . $fallback;
    }

    /**
     * Darken a hex color by a percentage (0–100).
     *
     * @param string $hex Hex color.
     * @param int    $percent Percent to darken.
     *
     * @return string
     */
    public static function darken_hex_color( $hex, $percent = 12 ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
            return '#004d40';
        }

        $ratio = max( 0, min( 100, (int) $percent ) ) / 100;
        $out   = '#';
        for ( $i = 0; $i < 3; $i++ ) {
            $channel = hexdec( substr( $hex, $i * 2, 2 ) );
            $channel = (int) max( 0, min( 255, round( $channel * ( 1 - $ratio ) ) ) );
            $out    .= str_pad( dechex( $channel ), 2, '0', STR_PAD_LEFT );
        }

        return $out;
    }

    /**
     * Lighten a hex color by a percentage (0–100).
     *
     * @param string $hex Hex color.
     * @param int    $percent Percent to lighten.
     *
     * @return string
     */
    public static function lighten_hex_color( $hex, $percent = 12 ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
            return '#007a73';
        }

        $ratio = max( 0, min( 100, (int) $percent ) ) / 100;
        $out   = '#';
        for ( $i = 0; $i < 3; $i++ ) {
            $channel = hexdec( substr( $hex, $i * 2, 2 ) );
            $channel = (int) max( 0, min( 255, round( $channel + ( ( 255 - $channel ) * $ratio ) ) ) );
            $out    .= str_pad( dechex( $channel ), 2, '0', STR_PAD_LEFT );
        }

        return $out;
    }

    /**
     * Google Fonts stylesheet URL for non-system fonts in branding, or empty string.
     *
     * @param array|null $branding Optional branding array from get_branding_settings().
     *
     * @return string
     */
    public static function get_branding_google_fonts_url( $branding = null ) {
        $branding = is_array( $branding ) ? $branding : self::get_branding_settings();
        $system   = self::get_system_fonts();
        $needed   = [];

        foreach ( [ $branding['body_font'], $branding['heading_font'] ] as $font ) {
            if ( ! in_array( $font, $system, true ) && ! in_array( $font, $needed, true ) ) {
                $needed[] = $font;
            }
        }

        if ( empty( $needed ) ) {
            return '';
        }

        $families = [];
        foreach ( $needed as $font ) {
            $families[] = 'family=' . rawurlencode( $font ) . ':wght@400;500;600;700';
        }

        return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
    }

    /**
     * Inline CSS that overrides theme CSS variables from branding settings.
     *
     * @param string     $context  'public' or 'admin'.
     * @param array|null $branding Optional branding array.
     *
     * @return string
     */
    public static function get_branding_inline_css( $context = 'public', $branding = null ) {
        $branding = is_array( $branding ) ? $branding : self::get_branding_settings();

        $primary       = $branding['primary_color'];
        $accent        = $branding['accent_color'];
        $header_bg     = $branding['header_bg'];
        $header_text   = $branding['header_text'];
        $footer_bg     = $branding['footer_bg'];
        $footer_text   = $branding['footer_text'];
        $link          = $branding['link_color'];
        $button        = $branding['button_color'];
        $button_hover  = $branding['button_hover_color'];
        $text          = $branding['text_color'];
        $heading_color = $branding['heading_color'];
        $sidebar_dark  = self::darken_hex_color( $primary, 15 );
        $light         = self::lighten_hex_color( $primary, 18 );
        $sidebar_hover = self::lighten_hex_color( $sidebar_dark, 10 );
        $body          = self::get_font_stack( $branding['body_font'] );
        $heading       = self::get_font_stack( $branding['heading_font'] );

        if ( 'admin' === $context ) {
            return ":root {
  --primary-color: {$primary};
  --primary-light-color: {$light};
  --rm-primary: {$primary};
  --rm-primary-dark: {$button_hover};
  --rm-accent: {$accent};
  --rm-text: {$text};
  --rm-button: {$button};
  --rm-button-hover: {$button_hover};
  --rm-link: {$link};
  --rm-heading: {$heading_color};
}
.rm-page-header .rm-page-title,
.page-heading-title h1,
.rm-card-title {
  font-family: {$heading};
  color: {$heading_color};
}
.rm-settings-btn--primary,
.rm-pay-btn {
  background: {$button} !important;
}
.rm-settings-btn--primary:hover,
.rm-pay-btn:hover {
  background: {$button_hover} !important;
}
a {
  color: {$link};
}";
        }

        return ":root {
  --rm-primary: {$primary};
  --rm-primary-dark: {$button_hover};
  --rm-sidebar-bg: {$sidebar_dark};
  --rm-sidebar-hover: {$sidebar_hover};
  --rm-sidebar-active: {$sidebar_hover};
  --rm-accent: {$accent};
  --rm-text: {$text};
  --rm-header-bg: {$header_bg};
  --rm-header-text: {$header_text};
  --rm-footer-bg: {$footer_bg};
  --rm-footer-text: {$footer_text};
  --rm-link: {$link};
  --rm-button: {$button};
  --rm-button-hover: {$button_hover};
  --rm-heading: {$heading_color};
  --primary-color: {$primary};
}
body.rm-dashboard-body,
body.rmhp-body,
.rm-dashboard-body,
.rmhp-body {
  font-family: {$body} !important;
  color: {$text};
}
.rm-dashboard-header h1,
.rm-auth-header h2,
.rm-card h3,
.rmhp-hero-title,
.rmhp-section-title,
.rmhp-body h1,
.rmhp-body h2,
.rmhp-body h3,
.rmhp-body h4,
.rmhp-body h5,
.rmhp-body h6,
.rm-dashboard-body h1,
.rm-dashboard-body h2,
.rm-dashboard-body h3 {
  font-family: {$heading};
  color: {$heading_color};
}
.rmhp-header {
  background: {$header_bg} !important;
}
.rmhp-logo-text,
.rmhp-nav-link {
  color: {$header_text} !important;
}
.rmhp-nav-link:hover,
.rmhp-nav-link.rmhp-nav-active {
  color: {$primary} !important;
}
.rmhp-footer {
  background: {$footer_bg} !important;
  color: {$footer_text} !important;
}
.rmhp-footer .rmhp-logo-text,
.rmhp-footer-col-title,
.rmhp-footer-bar {
  color: {$footer_text} !important;
}
.rmhp-footer-tagline,
.rmhp-footer-links a,
.rmhp-footer-contact,
.rmhp-footer-dev,
.rmhp-social-link {
  color: {$footer_text} !important;
  opacity: .85;
}
.rmhp-footer-links a:hover,
.rmhp-footer-dev a:hover {
  color: {$accent} !important;
  opacity: 1;
}
.rm-dashboard-body a:not(.rm-button):not(.rmhp-btn):not(.rm-nav-link),
.rmhp-body a:not(.rmhp-btn):not(.rmhp-nav-link):not(.rm-button) {
  color: {$link};
}
.rm-button,
.rm-button-primary,
.rmhp-btn-primary,
.rm-button-submit {
  background: {$button} !important;
  border-color: {$button} !important;
  color: #fff !important;
}
.rm-button:hover,
.rm-button-primary:hover,
.rmhp-btn-primary:hover,
.rm-button-submit:hover {
  background: {$button_hover} !important;
  border-color: {$button_hover} !important;
  color: #fff !important;
}
.rmhp-btn-outline,
.rm-button-outline {
  color: {$button} !important;
  border-color: {$button} !important;
}
.rmhp-btn-outline:hover,
.rm-button-outline:hover {
  background: {$button} !important;
  color: #fff !important;
}";
    }

    /**
     * Enqueue Google Fonts (if needed) and attach branding CSS to a stylesheet handle.
     *
     * @param string $style_handle Registered style handle to attach inline CSS to.
     * @param string $context      'public' or 'admin'.
     *
     * @return void
     */
    public static function enqueue_branding_assets( $style_handle, $context = 'public' ) {
        $branding  = self::get_branding_settings();
        $fonts_url = self::get_branding_google_fonts_url( $branding );

        if ( $fonts_url ) {
            wp_enqueue_style( 'rm-branding-fonts', $fonts_url, [], null );
        }

        $css = self::get_branding_inline_css( $context, $branding );
        if ( $css ) {
            wp_add_inline_style( $style_handle, $css );
        }
    }

    /**
     * Contact / social / live-chat field definitions.
     *
     * @return array<string, array{label: string, type: string, default: string, placeholder?: string, section: string}>
     */
    public static function get_contact_field_defs() {
        return [
            'contact_phone'   => [
                'label'       => __( 'Phone Number', 'reseller-management' ),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => '+8801XXXXXXXXX',
                'section'     => 'contact',
            ],
            'contact_email'   => [
                'label'       => __( 'Email Address', 'reseller-management' ),
                'type'        => 'email',
                'default'     => '',
                'placeholder' => 'info@example.com',
                'section'     => 'contact',
            ],
            'contact_address' => [
                'label'       => __( 'Address', 'reseller-management' ),
                'type'        => 'textarea',
                'default'     => '',
                'placeholder' => __( 'Office address', 'reseller-management' ),
                'section'     => 'contact',
            ],
            'contact_website' => [
                'label'       => __( 'Website URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://example.com',
                'section'     => 'contact',
            ],
            'social_facebook'  => [
                'label'       => __( 'Facebook URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://facebook.com/yourpage',
                'section'     => 'social',
            ],
            'social_instagram' => [
                'label'       => __( 'Instagram URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://instagram.com/yourpage',
                'section'     => 'social',
            ],
            'social_twitter'   => [
                'label'       => __( 'Twitter / X URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://x.com/yourpage',
                'section'     => 'social',
            ],
            'social_youtube'   => [
                'label'       => __( 'YouTube URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://youtube.com/@yourchannel',
                'section'     => 'social',
            ],
            'social_linkedin'  => [
                'label'       => __( 'LinkedIn URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://linkedin.com/company/yourpage',
                'section'     => 'social',
            ],
            'social_tiktok'    => [
                'label'       => __( 'TikTok URL', 'reseller-management' ),
                'type'        => 'url',
                'default'     => '',
                'placeholder' => 'https://tiktok.com/@yourpage',
                'section'     => 'social',
            ],
            'chat_enabled'          => [
                'label'   => __( 'Enable Live Chat Buttons', 'reseller-management' ),
                'type'    => 'checkbox',
                'default' => 'yes',
                'section' => 'chat',
            ],
            'chat_messenger'        => [
                'label'       => __( 'Messenger Page URL or Username', 'reseller-management' ),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => 'https://m.me/yourpage or yourpage',
                'section'     => 'chat',
            ],
            'chat_whatsapp'         => [
                'label'       => __( 'WhatsApp Number', 'reseller-management' ),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => '8801XXXXXXXXX',
                'section'     => 'chat',
            ],
            'chat_call'             => [
                'label'       => __( 'Call Number', 'reseller-management' ),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => '+8801XXXXXXXXX',
                'section'     => 'chat',
            ],
            'chat_whatsapp_message' => [
                'label'       => __( 'WhatsApp Pre-filled Message', 'reseller-management' ),
                'type'        => 'text',
                'default'     => '',
                'placeholder' => __( 'Hello! I need help.', 'reseller-management' ),
                'section'     => 'chat',
            ],
        ];
    }

    /**
     * Resolved contact / social / chat settings.
     *
     * @param bool $with_fallbacks Apply email/website/phone fallbacks for frontend display.
     *
     * @return array<string, string>
     */
    public static function get_contact_settings( $with_fallbacks = true ) {
        $settings = get_option( 'rm_settings', [] );
        $out      = [];

        foreach ( self::get_contact_field_defs() as $key => $field ) {
            $raw = $settings[ $key ] ?? $field['default'];

            if ( 'checkbox' === $field['type'] ) {
                $out[ $key ] = ( 'yes' === $raw ) ? 'yes' : 'no';
                continue;
            }

            if ( 'email' === $field['type'] ) {
                $out[ $key ] = sanitize_email( (string) $raw );
                continue;
            }

            if ( 'url' === $field['type'] ) {
                $out[ $key ] = esc_url_raw( (string) $raw );
                continue;
            }

            if ( 'textarea' === $field['type'] ) {
                $out[ $key ] = sanitize_textarea_field( (string) $raw );
                continue;
            }

            $out[ $key ] = sanitize_text_field( (string) $raw );
        }

        if ( $with_fallbacks ) {
            if ( '' === $out['contact_email'] ) {
                $out['contact_email'] = sanitize_email( (string) get_option( 'admin_email', '' ) );
            }
            if ( '' === $out['contact_website'] ) {
                $out['contact_website'] = home_url( '/' );
            }
            if ( '' === $out['chat_call'] && '' !== $out['contact_phone'] ) {
                $out['chat_call'] = $out['contact_phone'];
            }
            if ( '' === $out['chat_whatsapp'] && '' !== $out['contact_phone'] ) {
                $out['chat_whatsapp'] = $out['contact_phone'];
            }
        }

        return $out;
    }

    /**
     * Sanitize contact/social/chat fields from settings POST.
     *
     * @param array $post Unslashed POST data.
     *
     * @return array<string, string>
     */
    public static function sanitize_contact_from_post( array $post ) {
        $out = [];

        foreach ( self::get_contact_field_defs() as $key => $field ) {
            if ( 'checkbox' === $field['type'] ) {
                $out[ $key ] = isset( $post[ $key ] ) ? 'yes' : 'no';
                continue;
            }

            $raw = $post[ $key ] ?? '';

            if ( 'email' === $field['type'] ) {
                $out[ $key ] = sanitize_email( (string) $raw );
                continue;
            }

            if ( 'url' === $field['type'] ) {
                $out[ $key ] = esc_url_raw( (string) $raw );
                continue;
            }

            if ( 'textarea' === $field['type'] ) {
                $out[ $key ] = sanitize_textarea_field( (string) $raw );
                continue;
            }

            $out[ $key ] = sanitize_text_field( (string) $raw );
        }

        return $out;
    }

    /**
     * Digits-only phone for tel:/wa.me links.
     *
     * @param string $phone Raw phone.
     *
     * @return string
     */
    public static function normalize_phone_digits( $phone ) {
        $digits = preg_replace( '/\D+/', '', (string) $phone );
        // Bangladesh local mobiles: 01XXXXXXXXX → 8801XXXXXXXXX
        if ( preg_match( '/^0\d{10}$/', $digits ) ) {
            $digits = '88' . $digits;
        }

        return $digits;
    }

    /**
     * Build Messenger chat URL from page URL or username.
     *
     * @param string $value Raw setting.
     *
     * @return string
     */
    public static function get_messenger_url( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '#^https?://#i', $value ) ) {
            return esc_url( $value );
        }

        $slug = preg_replace( '/[^a-zA-Z0-9._-]/', '', $value );

        return $slug ? esc_url( 'https://m.me/' . $slug ) : '';
    }

    /**
     * Build WhatsApp click-to-chat URL.
     *
     * @param string $phone   Phone number.
     * @param string $message Optional prefilled message.
     *
     * @return string
     */
    public static function get_whatsapp_url( $phone, $message = '' ) {
        $digits = self::normalize_phone_digits( $phone );
        if ( '' === $digits ) {
            return '';
        }

        $url     = 'https://wa.me/' . $digits;
        $message = trim( (string) $message );
        if ( '' !== $message ) {
            $url = add_query_arg( 'text', $message, $url );
        }

        return esc_url( $url );
    }

    /**
     * Build tel: link from phone.
     *
     * @param string $phone Phone number.
     *
     * @return string
     */
    public static function get_tel_url( $phone ) {
        $clean = preg_replace( '/[^\d+]/', '', (string) $phone );
        $clean = ltrim( (string) $clean, '+' );
        if ( '' === $clean ) {
            return '';
        }

        return 'tel:+' . self::normalize_phone_digits( $clean );
    }

    /**
     * Active live-chat channels for the floating widget.
     *
     * @return array<int, array{id: string, label: string, url: string, class: string}>
     */
    public static function get_live_chat_channels() {
        $c = self::get_contact_settings();

        if ( 'yes' !== ( $c['chat_enabled'] ?? 'no' ) ) {
            return [];
        }

        $channels = [];

        $messenger = self::get_messenger_url( $c['chat_messenger'] ?? '' );
        if ( $messenger ) {
            $channels[] = [
                'id'    => 'messenger',
                'label' => __( 'Messenger', 'reseller-management' ),
                'url'   => $messenger,
                'class' => 'rm-chat-messenger',
            ];
        }

        $whatsapp = self::get_whatsapp_url( $c['chat_whatsapp'] ?? '', $c['chat_whatsapp_message'] ?? '' );
        if ( $whatsapp ) {
            $channels[] = [
                'id'    => 'whatsapp',
                'label' => __( 'WhatsApp', 'reseller-management' ),
                'url'   => $whatsapp,
                'class' => 'rm-chat-whatsapp',
            ];
        }

        $call = self::get_tel_url( $c['chat_call'] ?? '' );
        if ( $call ) {
            $channels[] = [
                'id'    => 'call',
                'label' => __( 'Call', 'reseller-management' ),
                'url'   => $call,
                'class' => 'rm-chat-call',
            ];
        }

        return $channels;
    }
}
