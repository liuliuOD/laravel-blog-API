<?php

namespace BlogAPI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    const PAY_STATUS_NOT_PAID = 'NOT_PAID';

    const INVOICE_STATUS_PENDING = 'PENDING';

    const PAYMENT_METHOD_ATM = 'ATM';
    const PAYMENT_METHOD_CVS = 'CVS';
    const PAYMENT_METHOD_CREDITCARD = 'CREDIT_CARD';

    protected $guarded = [];
}