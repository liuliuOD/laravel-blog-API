<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\CartService;
use BlogAPI\Services\OrderService;
use BlogAPI\Services\PaymentService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Validators\PaymentsValidator;
use BlogAPI\Exceptions\NotFoundException;
use BlogAPI\Transformers\OrderTransformer;
use BlogAPI\Exceptions\InvalidParameterException;
use BlogAPI\Services\UserArticlePermissionService;

class PaymentsController extends Controller
{
    protected $cartService;
    protected $orderService;
    protected $articleService;
    protected $paymentService;
    protected $userArticlePermissionService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        ArticleService $articleService,
        PaymentService $paymentService,
        UserArticlePermissionService $userArticlePermissionService
    )
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->articleService = $articleService;
        $this->paymentService = $paymentService;
        $this->userArticlePermissionService = $userArticlePermissionService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $params = $request->only(['article_ids', 'payment_method']);

        $valid = new PaymentsValidator($params);
        $valid->setAddOrderRule();
        if(! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }

        $articles = $this->articleService
            ->findByIds($params['article_ids'])
            ->filter(function ($article) {
                return $article !== null;
            });
        if ($articles->isEmpty()) {
            throw new InvalidParameterException();
        }

        $amount = $this->cartService->addItemsToCart($articles)->getTotal();
        $orderInfo = $this->orderService->addOrder(OrderTransformer::toDatabase($amount));

        $articleIds = $articles->pluck('id')->toArray();
        $permission = $this->userArticlePermissionService->addUnPaidPermissions($articleIds, $orderInfo->id);

        $thirdResponse = $this->paymentService
            ->setPaymentMethod($params['payment_method'])
            ->setTradeData($orderInfo->order_no, $amount)
            ->sendTrade();

        return response()->json(
                $thirdResponse,
                self::RESPONSE_201
            );
    }
}
