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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('SSLCOMMERZ_LIVE_URL', 'https://securepay.sslcommerz.com/gwprocess/v3/api.php');
fn_define('SSLCOMMERZ_TEST_URL', 'https://sandbox.sslcommerz.com/gwprocess/v3/api.php');
fn_define('SSLCOMMERZ_VALIDATOR_LIVE_URL', 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php');
fn_define('SSLCOMMERZ_VALIDATOR_TEST_URL', 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php');

fn_define('SSLCOMMERZ_CURL_STATUS_OK', 200);
