# 🛒 WooCommerce Reseller System

A powerful, custom-built WordPress plugin that transforms a standard WooCommerce store into a fully functional multi-reseller platform. 

This plugin provides an app-like, decoupled frontend dashboard for resellers and a robust financial engine on the backend using a double-entry ledger system to track commissions, penalties, and payouts securely.

## ✨ Key Features

### 🧑‍💼 For Resellers (Frontend)
*   **Custom Registration:** Dedicated onboarding form including NID (National ID) document uploads and business details.
*   **App-like Dashboard:** A distraction-free, minimal UI dashboard completely separated from the standard WordPress admin panel.
*   **Product Catalog:** View approved store products with recommended reseller prices and one-click image downloads for marketing.
*   **Order Management:** Place orders on behalf of customers seamlessly via AJAX.
*   **Financial Wallet:** Track real-time balances, view monthly profit charts, and monitor transaction history (credits/debits).
*   **Withdrawal System:** Request payouts via preferred methods (e.g., Bank Transfer, bKash, Nagad) directly from the dashboard.
*   **Customer Directory:** Auto-generated directory of customers based on previous orders.

### 👑 For Store Admins (Backend)
*   **Reseller Hub:** A centralized dashboard to manage all reseller accounts.
*   **KYC & Verification:** Review NID documents and business details before approving or rejecting reseller applications.
*   **Account Control:** Temporarily or permanently ban resellers with date-picker logic.
*   **Commission Management:** Set custom `Recommended Price` and `Commission Amounts` on individual WooCommerce products.
*   **Automated Ledger:** Automated commission crediting on 'Completed' orders and shipping penalty debiting on 'Refunded/Returned' orders.
*   **Payout Management:** Review pending withdrawal requests, deduct balances, and mark as paid.

---

## 🛠️ Tech Stack & Architecture

*   **Core:** PHP, WordPress Plugin API, WooCommerce Core
*   **Frontend UI:** HTML/CSS, Custom PHP Page Templates (stripping standard theme headers/footers for a standalone app feel)
*   **Interactivity:** jQuery & WordPress AJAX (`wp_ajax_` / `wp_ajax_nopriv_`)
*   **Charting:** Chart.js (via aggregated PHP data)
*   **Database:** Standard WP/WC Architecture (HPOS compatible) + Custom Financial Tables.

---

## 🚀 Installation & Setup

### Prerequisites
*   WordPress 6.0+
*   WooCommerce 8.0+ (High-Performance Order Storage / HPOS supported)
*   PHP 7.4+ (8.x recommended)

### Installation Steps
1.  Clone this repository into your standard WordPress plugins directory:
    ```bash
    cd wp-content/plugins/
    git clone https://github.com/shahjalal132/reseller-management.git
    ```
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Upon activation, the plugin automatically:
    *   Creates the `reseller` user role.
    *   Generates the `wp_reseller_ledger` and `wp_reseller_withdrawals` custom tables.
4.  Create two new WordPress Pages and add the following shortcodes:
    *   **Registration Page:** Add `[reseller_registration]`
    *   **Dashboard Page:** Add `[reseller_dashboard]`

---

## 📖 Shortcodes & Usage

*   `[reseller_registration]`: Renders the multi-field registration form including NID file uploads. Access is restricted for logged-in users.
*   `[reseller_dashboard]`: The main application portal. Intercepts standard theme rendering to output the custom app UI. Restricted to logged-in users with an `approved` reseller role.

---

## 🔒 Security Notes
*   **NID Protection:** All NID uploads are handled securely via `media_handle_upload()`. Consider restricting directory access to `/wp-content/uploads/reseller-docs/` via `.htaccess` or NGINX rules to prevent public access to KYC documents.
*   **Idempotency:** The financial ledger hooks are strictly verified to prevent duplicate commissions on WooCommerce order status toggles.
*   **Data Validation:** All AJAX requests strictly utilize WordPress nonces (`wp_verify_nonce()`) and standard sanitization functions (`sanitize_text_field`, `intval`, etc.).

---
