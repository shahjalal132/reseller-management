### Step-by-Step Process Docs for Cursor IDE (PHP + jQuery Stack)

#### **Phase 1: Authentication & Registration (Frontend)**
1. **Registration Shortcode:** Create `[reseller_registration]`. Render an HTML form with fields: Name, Email, Phone, Business Name, Facebook URL, Website URL, NID Front Image (file input), NID Back Image (file input), Password, Confirm Password.
2. **AJAX Form Submission:** Write a jQuery script to submit this form via AJAX (`wp_ajax_nopriv_`). Include basic frontend validation (passwords match, required fields).
3. **PHP Registration Handler:**
    * Handle the AJAX request securely using nonces.
    * Upload NID images securely using `media_handle_upload()` and store them in the WordPress media library.
    * Create the user via `wp_create_user()`.
    * Assign the custom `reseller` role to the user.
    * Save the extra form fields and the Image attachment IDs as user meta (`wp_usermeta`).
    * Set user meta `_reseller_status` to `pending`.
4. **Login Restriction:** Hook into `wp_authenticate_user`. If the user attempting to log in has the `reseller` role, check their `_reseller_status`. If it is `pending` or if they have a future `_reseller_banned_until` timestamp, return a `WP_Error` and block the login.

#### **Phase 2: Admin Backend - Reseller Management**
1. **Menu Registration:** Add a top-level WP Admin menu called "Reseller Hub".
2. **Reseller List Table:** Extend the native WP `WP_List_Table` class to display all users with the `reseller` role. Include columns: Name, Business, Status, and Current Balance (calculated from the ledger).
3. **Row Actions:** Add row actions for "Approve", "Ban/Unban", and "View Profile".
4. **Profile View Page:** Create a sub-page displaying the reseller's details. Render the NID front/back images so the admin can verify them. Provide buttons to change the reseller's status (`pending`, `approved`, `rejected`).
5. **Ban Logic:** Add a date-picker UI on the profile page to ban a user until a specific date. Save this timestamp to `_reseller_banned_until` in user meta.

#### **Phase 3: Frontend Dashboard - Base Layout & Routing**
1. **Dashboard Shortcode:** Create `[reseller_dashboard]`.
2. **Access Control:** If the user is not logged in or their `_reseller_status` is not `approved`, redirect them to the login/registration page or show an error message.
3. **Minimal UI Layout:** Intercept the page rendering using the `template_include` hook. Force the page containing the shortcode to use a custom PHP template from within the plugin (e.g., `templates/dashboard-layout.php`) that removes the standard theme header/footer to create an app-like feel.
4. **Tab Routing:** Create a sidebar UI with navigation links: Dashboard, Orders, Products, Account, Settings, Customers. Use the URL parameter `$_GET['tab']` to dynamically `include` the corresponding PHP partials into the main content area.

#### **Phase 4: Dashboard - Products & Customers Modules**
1. **Products Tab (`products.php`):**
    * Query all published WooCommerce products.
    * Display them in a clean grid.
    * Show the Regular Price and a custom meta field: `_reseller_recommended_price` (ensure to register this field in the WC backend product edit screen).
    * Add a "Download Image" button that uses the HTML5 `download` attribute or a PHP script to force the download of the product's featured image.
2. **Customers Tab (`customers.php`):**
    * Query standard WooCommerce orders where the meta key `_assigned_reseller_id` matches the current logged-in user's ID.
    * Extract unique customer data (Billing Name, Billing Email, Billing Phone) from these orders and display them in a datatable.

#### **Phase 5: Dashboard - Order Management**
1. **Add New Order UI:** Create a form inside the Orders tab where the reseller inputs Customer Details (Name, Phone, Address) and selects Products (use a jQuery Select2 or AJAX autocomplete dropdown querying WC products).
2. **AJAX Order Creation:** Submit the order via jQuery AJAX. On the PHP side, programmatically create a WooCommerce Order using `wc_create_order()`. 
    * Set the billing/shipping info based on the customer details.
    * Add the selected products to the order.
    * Set the order status to `processing` or `on-hold`.
    * Most importantly: Add the meta data `update_meta_data('_assigned_reseller_id', $current_user_id)` to link it to the reseller.
3. **Order List UI:** Fetch orders assigned to the current reseller using `wc_get_orders()`. Display the Order ID, Customer Name, Status, Total, and Reseller Commission amount in an HTML table.

#### **Phase 6: The Financial Engine (Ledger & Hooks)**
1. **Commission Logic Setup:** Add a custom pricing field to WooCommerce products in the admin panel for `_reseller_commission_amount`.
2. **Order Completed Hook:** Hook into `woocommerce_order_status_completed`. If the order has an `_assigned_reseller_id`:
    * Calculate the total commission based on the items in the order.
    * Check for idempotency (query `wp_reseller_ledger` to ensure this specific `order_id` hasn't already been credited).
    * Insert a row into the `wp_reseller_ledger` custom table with the type `commission_credit`.
3. **Order Refunded Hook:** Hook into `woocommerce_order_status_refunded`. If the order is assigned to a reseller:
    * Insert a row into `wp_reseller_ledger` with the type `shipping_debit` to deduct the shipping penalty from their balance.
4. **Dashboard Widget:** On the main Dashboard tab, run a SQL query: `SELECT SUM(amount) FROM wp_reseller_ledger WHERE reseller_id = %d` to display the Reseller's Current Balance.
5. **Charts:** Use a PHP query to aggregate the ledger data (profits) grouped by month. Pass this data to the frontend using `wp_localize_script` and render a Monthly Profit Chart using Chart.js.

#### **Phase 7: Withdrawals & Settings Tab**
1. **Account Tab (Frontend):**
    * Query `wp_reseller_ledger` and display a paginated table of the Transaction History (Debits/Credits).
    * Create a "Request Withdrawal" HTML form. The user selects a payment method (e.g., Bank/Mobile), enters their account details, and specifies an amount.
2. **Withdrawal Processing (AJAX):**
    * Validate the requested amount against their current balance (`SUM(amount)` from the ledger).
    * Insert a `pending` record into the `wp_reseller_withdrawals` table.
    * *Crucial Step:* Immediately insert a negative `withdrawal_debit` record into `wp_reseller_ledger` to deduct the balance and prevent double-spending while the request is pending.
3. **Admin Withdrawals Management:** Create an Admin submenu under "Reseller Hub" to view pending withdrawals. The admin reviews the details, manually sends the money outside of WP, and then clicks a "Mark as Paid" button to change the status in `wp_reseller_withdrawals` to `completed`.
4. **Settings Tab:** Create a simple frontend form for the reseller to update their basic profile info and change their password (using `wp_set_password`) via AJAX.



# Database design plan.

### 1. Custom Database Tables (The Financial Engine)

You should create these tables during the plugin activation hook using WordPress's `dbDelta()` function.

#### Table A: `wp_reseller_ledger`
This table acts as a double-entry style ledger. We never "update" a user's balance directly; instead, we `SUM()` the `amount` column in this table to get their exact current balance.

**Schema (PHP `dbDelta` format for Cursor):**
```php
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$ledger_table = $wpdb->prefix . 'reseller_ledger';

$sql_ledger = "CREATE TABLE $ledger_table (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    reseller_id bigint(20) unsigned NOT NULL,
    order_id bigint(20) unsigned DEFAULT NULL,
    type varchar(50) NOT NULL,
    amount decimal(10,2) NOT NULL,
    description text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY  (id),
    KEY reseller_id (reseller_id),
    KEY order_id (order_id)
) $charset_collate;";
```

**Column Breakdown:**
*   `reseller_id`: Foreign key linked to `wp_users.ID`.
*   `order_id`: Foreign key linked to the WooCommerce Order ID (nullable, as withdrawals won't have an order ID).
*   `type`: The type of transaction. Enum/String values: 
    *   `commission_credit` (Positive amount)
    *   `shipping_debit` (Negative amount for returned orders)
    *   `withdrawal_debit` (Negative amount when they request a payout)
*   `amount`: The monetary value (e.g., `150.00` or `-60.00`).
*   `description`: Human-readable context (e.g., "Commission for Order #1042").

---

#### Table B: `wp_reseller_withdrawals`
This table tracks the payout requests made by the resellers. 

**Schema (PHP `dbDelta` format for Cursor):**
```php
$withdrawals_table = $wpdb->prefix . 'reseller_withdrawals';

$sql_withdrawals = "CREATE TABLE $withdrawals_table (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    reseller_id bigint(20) unsigned NOT NULL,
    amount decimal(10,2) NOT NULL,
    payment_method varchar(50) NOT NULL,
    account_details text NOT NULL,
    status varchar(20) DEFAULT 'pending' NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY  (id),
    KEY reseller_id (reseller_id),
    KEY status (status)
) $charset_collate;";
```

**Column Breakdown:**
*   `amount`: The requested withdrawal amount (must be positive).
*   `payment_method`: e.g., 'bKash', 'Nagad', 'Bank Transfer'.
*   `account_details`: A JSON encoded string or plain text containing the phone number or bank account info.
*   `status`: `pending`, `processing`, `completed`, `rejected`.

---

### 2. Standard WordPress / WooCommerce Meta Data Mapping

Instead of creating new tables for profiles, products, and orders, we will inject custom meta keys into the existing architecture.

#### A. Reseller Profiles (`wp_usermeta`)
When a user registers, they are inserted into `wp_users` with the role `reseller`. Their form data is stored in `wp_usermeta` using these exact keys:

*   `_reseller_status` : string (`pending`, `approved`, `rejected`, `banned`)
*   `_reseller_phone` : string
*   `_reseller_business_name` : string
*   `_reseller_fb_url` : string
*   `_reseller_web_url` : string
*   `_reseller_nid_front_id` : integer (Attachment ID from the WP Media Library)
*   `_reseller_nid_back_id` : integer (Attachment ID from the WP Media Library)
*   `_reseller_banned_until` : integer (UNIX timestamp. If current time < timestamp, user is banned).

#### B. WooCommerce Products (`wp_postmeta` / WC Product Meta)
You will add these custom fields to the WooCommerce Product edit screen so the Admin can configure reseller rules per product:

*   `_reseller_recommended_price` : decimal (What the reseller should sell it for).
*   `_reseller_commission_amount` : decimal (Fixed amount the reseller earns if this product is sold).

#### C. WooCommerce Orders (HPOS / `wp_wc_orders_meta`)
When a reseller places an order on behalf of a customer, standard WooCommerce order creation is used, but we tag the order with the reseller's ID:

*   `_assigned_reseller_id` : integer (Links the WooCommerce order to the reseller who placed it).

### Summary of How They Connect:

1. **Dashboard Login:** Queries `wp_users` for the `reseller` role and checks `wp_usermeta` for `_reseller_status = 'approved'`.
2. **Placing an Order:** Inserts a standard WC Order. Adds `_assigned_reseller_id` to link it to the reseller.
3. **Getting Paid:** WC Order changes to 'Completed'. System reads order items, calculates total `_reseller_commission_amount`, and `INSERT`s a row into `wp_reseller_ledger`.
4. **Showing Balance:** System runs `SELECT SUM(amount) FROM wp_reseller_ledger WHERE reseller_id = 123`.
5. **Requesting Payout:** Inserts row into `wp_reseller_withdrawals` AND immediately inserts a negative row into `wp_reseller_ledger` to lock the funds.
