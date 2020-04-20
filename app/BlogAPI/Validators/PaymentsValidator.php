<?php

namespace BlogAPI\Validators;

class PaymentsValidator
{
    protected $validator;
    protected $messages;
    protected $rules;
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;

        $this->messages = [
            'article_ids.required' => '缺少購買品項',
            'article_ids.array' => '傳入格式錯誤',
            'payment_method.in' => '付款方式錯誤',
        ];
    }

    public function setAddOrderRule()
    {
        $this->rules = [
            'article_ids' => 'required|array',
            'payment_method' => 'required|in:ATM,CREDIT_CARD,CVS',
        ];

        return $this;
    }

    public function passes()
    {
        $this->validator = validator()->make($this->params, $this->rules, $this->messages);

        return $this->validator->passes();
    }

    public function errors()
    {
        return $this->validator->errors();
    }
}