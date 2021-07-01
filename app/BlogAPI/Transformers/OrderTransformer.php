<?php

namespace BlogAPI\Transformers;

use BlogAPI\Models\Order;

class OrderTransformer
{
    public static function toDatabase($amount, $orderName, $params)
    {
        $userId = request()->user()->id;
        $orderNum = 'OD_' . $userId . time();

        $row = [
            'user_id' => $userId,
            'order_no' => $orderNum,
            'trade_no' => $orderNum,
            'total' => $amount,
            'paid_status' => Order::PAY_STATUS_NOT_PAID,
            'invoice_status' => Order::INVOICE_STATUS_PENDING,
            'order_name' => $orderName,
        ];

        if ($params['payment_method'] == Order::PAYMENT_METHOD_ATM) {
            $row['bank_type'] = $params['bank_type'];
        }

        return $row;
    }
}