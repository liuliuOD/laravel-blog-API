<?php
namespace BlogAPI\Services;

use DB;
use BlogAPI\Models\Order;
use BlogAPI\Payments\ATMPayment;
use BlogAPI\Payments\LinePayment;
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
            case Order::PAYMENT_METHOD_LINE:
                $this->paymentProvider = new LinePayment();
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

    public function setTradeData($orderInfo)
    {
        $this->paymentProvider->setTradeData($orderInfo);

        return $this;
    }

    public function setConfirmData($orderInfo, $transaction_id, $type)
    {
        $this->paymentProvider->setConfirmData($orderInfo, $transaction_id, $type);

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

    public function testLine($params = [])
    {
        $this->paymentProvider = new LinePayment();

        return $this->paymentProvider->test($params);
    }
}
