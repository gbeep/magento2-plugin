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

namespace Gobeep\Ecommerce\Ui\DataProvider;

use Gobeep\Ecommerce\Helper\Data;
use Gobeep\Ecommerce\Model\Config\Source\RefundStatuses;
use Magento\Framework\UrlInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Statuses
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $dataHelper;

    public function __construct(CollectionFactory $collectionFactory, UrlInterface $urlBuilder, Data $dataHelper)
    {
        $this->collectionFactory = $collectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    public function getActions()
    {
        $actions = [];
        $statuses = $this->dataHelper->getStatuses();

        foreach($statuses as $value => $label) {
            $actions[] = [
                'type' => $value,
                'label' => $label,
                'url' => $this->urlBuilder->getUrl('*/*/massChangeStatus', ['status' => $value]),
            ];
        }
        return $actions;
    }
}
