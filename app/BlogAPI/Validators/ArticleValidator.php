<?php

namespace BlogAPI\Validators;

class ArticleValidator
{
    protected $validator;
    protected $messages;
    protected $rules;
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;

        $this->messages = [
            'title.required' => '請輸入文章標題',
            'title.string' => '標題格式錯誤',
            'title.max' => '標題過長',
            'content.required' => '請輸入文章內容',
            'content.string' => '內容格式錯誤',
            'tag.max' => '標籤長度請勿大於 :max 位字元'
        ];
    }

    public function setCreateArticleRule()
    {
        $this->rules = [
            'title' => 'required|string|max:256',
            'content' => 'required|string',
            'tag' => 'array|max:32',
        ];

        return $this;
    }

    public function setUpdateArticleRule()
    {
        $this->rules = [
            'title' => 'required_without_all:content,tag|string|max:256',
            'content' => 'required_without_all:title,tag|string',
            'tag' => 'required_without_all:title,content|array|max:32',
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