<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice;

enum OrderInvoicePaymentMethod: string
{
    case CARD = 'card';
    case PAYPAL = 'paypal';
    case WIREPAYMENT = 'wirepayment';
    case WIREPAYMENT_INSTANT = 'wirepayment_instant';
    case SATISPAY = 'satispay';
    case CASH = 'cash';
}
