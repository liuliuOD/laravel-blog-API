<?php

namespace BlogAPI\Payments;

use GuzzleHttp\Client;

abstract class AbstractPayment
{
    const VERSION = 1.5;
    const RESPOND_TYPE = 'JSON';
    const URL = 'https://core.newebpay.com/MPG/mpg_gateway';
    const TEST_URL = 'https://ccore.newebpay.com/MPG/mpg_gateway';

    protected $merchantKey;
    protected $merchantIV;
    protected $merchantId;
    protected $url;
    protected $tradeInfo;
    protected $tradeSha;

    public function __construct()
    {
        $this->merchantKey = env('NEWEBPAY_HASH_KEY', '');
        $this->merchantIV = env('NEWEBPAY_HASH_IV', '');
        $this->merchantId = env('NEWEBPAY_MERCHANT_ID');
        $this->url = env('APP_ENV', 'local') == 'local' ? self::TEST_URL : self::URL;
    }

    public function checkParams()
    {
        return [
            'url' => $this->url,
            'info' => $this->tradeInfo,
            'sha' => $this->tradeSha,
        ];
    }

    public function sendRequest()
    {
        $client = new Client();

        $data = [
            'MerchantID' => $this->merchantId,
            'TradeInfo' => $this->tradeInfo,
            'TradeSha' => $this->tradeSha,
            'Version' => self::VERSION,
        ];

        return $client->request('POST', $this->url, [
            'form_params' => $data
        ]);
    }

    public function setTradeSha()
    {
        $shaString = 'HashKey=' . $this->merchantKey . '&' . $this->tradeInfo . '&HashIV=' . $this->merchantIV;
        
        $this->tradeSha = strtoupper(hash("sha256", $shaString));

        return $this;
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