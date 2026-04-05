<?php
require_once __DIR__ . '/wp-load.php';

$users = get_users([
    'role' => 'reseller',
]);

foreach ($users as $user) {
    $is_approved = get_user_meta($user->ID, '_reseller_status', true) === 'approved';
    echo "ID: {$user->ID}, Login: {$user->user_login}, Email: {$user->user_email}, Approved: " . ($is_approved ? 'Yes' : 'No') . "\n";
}
