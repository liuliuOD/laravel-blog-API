<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\CartService;
use BlogAPI\Services\OrderService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Validators\PaymentsValidator;
use BlogAPI\Exceptions\NotFoundException;
use BlogAPI\Transformers\OrderTransformer;
use BlogAPI\Exceptions\InvalidParameterException;

class PaymentsController extends Controller
{
    protected $cartService;
    protected $orderService;
    protected $articleService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        ArticleService $articleService
    )
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->articleService = $articleService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $params = $request->only(['article_ids']);

        // $valid = new PaymentsValidator($params);
        // $valid->setAddOrderRule();
        // if(! $valid->passes()) {
        //     throw new InvalidParameterException($valid->errors()->first());
        // }

        $articles = $this->articleService
            ->findByIds($params['article_ids'])
            ->filter(function ($article) {
                return $article !== null;
            });
        if ($articles->isEmpty()) {
            throw new InvalidParameterException();
        }

        $amount = $this->cartService->addItemsToCart($articles)->getTotal();
        $this->orderService->addOrder(OrderTransformer::toDatabase($amount));

        return response()->json(
                self::RESPONSE_201
            );
    }
}
