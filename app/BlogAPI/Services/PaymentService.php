<?php
namespace BlogAPI\Services;

use DB;
use BlogAPI\Models\Order;
use BlogAPI\Payments\ATMPayment;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\OrderRepository;

class PaymentService
{
    protected $paymentProvider;
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function setPaymentMethod($type)
    {
        switch($type) {
            case Order::PAYMENT_METHOD_ATM:
                $this->paymentProvider = new ATMPayment();
                break;
            case Order::PAYMENT_METHOD_CVS:

                break;
            case Order::PAYMENT_METHOD_CREDITCARD:
                break;
            default:
                throw new \Exception('Payment method not exist.');
        }

        return $this;
    }

    public function setTradeData($orderNo, $amount)
    {
        $this->paymentProvider
            ->setTradeInfo($orderNo, $amount)
            ->setTradeSha();

        return $this;
    }

    public function sendTrade()
    {
        $response = $this->paymentProvider->sendRequest();

        return $response = [
            'status' => $response->getStatusCode(),
            'content' => json_decode($response->getBody()->getContents(), true)
        ];
    }
}
