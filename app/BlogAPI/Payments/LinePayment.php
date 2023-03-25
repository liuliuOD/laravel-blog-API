<?php
namespace BlogAPI\Payments;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use BlogAPI\Payments\Client as gitClient;

class LinePayment extends AbstractPayment
{
    const ITEM_DESC = 'Pay by Line.';

    protected static $uri = [
        'request' => '/v3/payments/request',
        'confirm' => '/v3/payments/:transaction_id/confirm',
    ];

    protected $testUrl = 'https://sandbox-api-pay.line.me';
    protected $formalUrl = 'https://api-pay.line.me';
    
    protected $nonce;
    protected $linepayID;
    protected $requestUri;
    protected $linepaySecret;

    public function __construct()
    {
        $this->linepayID = env('LINEPAY_ID', '');
        $this->linepaySecret = strval(env('LINEPAY_SECRET', ''));

        $this->url = env('APP_ENV', 'local') == 'local' ? $this->testUrl : $this->formalUrl;
    }

    public function checkParams()
    {
        return [
            'url' => $this->url,
            'info' => $this->tradeInfo,
            'Id AND secret' => $this->linepayID.'||'.$this->linepaySecret,
            'sha' => $this->tradeSha,
        ];
    }

    public function setTradeInfo($orderInfo)
    {
        // 商品資訊寫死
        $this->tradeInfo = [
                'amount' => $orderInfo->total,
                'currency' => 'TWD',
                'orderId' => $orderInfo->order_no,
                'packages' => [
                    [
                        'id' => $orderInfo->id,
                        'amount' => $orderInfo->total,
                        'products' => [
                            [
                                'id' => 'PEN-B-001',
                                'name' => 'Pen Brown',
                                'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
                                'quantity' => 1,
                                'price' => $orderInfo->total
                            ]
                        ]
                    ]
                ],
                'redirectUrls' => [
                    'confirmUrl' => route('api.v1.line-notify'),
                    'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel',
                    'confirmUrlType' => 'CLIENT'
                ]
            ];

        return $this;
    }

    public function setTradeData($orderInfo, $type = 'request')
    {
        $this->requestUri = self::$uri[$type];
        $this->nonce = date('c') . uniqid('-');

        $this->setTradeInfo($orderInfo);

        $this->setTradeSHA();

        return $this;
    }

    public function setTradeSHA()
    {
        $macText = $this->linepaySecret.$this->requestUri.json_encode($this->tradeInfo).$this->nonce;

        $this->tradeSha = base64_encode(hash_hmac('sha256', $macText, $this->linepaySecret, true));

        return $this;
    }

    public function sendRequest()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $this->linepayID,
            'X-LINE-Authorization' => $this->tradeSha,
            'X-LINE-Authorization-Nonce' => $this->nonce
        ];

        $client = new Client([
            'headers' => $headers,
            'base_uri' => $this->url
        ]);

        $this->request = new Request('POST', $this->requestUri, $headers, json_encode($this->tradeInfo));
        
        return $client->send($this->request);
    }

    public function setConfirmData($orderInfo, $transaction_id, $type)
    {
        $this->requestUri = str_replace(':transaction_id', $transaction_id, self::$uri[$type]);
        $this->nonce = date('c').uniqid('-');

        $this->tradeInfo = [
            'amount' => $orderInfo->total,
            'currency' => 'TWD'
        ];

        $this->setTradeSHA();

        return $this;
    }

    public function test(array $params)
    {
        $channel = new gitClient([
            'channelId' => $this->linepayID,
            'channelSecret' => $this->linepaySecret,
            'isSandbox' => true
        ]);
    }
}
