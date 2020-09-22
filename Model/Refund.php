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

namespace Gobeep\Ecommerce\Model;

use Gobeep\Ecommerce\Api\Data\RefundInterface;
use Gobeep\Ecommerce\Model\Config\ConfigProvider;
use Gobeep\Ecommerce\SdkInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Refund extends AbstractModel implements RefundInterface
{
    protected $_eventPrefix = 'gobeep_ecommerce_refund';

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Configuration provider
     *
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * Order interface
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Transport builder for sending emails
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Constructor
     * @param ConfigProvider $configProvider
     * @param LoggerInterface $logger
     * @param TransportBuilder $transportBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        OrderRepositoryInterface $orderRepository,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->transportBuilder = $transportBuilder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Gobeep\Ecommerce\Model\ResourceModel\Refund');
    }

    /**
     * Sends a status notification email to customer
     *
     * @return bool
     */
    public function sendStatusNotification()
    {
        $storeId = $this->getStoreId();
        $notifyEnabled = $this->configProvider->getNotifyEnabled($storeId);
        if (!$notifyEnabled) {
            return false;
        }

        try {
            $status = $this->getStatus();
            $template = ($status === SdkInterface::STATUS_REFUNDED) ?
                ConfigProvider::XML_PATH_REFUND_EMAIL_TEMPLATE :
                ConfigProvider::XML_PATH_WINNING_EMAIL_TEMPLATE;

            // Check if emails have been already sent
            if (
                ($status === SdkInterface::STATUS_REFUNDED && $this->getRefundEmailSent()) ||
                ($status === SdkInterface::STATUS_PENDING && $this->getWinningEmailSent())
            ) {
                return false;
            }

            $emailTemplate = null;
            switch ($template) {
                case ConfigProvider::XML_PATH_REFUND_EMAIL_TEMPLATE:
                    $emailTemplate = $this->configProvider->getRefundEmailTemplate($storeId);
                    break;
                case ConfigProvider::XML_PATH_WINNING_EMAIL_TEMPLATE:
                    $emailTemplate = $this->configProvider->getWinningEmailTemplate($storeId);
                    break;
            }
            if ($emailTemplate) {
                $vars = [
                    'customerFirstName' => $this->getCustomerFirstname(),
                    'customerLastName'  => $this->getCustomerLastname(),
                    'customerEmail'     => $this->getCustomerEmail(),
                    'order'             => $this->orderRepository->get($this->getOrderId())
                ];

                $sender = $this->configProvider->getSenderEmail($storeId);

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $template
                )->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->getStoreId()
                    ]
                )->setTemplateVars(
                    $vars
                )->setFromByScope(
                    $sender
                )->addTo(
                    $this->getCustomerEmail(),
                    "{$this->getCustomerFirstname()} {$this->getCustomerLastname()}"
                )->getTransport();

                try {
                    $transport->sendMessage();
                } catch (\Exception $exception) {
                    $this->logger->critical($exception->getMessage());
                }

                $this->setData(($status === SdkInterface::STATUS_REFUNDED) ? 'refund_email_sent' : 'winning_email_sent', true);
                $this->save();
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return true;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->getData(self::ORDER_ID);
    }

    /**
     * @param int $id
     * @return void
     */
    public function setOrderId($id): void
    {
        $this->setData(self::ORDER_ID, $id);
    }

    /**
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        return (string)$this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @param string $id
     * @return void
     */
    public function setOrderIncrementId($id): void
    {
        $this->setData(self::ORDER_INCREMENT_ID, $id);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string)$this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return bool
     */
    public function getRefundEmailSent(): bool
    {
        return (string)$this->getData(self::REFUND_EMAIL_SENT);
    }

    /**
     * @param bool $sent
     * @return void
     */
    public function setRefundEmailSent(bool $sent): void
    {
        $this->setData(self::REFUND_EMAIL_SENT, $sent);
    }

    /**
     * @return bool
     */
    public function getWinningEmailSent(): bool
    {
        return (string)$this->getData(self::WINNING_EMAIL_SENT);
    }

    /**
     * @param bool $sent
     * @return void
     */
    public function setWinningEmailSent(bool $sent): void
    {
        $this->setData(self::WINNING_EMAIL_SENT, $sent);
    }
}
