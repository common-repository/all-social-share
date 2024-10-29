<?php
/**
 * Plugin Name:     All Social Share
 * Description:     This is just test plugin
 * Version:         1.0.0
 * License:         GPLv2 or later
 * Author:          Kirtikumar Solanki
 * Text Domain:     assp-social-share
 * Domain Path:     /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('ASSP_SOCIAL_SHARE_VERSION', '1.0.0');

require_once plugin_dir_path(__FILE__) . 'includes/class-social-share.php';

// Correct Placement for Activation Hook
function assp_social_share_activate() {
    // Activation code here, like initializing default plugin options
}

// Correct Placement for Deactivation Hook
function assp_social_share_deactivate() {
    // Deactivation code here, like cleaning up options or temporary data
}

// Hooking up the activation and deactivation functions
register_activation_hook(__FILE__, 'assp_social_share_activate');
register_deactivation_hook(__FILE__, 'assp_social_share_deactivate');

// Instantiate the Social_Share class
$social_share = new ASSP_Social_Share();

// Initialize the plugin
$social_share->init();