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

namespace Gobeep\Ecommerce\Block;

use Gobeep\Ecommerce\Model\SdkService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Link extends Template
{
    protected $sdk;

    public function __construct(
        Context $context,
        SdkService $sdk,
        array $data = []
    ) {
        $this->sdk = $sdk;
        parent::__construct($context, $data);
    }

    /**
     * Checks if a link can be generated based on system configuration
     * parameters
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canLink()
    {
        if (!$this->hasData('for')) {
            return false;
        }

        $storeId = $this->getData('store_id');
        $this->sdk->setStore($storeId);
        if (!$this->sdk->isReady()) {
            return false;
        }

        if ($this->getData('for') === SdkService::TYPE_CASHIER) {
            $orderAmount = $this->getData('order_amount');
            if ($orderAmount == 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the image associated to the link
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImage()
    {
        $storeId = $this->getData('store_id');
        $this->sdk->setStore($storeId);
        if ($this->getData('for') === SdkService::TYPE_CASHIER) {
            return $this->sdk->getImage(SdkService::TYPE_CASHIER);
        }

        return $this->sdk->getImage(SdkService::TYPE_CAMPAIGN);
    }

    /**
     * Returns the link
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLink()
    {
        $storeId = $this->getData('store_id');

        $this->sdk->setStore($storeId);
        if ($this->getData('for') === SdkService::TYPE_CASHIER) {
            $orderAmount = $this->getData('order_amount');
            $orderId = $this->getData('order_id');

            return $this->sdk->getCashierLink($orderAmount, $orderId);
        }

        return $this->sdk->getCampaignLink();
    }
}
