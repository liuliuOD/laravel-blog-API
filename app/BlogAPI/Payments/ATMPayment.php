<?php

namespace BlogAPI\Payments;

class ATMPayment extends AbstractPayment
{
    const ITEM_DESC = 'Pay by ATM.';

    public function setTradeInfo($orderNo, $amount)
    {
        $tradeInfo = array(
                'MerchantID' => $this->merchantId,
                'RespondType' => self::RESPOND_TYPE,
                'TimeStamp' => time(),
                'Version' => self::VERSION,
                'MerchantOrderNo' => $orderNo,
                'Amt' => $amount,
                'ItemDesc' => self::ITEM_DESC,
                'Email' => request()->user()->email,
                'LoginType' => 0,
                'VACC' => 1,
                'NotifyURL' => route('api.v1.notify'),
            );

        //交易資料經 AES 加密後取得 TradeInfo
        $this->tradeInfo = $this->create_mpg_aes_encrypt(
                $tradeInfo,
                $this->merchantKey,
                $this->merchantIV
            );

        return $this;
    }
}