<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Novanta\OrderPayment\Domain\OrderPayment\Command;

use DateTimeImmutable;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;
use PrestaShop\PrestaShop\Core\Domain\Employee\ValueObject\EmployeeId;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\NegativePaymentAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Adds payment for given order.
 */
class AddOrderPaymentCommand
{
    /**
     * @var string
     */
    public const INVALID_CHARACTERS_NAME = '<>{}';

    /**
     * @var string
     */
    private const PATTERN_PAYMENT_METHOD_NAME = '/^[^' . self::INVALID_CHARACTERS_NAME . ']*$/u';

    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var DateTimeImmutable
     */
    private $paymentDate;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var float
     */
    private $paymentAmount;

    /**
     * @var CurrencyId
     */
    private $paymentCurrencyId;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var int|null
     */
    private $orderInvoiceId;

    /**
     * @var EmployeeId
     */
    protected $employeeId;

    /**
     * @var UploadedFile|null
     */
    private $file;

    /**
     * @param int $orderId
     * @param string $paymentDate
     * @param string $paymentMethod
     * @param float $paymentAmount
     * @param int $paymentCurrencyId
     * @param int $employeeId
     * @param int|null $orderInvoiceId
     * @param string|null $transactionId transaction ID, usually payment ID from payment gateway
     * @param UploadedFile|null $file
     */
    public function __construct(
        int $orderId,
        string $paymentDate,
        string $paymentMethod,
        float $paymentAmount,
        int $paymentCurrencyId,
        int $employeeId,
        ?int $orderInvoiceId = null,
        ?string $transactionId = null,
        ?UploadedFile $file = null
    ) {

        $this->orderId = new OrderId($orderId);
        $this->paymentDate = new DateTimeImmutable($paymentDate);
        $this->paymentMethod = $paymentMethod;
        $this->paymentAmount = $paymentAmount;
        $this->paymentCurrencyId = new CurrencyId($paymentCurrencyId);
        $this->employeeId = new EmployeeId($employeeId);
        $this->orderInvoiceId = $orderInvoiceId;
        $this->transactionId = $transactionId;
        $this->file = $file;
    }

    /**
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @return CurrencyId
     */
    public function getPaymentCurrencyId()
    {
        return $this->paymentCurrencyId;
    }

    /**
     * @return int|null
     */
    public function getOrderInvoiceId()
    {
        return $this->orderInvoiceId;
    }

    /**
     * @return EmployeeId
     */
    public function getEmployeeId(): EmployeeId
    {
        return $this->employeeId;
    }

    /**
     * @return string|null
     */
    public function getPaymentTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param string $paymentMethod
     *
     * @return void
     *
     * @throws OrderConstraintException
     */
    private function assertPaymentMethodIsGenericName(string $paymentMethod): void
    {
        if (empty($paymentMethod) || !preg_match(self::PATTERN_PAYMENT_METHOD_NAME, $paymentMethod)) {
            throw new OrderConstraintException(
                'The selected payment method is invalid.',
                OrderConstraintException::INVALID_PAYMENT_METHOD
            );
        }
    }

    /**
     * @param DecimalNumber $amount
     *
     * @return void
     *
     * @throws NegativePaymentAmountException
     */
    private function assertAmountIsPositive(DecimalNumber $amount): void
    {
        if ($amount->isNegative()) {
            throw new NegativePaymentAmountException('The amount should be greater than 0.');
        }
    }
}
