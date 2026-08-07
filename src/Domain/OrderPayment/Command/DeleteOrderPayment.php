<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\Command;

use Novanta\OrderPayment\Domain\OrderPayment\ValueObject\OrderPaymentId;

/**
 * Class DeleteOrderPayment
 */
class DeleteOrderPayment
{
    /**
     * @var OrderPaymentId
     */
    private $orderPaymentId;

    /**
     * @param int $orderPaymentId
     */
    public function __construct(int $orderPaymentId)
    {
        $this->orderPaymentId = new OrderPaymentId($orderPaymentId);
    }

    /**
     * @return OrderPaymentId
     */
    public function getOrderPaymentId(): OrderPaymentId
    {
        return $this->orderPaymentId;
    }
}
