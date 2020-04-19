<?php

namespace BlogAPI\Transformers;

use BlogAPI\Models\Order;

class OrderTransformer
{
    public static function toDatabase($amount)
    {
        $userId = request()->user()->id;
        $orderNum = 'OD_' . $userId . time();

        return [
            'user_id' => $userId,
            'order_no' => $orderNum,
            'trade_no' => $orderNum,
            'total' => $amount,
            'paid_status' => Order::PAY_STATUS_NOT_PAID,
            'invoice_status' => Order::INVOICE_STATUS_PENDING,
        ];
    }
}