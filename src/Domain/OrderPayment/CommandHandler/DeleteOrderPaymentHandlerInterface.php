<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\CommandHandler;

use Novanta\OrderPayment\Domain\OrderPayment\Command\DeleteOrderPayment;

/**
 * Interface DeleteOrderPaymentHandlerInterface
 */
interface DeleteOrderPaymentHandlerInterface
{
    /**
     * @param DeleteOrderPayment $command
     */
    public function handle(DeleteOrderPayment $command);
}
