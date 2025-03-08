<?php
/**
 * Plugin Name: Disable Emails on Staging Site
 * Plugin URI:  https://github.com/Zenithcoder/
 * Description: Disables emails on a WordPress staging site identified by having "staging" in the URL (case-insensitive).
 * Version: 1.0
 * Author: https://github.com/Zenithcoder/
 * Author URI: https://github.com/Zenithcoder/
 * License: GPLv2 or later
 * Text Domain: disable-emails-staging
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Check if it's a staging site
 *
 * @return bool True if staging site, False otherwise
 */
function is_it_staging_site()
{
    $get_site_url = get_site_url();
    $pattern = '/\bstaging\b/i';
    $isStaging = preg_match($pattern, $get_site_url);
    return $isStaging ? true : false;
}

/**
 * Disable all default WordPress emails
 *
 * @param array $args The email arguments
 * @return array The modified email arguments
 */
add_filter('wp_mail', 'disabling_emails', 10, 1);
function disabling_emails($args)
{
    if(!is_it_staging_site()) {
        return $args;
    }
    unset($args['to']);
    return $args;
}

/**
 * Disable emails sent through PHPMailer
 *
 * @param object $phpmailer The PHPMailer object
 */
add_action('phpmailer_init', 'my_action');
function my_action($phpmailer)
{
    if(!is_it_staging_site()) {
        return;
    }
    $phpmailer->ClearAllRecipients();
}

/**
 * Unhook and remove WooCommerce default emails.
 */
add_action('woocommerce_email', 'unhook_those_pesky_emails');
function unhook_those_pesky_emails($email_class)
{
    if(!is_it_staging_site()) {
        return;
    }
    /**
     * Hooks for sending emails during store events
     **/
    remove_action('woocommerce_low_stock_notification', array( $email_class, 'low_stock' ));
    remove_action('woocommerce_no_stock_notification', array( $email_class, 'no_stock' ));
    remove_action('woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ));

    // New order emails
    remove_action('woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ));

    // Processing order emails
    remove_action('woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ));
    remove_action('woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ));

    // Completed order emails
    remove_action('woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ));

    // Note emails
    remove_action('woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ));
}
