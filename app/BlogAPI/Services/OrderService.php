<?php
namespace BlogAPI\Services;

use DB;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\OrderRepository;

class OrderService
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function addOrder($orderInfo)
    {
        return $this->orderRepository->create($orderInfo);
    }

    public function findOrderAndUserByOrderNo($orderNo)
    {
        $criteria = Criteria::create()->with(['user'])->where('order_no', $orderNo);

        return $this->orderRepository->first($criteria);
    }
}
