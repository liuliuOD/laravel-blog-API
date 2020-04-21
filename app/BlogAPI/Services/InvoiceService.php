<?php
namespace BlogAPI\Services;

use BlogAPI\Payments\Invoice\Ezpay;

class InvoiceService
{
    protected $ezpay;

    public function __construct(Ezpay $ezpay)
    {
        $this->ezpay = $ezpay;
    }

    public function issueInvoice($order)
    {
        return $this->ezpay
            ->setPostInfo($order)
            ->curlWork();
    }
}
