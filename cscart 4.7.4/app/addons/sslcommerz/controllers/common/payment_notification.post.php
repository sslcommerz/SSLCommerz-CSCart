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

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{

    if ($mode == 'sslcommerz') 
    {
        $response = $_REQUEST;
        $store_id = !empty($response['store_id']) ? $response['store_id'] : '';
        $processor_data = fn_sslcommerz_get_processor_data_by_store_id($store_id);
        $order_info = !empty($response['tran_id']) ? fn_get_order_info($response['tran_id']) : array();
      
          $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                            $debug = " \r\n\r\n Payment controller 9: \r\n" . TIME . "\r\n" . 'Processor Data : ' . serialize($processor_data)."\r\n".'Order details:'.serialize($order_info);
                            $test = fwrite($fp, $debug);
                            fclose($fp); 
      if($order_info['status']=='N')
      {
          
      
            $_order_id = isset($response['tran_id']) ? str_replace($processor_data['order_prefix'], '', $response['tran_id']) : '';
    
            if ($response && !empty($response['status'])) 
            {
                if (isset($response['amount']) && isset($order_info['total']) && $response['amount'] == $order_info['total'])
                 {
                    if (!in_array($response['status'], array(PaymentStatus::SSLCOMMERZ_CANCELLED_STATUS, PaymentStatus::SSLCOMMERZ_EXPIRED_STATUS, PaymentStatus::SSLCOMMERZ_UNATTEMPTED_STATUS))) 
                        {
                            if ($response['status'] == PaymentStatus::SSLCOMMERZ_VALID_STATUS || $response['status'] == PaymentStatus::SSLCOMMERZ_VALIDATED_STATUS) 
                            {
                                $store_password = isset($processor_data['store_password']) ? $processor_data['store_password'] : '';
                                
                                if($response['currency']=='')
                                $response['currency']=$processor_data['currency'];
                                
                                 $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                 $debug = " \r\n\r\n Payment controller10: \r\n" . TIME . "\r\n" . 'Response : ' . serialize($response). "\r\n";
                                 $test = fwrite($fp, $debug);
                                 fclose($fp);
                                
                                if (fn_sslcommerz_ipn_hash_varify($store_password, $response)) 
                                {
                                $val_id = isset($response['val_id']) ? urlencode($response['val_id']) : '';
                                
                                // $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                // $debug = " \r\n\r\n Payment controller10: \r\n" . TIME . "\r\n" . 'Processor Mode : ' . $processor_data['mode']. "\r\n" . ' Live URL : ' . SSLCOMMERZ_VALIDATOR_LIVE_URL. "\r\n" . ' Test URL : ' . SSLCOMMERZ_VALIDATOR_TEST_URL;
                                // $test = fwrite($fp, $debug);
                                // fclose($fp);            
                                
                                if($processor_data['mode'] == "live")
                                {
                                    $validate_url = SSLCOMMERZ_VALIDATOR_LIVE_URL;
                                }
                                else
                                {
                                    $validate_url = SSLCOMMERZ_VALIDATOR_TEST_URL;
                                }
                                
                               // $validate_url = $processor_data['processor_params']['mode'] == 'live' ? SSLCOMMERZ_VALIDATOR_LIVE_URL : SSLCOMMERZ_VALIDATOR_TEST_URL;
    
                                $requested_url = ($validate_url.'?val_id=' . $val_id . '&store_id=' . urlencode($store_id) . '&store_passwd=' . $store_password . '&v=1&format=json');
                                $curlport_ssl_host = Registry::get('config.current_host') != 'localhost';
                                     
                                     
    
                                $handle = curl_init();
                                curl_setopt($handle, CURLOPT_URL, $requested_url);
                                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $curlport_ssl_host); // Keep it false if you run from local PC
                                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $curlport_ssl_host); // Keep it false if you run from local PC
                                $result = curl_exec($handle);
                                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    
                                     
                                $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                $debug = " \r\n\r\n Payment controller11: \r\n" . TIME . "\r\n" . ' Requested_url : ' . serialize($requested_url). "\r\n" . ' Result : ' . serialize($result);
                                $test = fwrite($fp, $debug);
                                fclose($fp);            
    
                                    if ($code == SSLCOMMERZ_CURL_STATUS_OK && !(curl_errno($handle))) 
                                    {
                                        $result = json_decode($result);
                                        $status = fn_sslcommerz_get_status($result->status);
                                
                                     
                                        $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                        $debug = " \r\n\r\n Payment controller1: \r\n" . TIME . "\r\n" . ' STATUS 1: ' . $status . ' ORDER ID: ' . $_order_id;
                                        $test = fwrite($fp, $debug);
                                        fclose($fp);
                                    } 
                                    else 
                                    {
                                        $status = fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS);
                                        $reason_text = __('addons.sslcommerz.failed_connect');
                                     
                                    $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                    $debug = " \r\n\r\n Payment controller2: \r\n" . TIME . "\r\n" . ' STATUS 2: ' . $status . ' ORDER ID: ' . $_order_id;
                                    $test = fwrite($fp, $debug);
                                    fclose($fp);
                                    }
                            
                                } 
                                else 
                                {
                                    $status = fn_sslcommerz_get_status(SSLCOMMERZ_FAILED_STATUS);
                                    $reason_text = __('addons.sslcommerz.failed_hash_validation');
                                    
                                    $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                    $debug = " \r\n\r\n Payment controller3: \r\n" . TIME . "\r\n" . ' STATUS 3: ' . $status . ' ORDER ID: ' . $_order_id;
                                    $test = fwrite($fp, $debug);
                                    fclose($fp);    
                                }
                            } 
                            else 
                            {
                                $status = fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS);
                                $reason_text = __('addons.sslcommerz.failed_connect');
                                    
                                $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                                $debug = " \r\n\r\n Payment controller4: \r\n" . TIME . "\r\n" . ' STATUS 4: ' . $status . ' ORDER ID: ' . $_order_id;
                                $test = fwrite($fp, $debug);
                                fclose($fp);
                            }
                        } 
                        else 
                        {
                            $status = fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_CANCELLED_STATUS);
                        
                            $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                            $debug = " \r\n\r\n Payment controller5: \r\n" . TIME . "\r\n" . ' STATUS 5: ' . $status . ' ORDER ID: ' . $_order_id;
                            $test = fwrite($fp, $debug);
                            fclose($fp);
                        }
                    }
                     else 
                     {
                        $status = fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS);
                        $reason_text = __('addons.sslcommerz.failed_connect');
                        
                        $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
                        $debug = " \r\n\r\n Payment controller6: \r\n" . TIME . "\r\n" . ' STATUS 6: ' . $status . ' ORDER ID: ' . $_order_id;
                        $test = fwrite($fp, $debug);
                        fclose($fp);
                    }   
            } else {
                $status = fn_sslcommerz_get_status(PaymentStatus::SSLCOMMERZ_FAILED_STATUS);
                $reason_text = __('addons.sslcommerz.failed_connect');
    
    $fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
    $debug = " \r\n\r\n Payment controller7: \r\n" . TIME . "\r\n" . ' STATUS 7: ' . $status . ' ORDER ID: ' . $_order_id;
    $test = fwrite($fp, $debug);
    fclose($fp);
            }
        }
        $reason_text = isset($reason_text) ? $reason_text : '';
        $transaction_id = isset($response['bank_tran_id']) ? $response['bank_tran_id'] : '';
        $order_id = isset($response['tran_id']) ? str_replace($processor_data['order_prefix'], '', $response['tran_id']) : '';

$fp = fopen(DIR_ROOT . "/ssl_commerz_log.txt", "a+");
$debug = " \r\n\r\n Payment controller8: \r\n" . TIME . "\r\n" . ' RESULT: ' . $status . ' ORDER ID: ' . $order_id . ' REASON TEXT: ' . $reason_text . ' RESPONSE: ' . serialize($response);
$test = fwrite($fp, $debug);
fclose($fp);
        fn_sslcommerz_finish_payment($order_id, array('order_status' => $status, 'transaction_id' => $transaction_id, 'reason_text' => $reason_text));
        
    }

    return array(CONTROLLER_STATUS_OK);
}
