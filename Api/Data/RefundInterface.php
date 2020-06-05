<?php
/**
 * GoBeep
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    GoBeep
 * @package     Gobeep_Ecommerce
 * @author      Jonathan Gautheron <jgautheron@gobeep.co>
 * @copyright   Copyright (c) GoBeep (https://gobeep.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Gobeep\Ecommerce\Api\Data;

/**
 * Interface RefundInterface
 * @package Gobeep\Refund\Api\Data
 */
interface RefundInterface
{
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const REFUND_EMAIL_SENT = 'refund_email_sent';
    const WINNING_EMAIL_SENT = 'winning_email_sent';

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $id
     * @return void
     */
    public function setOrderId($id): void;

    /**
     * @return string
     */
    public function getOrderIncrementId(): string;

    /**
     * @param string $id
     * @return void
     */
    public function setOrderIncrementId($id): void;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void;

    /**
     * @return bool
     */
    public function getRefundEmailSent(): bool;

    /**
     * @param bool $sent
     * @return void
     */
    public function setRefundEmailSent(bool $sent): void;

    /**
     * @return bool
     */
    public function getWinningEmailSent(): bool;

    /**
     * @param bool $sent
     * @return void
     */
    public function setWinningEmailSent(bool $sent): void;
}
