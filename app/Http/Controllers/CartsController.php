<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\CartService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Validators\CartValidator;
use BlogAPI\Exceptions\NotFoundException;
use BlogAPI\Exceptions\InvalidParameterException;

class CartsController extends Controller
{
    protected $cartService;
    protected $articleService;

    public function __construct(
        CartService $cartService,
        ArticleService $articleService
    )
    {
        $this->cartService = $cartService;
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

        $valid = new CartValidator($params);
        $valid->setAddItemRule();
        if(! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }

        $articles = $this->articleService->findByIds($params['article_ids']);
        if (! $articles) {
            throw new InvalidParameterException();
        }

        $this->cartService->addItemsToCart($articles);

        return response()->json(
                ['items' => $this->cartService->getItems()],
                self::RESPONSE_201
            );
    }
}
