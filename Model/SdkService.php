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

use Exception;
use Gobeep\Ecommerce\Model\Config\ConfigProvider;
use Gobeep\Ecommerce\Model\System\Config\Backend\Image;
use Gobeep\Ecommerce\Sdk as GobeepSdk;
use Gobeep\Ecommerce\SdkInterface as GoBeepSdkInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SdkService extends AbstractModel
{
    /**
     * Type constants
     */
    const TYPE_CASHIER = 'cashier';
    const TYPE_CAMPAIGN = 'campaign';

    /**
     * Holds SDK instance
     *
     * @var GobeepSdk
     */
    protected $sdk;

    /**
     * Store manager instance
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Scoped configuration instance
     *
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Currency interface
     *
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * Constructor
     * @param StoreManagerInterface $storeManager
     * @param ConfigProvider $configProvider
     * @param LoggerInterface $logger
     * @param CurrencyInterface $currency
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        CurrencyInterface $currency,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null
    ) {
        $this->currency = $currency;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Initialize resource model
     * @param int|null $store
     * @return SdkService
     */
    public function setStore($store = null)
    {
        // Initialize SDK with system configuration values
        $this->sdk = new GobeepSdk();
        $store = $store ?: $this->storeManager->getStore()->getId();

        $this->addData([
            'is_enabled'              => $this->configProvider->getIsEnabled($store),
            'environment'             => $this->configProvider->getEnvironment($store) ?: GoBeepSdkInterface::ENV_PRODUCTION,
            'region'                  => $this->configProvider->getRegion($store) ?: GoBeepSdkInterface::REGION_EU,
            'campaign_id'             => $this->configProvider->getCampaignId($store),
            'cashier_id'              => $this->configProvider->getCashierId($store),
            'secret'                  => $this->configProvider->getSecret($store),
            'from_date'               => $this->configProvider->getFromDate($store),
            'to_date'                 => $this->configProvider->getToDate($store),
            'eligible_days'           => $this->configProvider->getEligibleDays($store),
            'timezone'                => $this->configProvider->getTimezone($store) ?: 'Europe/Paris',
            'cashier_image'           => $this->configProvider->getCashierImage($store),
            'external_cashier_image'  => $this->configProvider->getExtCashierImage($store),
            'campaign_image'          => $this->configProvider->getCampaignImage($store),
            'external_campaign_image' => $this->configProvider->getExtCampaignImage($store)
        ]);

        // Initialize SDK
        $this->sdk->setEnvironment($this->getData('environment'))
            ->setRegion($this->getData('region'))
            ->setCampaignId($this->getData('campaign_id'))
            ->setCashierId($this->getData('cashier_id'))
            ->setSecret($this->getData('secret'))
            ->setTimezone($this->getData('timezone'));

        return $this;
    }

    /**
     * Check if we're ready to use the SDK
     *
     * @param bool $advancedCheck Advanced check (also checks availability of graphical assets)
     *
     * @return bool
     */
    public function isReady($advancedCheck = true)
    {
        // First check mandatory parameters
        if (!$this->getData('is_enabled') || !$this->getData('campaign_id') || !$this->getData('cashier_id') || !$this->getData('secret')) {
            return false;
        }
        // Then check dates
        if (!$this->hasValidDates()) {
            return false;
        }
        // And check resources
        if ($advancedCheck) {
            return $this->hasValidResources();
        }

        return true;
    }

    /**
     * Checks if we have a cashier and campaign image
     *
     * @return bool
     */
    public function hasValidResources()
    {
        return ($this->getData('cashier_image') || $this->getData('external_cashier_image')) &&
            ($this->getData('campaign_image') || $this->getData('external_campaign_image'));
    }

    /**
     * Checks through SDK if dates are valid
     *
     * @return bool
     */
    public function hasValidDates()
    {
        $date = date('Y-m-d H:i:s', strtotime('now'));

        // Check if date is in range
        $isDateInRange = $this->sdk->isDateInRange($date, $this->getData('from_date'), $this->getData('to_date'));
        $isDayEligible = !$this->getData('') || $this->sdk->isDayEligible($date, explode(',', $this->getData('eligible_days')));

        return $isDateInRange && $isDayEligible;
    }

    /**
     * Returns the Gobeep/Ecommerce campaign link
     *
     * @return string
     */
    public function getCampaignLink()
    {
        if (!$this->isReady()) {
            return '';
        }

        $campaignLink = '';
        try {
            $campaignLink = $this->sdk->getCampaignLink();
        } catch (Exception $e) {
            $this->logger->error($e);
        }

        return $campaignLink;
    }

    /**
     * Returns the Gobeep/Ecommerce cashier link
     *
     * @param float $orderAmount Order amount
     * @param string orderId Order identifier
     *
     * @return string
     */
    public function getCashierLink($orderAmount, $orderId)
    {
        if (!$this->isReady()) {
            return '';
        }

        $cashierLink = '';
        try {
            $cashierLink = $this->sdk->getCashierLink([
                'order_amount' => $this->currency->format(
                    $orderAmount,
                    false,
                    2,
                    null,
                    null
                ),
                'order_id' => $orderId,
                'referrer' => 'online',
            ]);
        } catch (Exception $e) {
            $this->logger->error($e);
        }

        return $cashierLink;
    }

    /**
     * Signs payload
     *
     * @param string $payload Payload
     *
     * @return string
     */
    public function sign($payload)
    {
        $res = '';
        try {
            $res = $this->sdk->sign($payload);
        } catch (Exception $e) {
            $this->logger->error($e);
        }

        return $res;
    }

    /**
     * Returns either an external or internal cashier or campaign image
     * path based on system configuration
     *
     * @param string $type Type
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImage($type = self::TYPE_CAMPAIGN)
    {
        $image = $this->getData("${type}_image");
        $externalImage = $this->getData("external_${type}_image");

        if (!empty($externalImage)) {
            return $externalImage;
        }

        return sprintf(
            '%s%s/%s',
            $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            Image::UPLOAD_DIR,
            $image
        );
    }
}
