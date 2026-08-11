<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice;

enum OrderInvoicePaymentTerm: string
{
    CASE TOTAL = 'total';
    case ADVANCE = 'advance';
    case DOWN = 'down';

}
