<?php

namespace Novanta\OrderPayment\Domain\OrderPayment\Command;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\File;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use Novanta\OrderPayment\Domain\OrderPayment\ValueObject\OrderPaymentId;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;
use PrestaShop\PrestaShop\Core\Domain\Employee\ValueObject\EmployeeId;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\NegativePaymentAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderConstraintException;

/**
 * Class EditOrderPayment
 */
class EditOrderPayment
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
     * @var OrderPaymentId
     */
    private $orderPaymentId;

    /**
     * @var DateTimeImmutable
     */
    private $paymentDate;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var DecimalNumber
     */
    private $paymentAmount;

    /**
     * @var CurrencyId
     */
    private $paymentCurrencyId;

    /**
     * @var EmployeeId
     */
    private $employeeId;

    /**
     * @var int|null
     */
    private $orderInvoiceId;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @param int $orderPaymentId
     * @param string $paymentDate
     * @param string $paymentMethod
     * @param string $paymentAmount
     * @param int $paymentCurrencyId
     * @param int $employeeId
     * @param int|null $orderInvoiceId
     * @param string|null $transactionId
     * @param File|null $file
     *
     * @throws OrderPaymentException
     */
    public function __construct(
        int $orderPaymentId,
        string $paymentDate,
        string $paymentMethod,
        string $paymentAmount,
        int $paymentCurrencyId,
        int $employeeId,
        ?int $orderInvoiceId = null,
        ?string $transactionId = null,
        ?File $file = null
    ) {
        $amount = new DecimalNumber($paymentAmount);
        $this->assertAmountIsPositive($amount);
        $this->assertPaymentMethodIsGenericName($paymentMethod);

        $this->orderPaymentId = new OrderPaymentId($orderPaymentId);
        $this->paymentDate = new DateTimeImmutable($paymentDate);
        $this->paymentMethod = $paymentMethod;
        $this->paymentAmount = $amount;
        $this->paymentCurrencyId = new CurrencyId($paymentCurrencyId);
        $this->employeeId = new EmployeeId($employeeId);
        $this->orderInvoiceId = $orderInvoiceId;
        $this->transactionId = $transactionId;
        $this->file = $file;
    }

    /**
     * @return OrderPaymentId
     */
    public function getOrderPaymentId(): OrderPaymentId
    {
        return $this->orderPaymentId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPaymentDate(): DateTimeImmutable
    {
        return $this->paymentDate;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return DecimalNumber
     */
    public function getPaymentAmount(): DecimalNumber
    {
        return $this->paymentAmount;
    }

    /**
     * @return CurrencyId
     */
    public function getPaymentCurrencyId(): CurrencyId
    {
        return $this->paymentCurrencyId;
    }

    /**
     * @return EmployeeId
     */
    public function getEmployeeId(): EmployeeId
    {
        return $this->employeeId;
    }

    /**
     * @return int|null
     */
    public function getOrderInvoiceId(): ?int
    {
        return $this->orderInvoiceId;
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
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
