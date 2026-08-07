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

use JsonSerializable;

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

    /** @var OrderInvoiceForViewing|null */
    private $orderInvoice;

    /** @var EmployeeForViewing|null */
    private $employee;

    /** @var string|null */
    private $document;

    /** @var int|null */
    private $documentId;

    /** @var string */
    private $date;

    public function __construct(
        int $id,
        float $amount,
        string $method,
        ?string $transactionId,
        ?OrderInvoiceForViewing $orderInvoice,
        ?EmployeeForViewing $employee,
        ?string $document,
        ?int $documentId,
        string $date
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->method = $method;
        $this->transactionId = $transactionId;
        $this->orderInvoice = $orderInvoice;
        $this->employee = $employee;
        $this->document = $document;
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

    public function getOrderInvoice(): ?OrderInvoiceForViewing
    {
        return $this->orderInvoice;
    }

    public function getEmployee(): ?EmployeeForViewing
    {
        return $this->employee;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'method' => $this->method,
            'transactionId' => $this->transactionId,
            'orderInvoice' => $this->orderInvoice,
            'employee' => $this->employee,
            'document' => $this->document,
            'documentId' => $this->documentId,
            'date' => $this->date,
        ];
    }
}
