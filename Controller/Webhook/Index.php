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

namespace Gobeep\Ecommerce\Controller\Webhook;

use Gobeep\Ecommerce\Model\RefundRepository;
use Gobeep\Ecommerce\Model\SdkService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action
{
    /**
     * @var RefundRepository
     */
    private $refundRepository;

    /**
     * @var SdkService
     */
    private $sdkService;

    /**
     * Index constructor.
     * @param Context $context
     * @param RefundRepository $refundRepository
     * @param SdkService $sdkService
     */
    public function __construct(
        Context $context,
        RefundRepository $refundRepository,
        SdkService $sdkService
    ) {
        $this->refundRepository = $refundRepository;
        $this->sdkService = $sdkService->setStore();
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->sdkService->isReady(false)) {
            $errors[] = 'Webhook is disabled, please come back later';
            $this->getResponse()
                ->setHeader('content-type', 'application/json')
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(404);
            return;
        }

        // If request is not a POST request, return a 405 Method Not Allowed
        if (!$this->getRequest()->isPost()) {
            $errors[] = 'Webhook expects an HTTP POST';
            $this->getResponse()
                ->setHeader('content-type', 'application/json')
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(405);
            return;
        }

        // Check if we have a X-Gobeep-Signature header in the HTTP request
        // If not, send a 400 Bad Request error
        $signature = $this->getRequest()->getHeader('x-gobeep-signature');
        if (!$signature) {
            $errors[] = 'Missing x-gobeep-signature header';
            $this->getResponse()
                ->setHeader('content-type', 'application/json')
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(400);
            return;
        }

        // Verify signature, return a 403 if hashes doesn't match
        $body = $this->getRequest()->getContent();
       $digest = $this->sdkService->sign($body);
        if ($signature !== $digest) {
            $errors[] = 'Signature doesn\'t match with incoming data';
            $this->getResponse()
                ->setHeader('content-type', 'application/json')
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(403);
            return;
        }

        $errors = $this->refundRepository->processRefund(json_decode($body));

        if ($errors) {
            $this->getResponse()
                ->setHeader('content-type', 'application/json')
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(400);
            return;
        }

        // Success
        $this->getResponse()
            ->setHeader('content-type', 'application/json')
            ->setBody(json_encode(['success' => true]))
            ->setHttpResponseCode(200);
    }
}
