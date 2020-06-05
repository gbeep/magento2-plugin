[![Gitter](https://badges.gitter.im/gbeep/magento2-plugin.svg)](https://gitter.im/gbeep/magento2-plugin?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

## GoBeep - Ecommerce module - Magento 2x

Gobeep’s extension for Magento 2.x is designed to help clients who use the Magento platform to quickly and seamlessly generate links to validate game codes distributed by Gobeep gaming solution.
And to (optionally) display the game on the frontend.

### Installation

#### Using Zip file

 - Unzip the zip file in `app/code/Gobeep`
 - Enable the module by running `php bin/magento module:enable Gobeep_Ecommerce`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

##### Using composer

 - Install the module composer by running `composer require gbeep/magento2-plugin`
 - enable the module by running `php bin/magento module:enable Gobeep_Ecommerce`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Setup

After the extension is installed, log in to the Magento Account, you'll find the `Gobeep` menu in the left sidebar.

### Inputs

| Name                    | Type             | Description                                                                   |  Default  | Required |
| ----------------------- | ---------------- | ----------------------------------------------------------------------------- | --------- | -------- |
| enabled                  | yes/no           | Whether extension is disabled or enabled                                      | No        | Yes      |
| environment             | text             | Gobeep Environment                                                            | stable    | No       |
| region                  | text             | Gobeep Region                                                                 | eu        | No       |
| campaign_id             | text             | Campaign ID (used in `Gobeep_Ecommerce_Block_Link` block)                     |           | Yes      |
| cashier_id              | text             | Cashier ID (used in `Gobeep_Ecommerce_Block_Link` block)                      |           | Yes      |
| secret                  | text             | Secret given by `GoBeep` for signing requests and verify incoming webhooks    |           | Yes      |
| from_date               | date             | Start date (Date will be checked to determine if module is enabled or not)    |           | No       |
| to_date                 | date             | End date (Date will be checked to determine if module is enabled or not)      |           | No       |
| eligible_days           | multiselect      | Days of the week when module is enabled                                       |           | No       |
| cashier_image           | image            | Cashier link image (used in `Gobeep_Ecommerce_Block_Link` block)              |           | Yes*     |
| cashier_external_image  | string           | Cashier link image URL (used in `Gobeep_Ecommerce_Block_Link` block)          |           | Yes*     |
| campaign_image          | image            | Campaign link image (used in `Gobeep_Ecommerce_Block_Link` block)             |           | Yes*     |
| campaign_external_image | string           | Campaign link image URL (used in `Gobeep_Ecommerce_Block_Link` block)         |           | Yes*     |
| notify                  | yes/no           | Whether we should notify users when they are winning or they are refunded     |           | No       |
| winning_email_template  | string           | Email Notification template (winning)                                         |           | No       |
| refund_email_template   | string           | Email Notification template (refund)                                          |           | No       |

<sub>(*) Use one or another (external or internal)</sub>

#### Blocks

##### Link block

We recommend to use the `New Order` email to integrate the cashier/campaign links. The `\Magento\Sales\Model\Order` object **MUST** be passed to the block when generating `cashier` links.

###### cashier link

```{{block class='Magento\\Framework\\View\\Element\\Template' area='frontend' template='Gobeep_Ecommerce::email/link.phtml' order=$order for='cashier'}}```

###### campaign link

```{{block class='Magento\\Framework\\View\\Element\\Template' area='frontend' template='Gobeep_Ecommerce::email/link.phtml' order=$order for='campaign'}}```

##### Interstitial

The `Gobeep\Ecommerce\Block\Link` block can be used to display the game UI on `frontend`, this should be done on `cms_home` block ideally. Example below.

```
<referenceContainer name="content">
  <block type="Gobeep\Ecommerce\Block\Link" name="gobeep_insterstitial" template="..."></block>
</referenceContainer>
```

#### Transactional Email

If you want to use email notifications. Here's the list of templates.
:warning: You should create a new template for all these transactional emails in the `admin`, there's no default template for them. 

| Name                             | Type             |
| -------------------------------- | ---------------- |
| Gobeep Ecommerce Status Refunded | Refund email     |
| Gobeep Ecommerce Status Winning  | Winning email    |


### Support

For any technical issue with the module, please open an issue on `Github`.
