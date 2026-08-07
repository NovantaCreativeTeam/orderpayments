<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\ValueObject;

use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;

/**
 * Class OrderPaymentId
 */
class OrderPaymentId
{
    /**
     * @var int
     */
    private $orderPaymentId;

    /**
     * @param int $orderPaymentId
     *
     * @throws OrderPaymentException
     */
    public function __construct(int $orderPaymentId)
    {
        $this->assertIntegerIsGreaterThanZero($orderPaymentId);
        $this->orderPaymentId = $orderPaymentId;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->orderPaymentId;
    }

    /**
     * @param int $orderPaymentId
     *
     * @throws OrderPaymentException
     */
    private function assertIntegerIsGreaterThanZero(int $orderPaymentId)
    {
        if (0 >= $orderPaymentId) {
            throw new OrderPaymentException(
                sprintf('Invalid OrderPaymentId: %s. ID must be greater than zero.', $orderPaymentId)
            );
        }
    }
}
