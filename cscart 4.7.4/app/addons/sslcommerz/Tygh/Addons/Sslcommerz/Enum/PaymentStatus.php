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

namespace Tygh\Addons\Sslcommerz\Enum;

class PaymentStatus
{
    const SSLCOMMERZ_VALID_STATUS = 'VALID';
    const SSLCOMMERZ_FAILED_STATUS = 'FAILED';
    const SSLCOMMERZ_CANCELLED_STATUS = 'CANCELLED';
    const SSLCOMMERZ_VALIDATED_STATUS = 'VALIDATED';
    const SSLCOMMERZ_INVALID_TRANSACTION_STATUS = 'INVALID_TRANSACTION';
    const SSLCOMMERZ_UNATTEMPTED_STATUS = 'UNATTEMPTED';
    const SSLCOMMERZ_EXPIRED_STATUS = 'EXPIRED';
}
