<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\CommandHandler;

use Novanta\OrderPayment\Domain\OrderPayment\Command\AddOrderPaymentCommand;

interface AddOrderPaymentHandlerInterface
{
    public function handle(AddOrderPaymentCommand $command);
}
