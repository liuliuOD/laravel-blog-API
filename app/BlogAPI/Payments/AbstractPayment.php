<?php

namespace BlogAPI\Payments;

use GuzzleHttp\Client;

abstract class AbstractPayment
{
    const VERSION = 1.0;
    const RESPOND_TYPE = 'JSON';

    protected $merchantKey;
    protected $merchantIV;
    protected $merchantId;
    protected $url;
    protected $tradeInfo;
    protected $tradeSha;
    protected $originTradeInfo;

    abstract function setTradeData($orderInfo);

    public function __construct()
    {
        $this->merchantKey = env('NEWEBPAY_HASH_KEY', '');
        $this->merchantIV = env('NEWEBPAY_HASH_IV', '');
        $this->merchantId = env('NEWEBPAY_MERCHANT_ID');

        $this->url = env('APP_ENV', 'local') == 'local' ? $this->testUrl : $this->formalUrl;
    }

    public function checkParams()
    {
        return [
            'url' => $this->url,
            'info' => $this->tradeInfo,
            'origin info' => $this->originTradeInfo,
            'sha' => $this->tradeSha,
        ];
    }

    public function setTradeSha()
    {
        $shaString = 'HashKey=' . $this->merchantKey . '&' . $this->tradeInfo . '&HashIV=' . $this->merchantIV;
        
        $this->tradeSha = strtoupper(hash("sha256", $shaString));

        return $this;
    }

    public function sendRequest()
    {
        $client = new Client();

        $data = [
            'MerchantID_' => $this->merchantId,
            'PostData_' => $this->tradeInfo,
        ];

        return $client->request('POST', $this->url, [
            'form_params' => $data
        ]);
    }

    public function create_mpg_aes_encrypt($parameter = '' , $key = '', $iv = '')
    {
        $return_str = '';

        if (!empty($parameter)) {
            //將參數經過 URL ENCODED QUERY STRING
            $return_str = http_build_query($parameter);
        }
        return trim(
                bin2hex(
                    openssl_encrypt(
                        $this->addpadding($return_str),
                        'aes-256-cbc',
                        $key,
                        OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
                        $iv
                    )
                )
            );
    }

    public function create_aes_decrypt($parameter = '' , $key = '', $iv = '')
    {
        return $this->strippadding(
                openssl_decrypt(
                    hex2bin($parameter),
                    'AES-256-CBC',
                    $key,
                    OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
                    $iv
                )
            );
    }
       
    private function strippadding($string)
    {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);

            return $string;
        } else {
            return false;
        }
    }
    
    private function addpadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }
}