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

namespace Novanta\OrderPayment\Domain\OrderPayment\QueryResult;

use DateTime;
use JsonSerializable;
use Novanta\OrderPayment\Domain\OrderInvoice\QueryResult\OrderInvoiceForViewing;

class OrderPaymentForViewing implements JsonSerializable
{
    /** @var int */
    private $id;

    /** @var float */
    private $amount;

    /** @var string */
    private $method;

    /** @var string|null */
    private $transactionId;

    /** @var string|null */
    private $orderInvoiceNumber;

    /** @var EmployeeForViewing|null */
    private $employee;

    /** @var int|null */
    private $documentId;

    private $date;
    private ?int $orderInvoiceId;

    public function __construct(
        int                 $id,
        float               $amount,
        string              $method,
        ?string             $transactionId,
        ?int                $orderInvoiceId,
        ?string             $orderInvoiceNumber,
        ?EmployeeForViewing $employee,
        ?int                $documentId,
        ?DateTime              $date
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->method = $method;
        $this->transactionId = $transactionId;
        $this->orderInvoiceId = $orderInvoiceId;
        $this->orderInvoiceNumber = $orderInvoiceNumber;
        $this->employee = $employee;
        $this->documentId = $documentId;
        $this->date = $date;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getOrderInvoiceNumber(): ?string
    {
        return $this->orderInvoiceNumber;
    }

    public function getEmployee(): ?EmployeeForViewing
    {
        return $this->employee;
    }

    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'method' => $this->method,
            'transactionId' => $this->transactionId,
            'orderInvoiceId' => $this->orderInvoiceId,
            'orderInvoiceNumber' => $this->orderInvoiceNumber,
            'employee' => $this->employee,
            'documentId' => $this->documentId,
            'date' => $this->date,
        ];
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function getOrderInvoiceId(): ?int
    {
        return $this->orderInvoiceId;
    }
}
