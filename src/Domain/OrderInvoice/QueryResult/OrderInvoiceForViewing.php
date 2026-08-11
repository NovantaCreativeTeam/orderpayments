<?php
/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2026 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Novanta\OrderPayment\Domain\OrderInvoice\QueryResult;


class OrderInvoiceForViewing
{
    /** @var int */
    private $id;

    /** @var string */
    private $number;

    /** @var float */
    private $totalPaidTaxIncluded;

    private $paymentMethod;
    private $paymentTerm;
    private $amountType;
    private $amount;
    private $note;

    private $totalToPay;

    /** @var string */
    private $dateAdd;
    private ?string $deliveryDate;

    public function __construct(
        int     $id,
        string  $number,
        ?string $paymentMethod,
        ?string $paymentTerm,
        ?string $amountType,
        ?float  $amount,
        ?string $note,
        ?float  $totalPaidTaxIncluded,
        ?float  $totalToPay,
        ?string $dateAdd,
        ?string $deliveryDate
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->totalPaidTaxIncluded = $totalPaidTaxIncluded;
        $this->dateAdd = $dateAdd;
        $this->paymentMethod = $paymentMethod;
        $this->paymentTerm = $paymentTerm;
        $this->amountType = $amountType;
        $this->amount = $amount;
        $this->note = $note;
        $this->totalToPay = $totalToPay;
        $this->deliveryDate = $deliveryDate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getTotalPaidTaxIncluded(): float
    {
        return $this->totalPaidTaxIncluded;
    }

    public function getDateAdd(): string
    {
        return $this->dateAdd;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getPaymentTerm()
    {
        return $this->paymentTerm;
    }

    /**
     * @return mixed
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return mixed
     */
    public function getTotalToPay()
    {
        return $this->totalToPay;
    }

    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate;
    }
}
