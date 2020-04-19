<?php
namespace BlogAPI\Services;

use Cart;

class CartService
{
    protected $cartRepository;

    public function addItemsToCart($items)
    {
        $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->title,
                    'price' => $item->price,
                    'quantity' => 1,
                ];
            })
            ->toArray();

        Cart::add($items);

        return $this;
    }

    public function getItems()
    {
        return Cart::getContent();
    }

    public function getTotal()
    {
        return Cart::getTotal();
    }
}
