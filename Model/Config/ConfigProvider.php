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

namespace Gobeep\Ecommerce\Model\Config;

use Gobeep\Ecommerce\Api\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ScopeInterfaceAlias;

/**
 * Class ConfigProvider
 * @package Gobeep\Ecommerce\Model\Config
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * System config constants
     */
    const XML_PATH_ENABLED = 'gobeep/ecommerce/enabled';
    const XML_PATH_ENVIRONMENT = 'gobeep/ecommerce/environment';
    const XML_PATH_REGION = 'gobeep/ecommerce/region';
    const XML_PATH_CASHIER_ID = 'gobeep/ecommerce/cashier_id';
    const XML_PATH_CAMPAIGN_ID = 'gobeep/ecommerce/campaign_id';
    const XML_PATH_SECRET = 'gobeep/ecommerce/secret';
    const XML_PATH_FROM_DATE = 'gobeep/ecommerce/from_date';
    const XML_PATH_TO_DATE = 'gobeep/ecommerce/to_date';
    const XML_PATH_ELIGIBLE_DAYS = 'gobeep/ecommerce/eligible_days';
    const XML_PATH_CASHIER_IMAGE = 'gobeep/ecommerce/cashier_image';
    const XML_PATH_EXT_CASHIER_IMAGE = 'gobeep/ecommerce/cashier_external_image';
    const XML_PATH_CAMPAIGN_IMAGE = 'gobeep/ecommerce/campaign_image';
    const XML_PATH_EXT_CAMPAIGN_IMAGE = 'gobeep/ecommerce/campaign_external_image';
    const XML_PATH_NOTIFY = 'gobeep/ecommerce/notify';
    const XML_PATH_REFUND_EMAIL_TEMPLATE = 'gobeep/ecommerce/refund_email_template';
    const XML_PATH_WINNING_EMAIL_TEMPLATE = 'gobeep/ecommerce/winning_email_template';
    const XML_PATH_TIMEZONE = 'general/locale/timezone';
    const XML_PATH_SENDER_EMAIL_IDENTITY = 'email_section/sendmail/sender';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $store
     * @return bool
     */
    public function getIsEnabled($store): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getEnvironment($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENVIRONMENT, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getRegion($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_REGION, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCashierId($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CASHIER_ID, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCampaignId($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CAMPAIGN_ID, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getSecret($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_SECRET, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getFromDate($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_FROM_DATE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getToDate($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_TO_DATE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return int
     */
    public function getEligibleDays($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ELIGIBLE_DAYS, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCashierImage($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CASHIER_IMAGE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getExtCashierImage($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_EXT_CASHIER_IMAGE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCampaignImage($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CAMPAIGN_IMAGE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getExtCampaignImage($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_EXT_CAMPAIGN_IMAGE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return bool
     */
    public function getNotifyEnabled($store): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_NOTIFY, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getRefundEmailTemplate($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_REFUND_EMAIL_TEMPLATE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getWinningEmailTemplate($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_WINNING_EMAIL_TEMPLATE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getTimezone($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_TIMEZONE, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }

    /**
     * @param int $store
     * @return string
     */
    public function getSenderEmail($store): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_SENDER_EMAIL_IDENTITY, ScopeInterfaceAlias::SCOPE_STORE, $store);
    }
}