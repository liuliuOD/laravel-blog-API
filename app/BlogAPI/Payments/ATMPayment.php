<?php

namespace BlogAPI\Payments;

class ATMPayment extends AbstractPayment
{
    const ITEM_DESC = 'Pay by ATM.';

    protected $testUrl = 'https://ccore.spgateway.com/API/gateway/vacc';
    protected $formalUrl = 'https://core.spgateway.com/API/gateway/vacc';


    public function setTradeData($orderInfo)
    {
        $this->setTradeInfo($orderInfo)
            ->setTradeSha();
        
        return $this;
    }

    public function setTradeInfo($orderInfo)
    {
        $this->originTradeInfo = array(
                'RespondType' => self::RESPOND_TYPE,
                'TimeStamp' => time(),
                'Version' => self::VERSION,
                'MerchantOrderNo' => $orderInfo->order_no,
                'Amt' => $orderInfo->total,
                'ProdDesc' => self::ITEM_DESC,
                'BankType' => $orderInfo->bank_type,
                'NotifyURL' => route('api.v1.notify'),
                'ExpireDate' => (new \DateTime($orderInfo->created_at))
                    ->add(new \DateInterval('P1D'))
                    ->format('Ymd'),
                'Email' => request()->user()->email,
            );

        //交易資料經 AES 加密後取得 TradeInfo
        $this->tradeInfo = $this->create_mpg_aes_encrypt(
                $this->originTradeInfo,
                $this->merchantKey,
                $this->merchantIV
            );

        return $this;
    }
}