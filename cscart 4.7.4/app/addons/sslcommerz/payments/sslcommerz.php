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

if (!defined('PAYMENT_NOTIFICATION')) {
    //print_r($processor_data);
   // die();
    if (fn_sslcommerz_is_currency_supports($processor_data)) {

        $post_data = fn_sslcommerz_merge_order_data($order_info, $processor_data);

$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment processor62: \r\n" . TIME . "\r\n" . ' ORDER ID: ' . $order_info['order_id'] . ' POST DATA: ' . serialize($post_data)."\r\n" .'Processor_data'.serialize($processor_data['processor_params']['mode']);
$test = fwrite($fp, $debug);
fclose($fp);
        $curlport_ssl_varifypeer = Registry::get('config.current_host') != 'localhost';
        //$direct_api_url = $processor_data['mode'] == 'live' ? SSLCOMMERZ_LIVE_URL : SSLCOMMERZ_TEST_URL;
        $direct_api_url = $processor_data['processor_params']['mode'] == 'live' ? SSLCOMMERZ_LIVE_URL : SSLCOMMERZ_TEST_URL;

$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment processor52: \r\n" . TIME . "\r\n" . ' ORDER ID: ' . $order_info['order_id'] ."\r\n" .'Processor_data'.serialize($processor_data['processor_params']['mode'])."\r\n" .'directapirul:'.$direct_api_url;
$test = fwrite($fp, $debug);
fclose($fp);

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url );
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $curlport_ssl_varifypeer); // Keep it false if you run from local PC

        $content = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == SSLCOMMERZ_CURL_STATUS_OK && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerz_response = $content;
        } else {
            curl_close($handle);

$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment processor72: \r\n" . TIME . "\r\n" . ' ORDER ID: ' . $order_info['order_id'] . ' POST DATA: FALIED STATUS1'. "\r\n" .'SSL Response:'.serialize($sslcommerz_response);
$test = fwrite($fp, $debug);
fclose($fp);
            fn_sslcommerz_finish_payment($order_info['order_id'], array('order_status' => fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS), 'reason_text' => __('addons.sslcommerz.failed_connect')));
        }

        // Parse the json response
        $sslcz = json_decode($sslcommerz_response, true);

        if (!empty($sslcz['GatewayPageURL'])) {
$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment processor82: \r\n" . TIME . "\r\n" . ' ORDER ID: ' . $order_info['order_id'] . ' POST DATA: EXIT';
$test = fwrite($fp, $debug);
fclose($fp);
            echo "<meta http-equiv='refresh' content='0; url=".$sslcz['GatewayPageURL']."'>";
            exit;
        } else {
            $failed_reason = !empty($sslcz['failedreason']) ? $sslcz['failedreason'] : '';

$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment processor92: \r\n" . TIME . "\r\n" . ' ORDER ID: ' . $order_info['order_id'] . ' POST DATA: FALIED STATUS2';
$test = fwrite($fp, $debug);
fclose($fp);
            fn_sslcommerz_finish_payment($order_info['order_id'], array('order_status' => fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS), 'reason_text' => $failed_reason));
        }
    } else {
        fn_set_notification('E', __('error'), __('addons.sslcommerz.currency_not_support'));
        fn_order_placement_routines('checkout_redirect');
    }
}
