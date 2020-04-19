<?php

namespace BlogAPI\Validators;

class CartValidator
{
    protected $validator;
    protected $messages;
    protected $rules;
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;

        $this->messages = [
            'username.required' => '請輸入您的姓名',
            'email.required' => '請輸入電子郵件',
            'email.email' => '請輸入正確的電子郵件格式',
            'email.exists' => '您尚未成為會員',
            'email.unique' => ':email 已存在，請輸入其他的電子郵件',
            'password.required' => '請輸入密碼',
            'password.min' => '請輸入至少 :min 位字元的密碼',
        ];
    }

    public function setAddItemRule()
    {
        $this->rules = [
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