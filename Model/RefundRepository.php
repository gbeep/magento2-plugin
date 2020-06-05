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

use DateTime;
use Gobeep\Ecommerce\Api\Data\RefundInterface;
use Gobeep\Ecommerce\Api\Data\RefundRepositoryInterface;
use Gobeep\Ecommerce\Model\ResourceModel\Refund as RefundResource;
use Gobeep\Ecommerce\SdkInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;

class RefundRepository implements RefundRepositoryInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RefundResource
     */
    private $refundResource;

    /**
     * @var RefundFactory
     */
    private $refundFactory;

    /**
     * RefundRepository constructor.
     * @param RefundResource $refundResource
     * @param RefundFactory $refundFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param EventManager $eventManager
     */
    public function __construct(
        RefundResource $refundResource,
        RefundFactory $refundFactory,
        OrderRepositoryInterface $orderRepository,
        EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
        $this->refundResource = $refundResource;
        $this->refundFactory = $refundFactory;
    }

    /**
     * @param int $id
     * @return RefundInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId($id): RefundInterface
    {
        $refundModel = $this->refundFactory->create();
        $this->refundResource->load($refundModel, $id);
        if (!$refundModel->getId()) {
            throw new NoSuchEntityException();
        }
        return $refundModel;
    }

    /**
     * @param RefundInterface $refund
     * @return void
     * @throws AlreadyExistsException
     */
    public function save(RefundInterface $refund): void
    {
        $refundModel = $this->refundFactory->create();
        $refundModel->setOrderId($refund->getOrderId());
        $refundModel->setOrderIncrementId($refund->getOrderIncrementId());
        $refundModel->setStatus($refund->getStatus());
        $refundModel->setCreatedAt($refund->getCreatedAt());
        $refundModel->setUpdatedAt($refund->getUpdatedAt());
        $refundModel->setRefundEmailSent($refund->getRefundEmailSent());
        $refundModel->setWinningEmailSent($refund->getWinningEmailSent());
        $this->refundResource->save($refundModel);
    }

    /**
     * @param Object $payload
     * @return array|null
     */
    public function processRefund($payload): ?array
    {
        $refundModel = $this->refundFactory->create();
        $refundModel->setStatus(SdkInterface::STATUS_PENDING);
        $refundModel->setOrderId($payload->orderId);
        $refundModel->setCreatedAt($payload->createdAt);
        $refundModel->setUpdatedAt($payload->updatedAt);
        $refundModel->setRefundEmailSent(false);
        $refundModel->setWinningEmailSent(false);

        $errors = [];

        $orderIdValid = new \Zend\Validator\NotEmpty();
        if (!$orderIdValid->isValid($refundModel->getOrderId())) {
            $errors[] = __("`orderId` can't be empty");
        } else {
            // Check if refund already exists
            try {
                $existingRefund = $this->getByOrderId($payload->orderId);
            } catch (NoSuchEntityException $e) {
                $existingRefund = false;
            }
            if ($existingRefund) {
                return null;
            }

            // Check if order exists
            $order = null;
            try {
                $order = $this->orderRepository->get($payload->orderId);
            } catch (NoSuchEntityException $e) {
                $errors[] = __("Order doesn't exist");
            }
            if ($order && $order->getData()) {
                $refundModel->setOrderIncrementId($order->getIncrementId());
            }
        }

        if (DateTime::createFromFormat(DateTime::RFC3339, $payload->createdAt) === false) {
            $errors[] = __('`createdAt` is not a valid date');
        }

        if ($errors) {
            return $errors;
        }

        try {
            $this->refundResource->save($refundModel);
        } catch (AlreadyExistsException $e) {
            return null;
        }

        $refundModel->sendStatusNotification();
        $this->eventManager->dispatch('gobeep_ecommerce_adminhtml_webhook_refund', ['refund' => $refundModel]);

        return null;
    }
}
