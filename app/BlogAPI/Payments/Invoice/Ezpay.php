<?php

namespace BlogAPI\Payments\Invoice;

class Ezpay
{
    const TAX_RATE = 5;
    const VERSION = 1.4;
    const CATEGORY = 'B2C';
    const RESPOND_TYPE = 'JSON';
    const URL = 'https://inv.pay2go.com/Api/invoice_issue';
    const TEST_URL = 'https://cinv.pay2go.com/Api/invoice_issue';

    protected $merchantKey;
    protected $merchantIV;
    protected $merchantId;
    protected $url;
    protected $postInfo;

    public function __construct()
    {
        $this->merchantKey = env('EZPAY_HASH_KEY', '');
        $this->merchantIV = env('EZPAY_HASH_IV', '');
        $this->merchantId = env('EZPAY_MERCHANT_ID');
        $this->url = env('APP_ENV', 'local') == 'local' ? self::TEST_URL : self::URL;
    }

    public function setPostInfo($order)
    {
        $taxAmt = $order->total * self::TAX_RATE;

        $postInfo = [
            //post_data 欄位資料
            'RespondType' => self::RESPOND_TYPE,
            'Version' => self::VERSION,
            'TimeStamp' => time(), //請以 time() 格式
            'TransNum' => $order->trade_no,
            'MerchantOrderNo' => $order->order_no,
            'BuyerName' => $order->user->user_name,
            'BuyerEmail' => $order->user->email,
            'Category' => self::CATEGORY,
            'TaxType' => '1',
            'TaxRate' => self::TAX_RATE,
            'Amt' => $order->total,
            'TaxAmt' => $taxAmt,
            'TotalAmt' => $order->total + $taxAmt,
            'PrintFlag' => 'Y',
            'ItemName' => $order->order_name, //多項商品時，以「|」分開
            'ItemCount' => '1', //多項商品時，以「|」分開
            'ItemUnit' => '組', //多項商品時，以「|」分開
            'ItemPrice' => $order->total, //多項商品時，以「|」分開
            'ItemAmt' => $order->total, //多項商品時，以「|」分開
            'Status' => '1' //1=立即開立，0=待開立，3=延遲開立
        ];

        $postInfo = http_build_query($postInfo); //轉成字串排列

        $this->postInfo = trim(
            bin2hex(
                openssl_encrypt(
                    $this->addpadding($postInfo),
                    'AES-256-CBC',
                    $this->merchantKey,
                    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                    $this->merchantIV
                )
            ));

        $this->postInfo = [
            'MerchantID_' => $this->merchantId,
            'PostData_' => $this->postInfo
        ];
        $this->postInfo = http_build_query($this->postInfo);

        return $this;
    }

    public function curlWork()
    {
        $curl_options = [
            CURLOPT_URL => $this->url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Google Bot',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_POST => '1',
            CURLOPT_POSTFIELDS => $this->postInfo
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($ch);
        curl_close($ch);

        $returnInfo = [
            'url' => $this->url,
            'sent_parameter' => $this->postInfo,
            'http_status' => $retcode,
            'curl_error_no' => $curl_error,
            'web_info' => $result
        ];

        return $returnInfo;
    }

    private function addpadding ($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }
}
