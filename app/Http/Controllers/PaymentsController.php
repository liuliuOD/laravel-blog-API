<?php
namespace App\Http\Controllers;

use BlogAPI\Models\Order;
use Illuminate\Http\Request;
use BlogAPI\Services\CartService;
use BlogAPI\Services\OrderService;
use BlogAPI\Services\PaymentService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Services\InvoiceService;
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
    protected $invoiceService;
    protected $userArticlePermissionService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        ArticleService $articleService,
        PaymentService $paymentService,
        InvoiceService $invoiceService,
        UserArticlePermissionService $userArticlePermissionService
    )
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->articleService = $articleService;
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
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
        $params = $request->only(['article_ids', 'payment_method', 'bank_type']);

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
        $orderName = $articles->implode('title', '+');
        $orderInfo = $this->orderService->addOrder(OrderTransformer::toDatabase($amount, $orderName, $params));

        $articleIds = $articles->pluck('id')->toArray();
        $this->userArticlePermissionService->addUnPaidPermissions($articleIds, $orderInfo->id);

        $thirdResponse = $this->paymentService
            ->setPaymentMethod($params['payment_method'])
            ->setTradeData($orderInfo)
            ->sendTrade();

        return response()->json(
                $thirdResponse,
                self::RESPONSE_201
            );
    }

    public function notify(Request $request)
    {
        $params = $request->only(['Status', 'Message', 'Result']);
        $order = $this->orderService->findOrderAndUserByOrderNo($params);

        if (!$order) {
            throw new NotFoundException();
        }

        $invoice = $this->invoiceService->issueInvoice($order);

        return $invoice;
    }

    public function lineNotify(Request $request)
    {
        $params = $request->only(['transactionId', 'orderId']);

        $orderInfo = $this->orderService->findOrderAndUserByOrderNo($params['orderId']);
        $orderInfo->paid_status = Order::PAYMENT_METHOD_LINE;
        $orderInfo->save();

        $thirdResponse = $this->paymentService
            ->setPaymentMethod(Order::PAYMENT_METHOD_LINE)
            ->setConfirmData($orderInfo, $params['transactionId'], 'confirm')
            ->sendTrade();

        return $thirdResponse;
    }
}
