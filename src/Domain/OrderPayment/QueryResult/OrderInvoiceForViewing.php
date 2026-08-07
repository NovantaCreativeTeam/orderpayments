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

class OrderInvoiceForViewing implements JsonSerializable
{
    /** @var int */
    private $id;

    /** @var string */
    private $number;

    public function __construct(
        int $id,
        string $number
    ) {
        $this->id = $id;
        $this->number = $number;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
        ];
    }
}
