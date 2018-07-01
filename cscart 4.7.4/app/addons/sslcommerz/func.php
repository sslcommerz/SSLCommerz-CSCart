<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;
use Tygh\Addons\Sslcommerz\Enum\PaymentStatus;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Install SSLCommerz payment processor
 */
function fn_sslcommerz_install()
{
    $data = array (
        'processor' => 'SSLCommerz',
        'processor_script' => 'sslcommerz.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'sslcommerz.tpl',
        'callback' => 'N',
        'type' => 'P',
        'addon' => 'sslcommerz'
    );

    db_replace_into('payment_processors', $data);
}

/**
 * Uninstall SSLCommerz payment processor
 */
function fn_sslcommerz_uninstall()
{
    db_query('DELETE FROM ?:payment_descriptions'
        . ' WHERE payment_id IN'
            . ' (SELECT payment_id'
            . ' FROM ?:payments'
            . ' WHERE processor_id IN'
                . ' (SELECT processor_id'
                . ' FROM ?:payment_processors'
                . ' WHERE processor_script = ?s))',
        'sslcommerz.php'
    );

    db_query('DELETE FROM ?:payment_processors WHERE processor_script = ?s', 'sslcommerz.php');
    db_query('DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s)', 'sslcommerz.php');
}

/**
 * Checks if the currency supports by payment processor
 *
 * @param array    $processor_data    Payment processor data
 *
 * @return boolean    True if success, otherwise false
 */
function fn_sslcommerz_is_currency_supports(array $processor_data)
{

    if (empty($processor_data['processor_params']['currency'])) {
        return false;
    }
    $currency_settings = Registry::get('currencies.' . $processor_data['processor_params']['currency']);

    return !empty($currency_settings);
}

/**
 * Finish payment and place order
 *
 * @param int      $order_id          Order id
 * @param array    $pp_response       Response payment data
 */
function fn_sslcommerz_finish_payment($order_id, array $pp_response)
{
    if ($order_id) {
        if (fn_check_payment_script('sslcommerz.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, true);
            fn_order_placement_routines('route', $order_id);
        }
    }
}

/**
 * Convert SSLCommerz to CS-Cart payment status
 *
 * @param string    $payment_status    SSLCommerz payment status
 *
 * @return string    CS-Cart payment status
 */
function fn_sslcommerz_get_status($payment_status)
{
    $status = 'F';

    if ($payment_status) {
        switch ($payment_status) {
            case PaymentStatus::SSLCOMMERZ_VALID_STATUS: $status = 'P'; break;
            case PaymentStatus::SSLCOMMERZ_FAILED_STATUS: $status = 'F'; break;
            case PaymentStatus::SSLCOMMERZ_CANCELLED_STATUS: $status = 'N'; break;
            case PaymentStatus::SSLCOMMERZ_VALIDATED_STATUS: $status = 'P'; break;
            case PaymentStatus::SSLCOMMERZ_INVALID_TRANSACTION_STATUS: $status = 'F'; break;
        }
    }

    return $status;
}

/**
 * Create post data for SSLCommerz request
 *
 * @param array    $order_info        Order info
 * @param array    $processor_data    Processor data
 *
 * @return array
 */
function fn_sslcommerz_merge_order_data($order_info, $processor_data)
{
    $post_data = array();

    if ($order_info && $processor_data) {
        $lang_code = Registry::get('settings.Appearance.backend_default_language');
        $cus_state = fn_get_state_name($order_info['b_state'], $order_info['b_country'], $lang_code);
        $ship_state = fn_get_state_name($order_info['s_state'], $order_info['s_country'], $lang_code);
        $redurect_url = fn_url('payment_notification.sslcommerz');

        // Integration Required Parameters
        $post_data['store_id'] = $processor_data['processor_params']['store_id'];
        $post_data['store_passwd'] = $processor_data['processor_params']['store_password'];
        $post_data['total_amount'] = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
        $post_data['currency'] = $processor_data['processor_params']['currency'];
        $post_data['tran_id'] = trim($processor_data['processor_params']['order_prefix']) . $order_info['order_id'];
        $post_data['success_url'] = $post_data['fail_url'] = $post_data['cancel_url'] = $redurect_url;

        // EMI info
        $post_data['emi_option'] = '0';

        // Customer info
        $post_data['cus_name'] = trim($order_info['b_firstname'] . ' ' . $order_info['b_lastname']);
        $post_data['cus_email'] = $order_info['email'];
        $post_data['cus_add1'] = $order_info['b_address'];
        $post_data['cus_add2'] = $order_info['b_address_2'];
        $post_data['cus_city'] = $order_info['b_city'];
        $post_data['cus_state'] = $cus_state ? $cus_state : $order_info['b_state'];
        $post_data['cus_postcode'] = $order_info['b_zipcode'];
        $post_data['cus_country'] = fn_get_country_name($order_info['b_country'], $lang_code);
        $post_data['cus_phone'] = !empty($order_info['b_phone']) ? $order_info['b_phone'] : $order_info['phone'];
        $post_data['cus_fax'] = $order_info['fax'];

        // Shipment info
        $post_data['ship_name'] = trim($order_info['s_firstname'] . ' ' . $order_info['s_lastname']);
        $post_data['ship_add1 '] = $order_info['s_address'];
        $post_data['ship_add2'] = $order_info['s_address_2'];
        $post_data['ship_city'] = $order_info['s_city'];
        $post_data['ship_state'] = $ship_state ? $ship_state : $order_info['s_state'];
        $post_data['ship_postcode'] = $order_info['s_zipcode'];
        $post_data['ship_country'] = fn_get_country_name($order_info['s_country'], $lang_code);

        // Cart parametrs
        $products_data = array();

        foreach ($order_info['products'] as $product) {
            $products_data[] = array('product' => $product['product'], 'amount' => fn_format_price_by_currency($product['price'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']));
        }

        $post_data['cart'] = json_encode($products_data);
        $post_data['product_amount'] = fn_format_price_by_currency($order_info['subtotal'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
        $post_data['vat'] = fn_format_price_by_currency($order_info['tax_subtotal'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
        $post_data['discount_amount'] = fn_format_price_by_currency($order_info['subtotal_discount'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
        $post_data['convenience_fee'] = fn_format_price_by_currency($order_info['payment_surcharge'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
    }
    
//print_r($post_data);    
//die();
    return $post_data;
}

/**
 * Get SSLCommerz processor data
 *
 * @param int    $store_id    SSLCommerz store id
 *
 * @return array
 */
function fn_sslcommerz_get_processor_data_by_store_id($store_id)
{
    if ($store_id) {
        $payment_processor_list = db_get_array('SELECT processor_params FROM ?:payments AS p LEFT JOIN ?:payment_processors AS pp ON pp.processor_id = p.processor_id WHERE pp.processor_script = ?s', 'sslcommerz.php');
        foreach ($payment_processor_list as $payment_processor) {
            $payment_processor = unserialize($payment_processor['processor_params']);
            if (isset($payment_processor['store_id']) && $payment_processor['store_id'] == $store_id) {
                $processor_data = $payment_processor;
                break;
            }
        }
    }

    return isset($processor_data) ? $processor_data : array();
}

/**
 * SSLCommerz hash validation
 *
 * @param string    $store_password    SSLCommerz payment status
 *
 * @return boolean
 */
function fn_sslcommerz_ipn_hash_varify($store_password = '', $response)
{
    if (isset($response) && isset($response['verify_sign']) && isset($response['verify_key'])) {
        // New array declared to take value off all post

        $pre_define_key = explode(',', $response['verify_key']);

        $new_data = array();
        if (!empty($pre_define_key)) {
            foreach ($pre_define_key as $value) {
                if (isset($response[$value])) {
                    $new_data[$value] = $response[$value];
                }
            }
        }

        // Add md5 of store password
        $new_data['store_passwd'] = md5($store_password);

        // Sort the key as before
        ksort($new_data);

        $hash_string = '';
        foreach ($new_data as $key => $value) {
            $hash_string .= $key . '=' . $value . '&';
        }

        $hash_string = rtrim($hash_string, '&');

        if (md5($hash_string) == $response['verify_sign']) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
