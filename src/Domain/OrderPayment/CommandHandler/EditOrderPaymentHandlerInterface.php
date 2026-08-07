<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\CommandHandler;

use Novanta\OrderPayment\Domain\OrderPayment\Command\EditOrderPayment;

/**
 * Interface EditOrderPaymentHandlerInterface
 */
interface EditOrderPaymentHandlerInterface
{
    /**
     * @param EditOrderPayment $command
     */
    public function handle(EditOrderPayment $command);
}
