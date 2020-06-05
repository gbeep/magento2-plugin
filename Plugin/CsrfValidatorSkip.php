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

namespace Gobeep\Ecommerce\Plugin;

use Closure;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\RequestInterface;

class CsrfValidatorSkip
{
    /**
     * @param CsrfValidator $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'gobeep') {
            return; // Skip CSRF check
        }
        $proceed($request, $action); // Proceed Magento 2 core functionalities
    }
}