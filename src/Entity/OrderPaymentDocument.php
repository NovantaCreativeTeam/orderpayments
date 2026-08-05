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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace OrderPayment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="OrderPayment\Repository\OrderPaymentDocumentRepository")
 */
class OrderPaymentDocument
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_order_payment", type="integer")
     */
    private $orderPaymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="id_order_document", type="integer")
     */
    private $orderDocumentId;

    /**
     * @return int
     */
    public function getOrderPaymentId()
    {
        return $this->orderPaymentId;
    }

    /**
     * @param int $orderPaymentId
     *
     * @return OrderPaymentDocument
     */
    public function setOrderPaymentId($orderPaymentId)
    {
        $this->orderPaymentId = $orderPaymentId;

        return $this;
    }

    public function getOrderDocumentId(): string
    {
        return $this->orderDocumentId;
    }

    public function setOrderDocumentId(string $orderDocumentId): void
    {
        $this->orderDocumentId = $orderDocumentId;
    }
}
