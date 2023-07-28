<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Plugin\Framework\App\Action;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\Exception\NoSuchEntityException;

class AbstractAction
{

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        callable $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {

        $customerId = $this->customerSession->getCustomerId();
        $this->httpContext->setValue(
            'current_customer_id',
            $customerId,
            0
        );

        return $proceed($request);
    }
}
