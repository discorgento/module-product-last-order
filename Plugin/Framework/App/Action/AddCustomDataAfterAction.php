<?php
declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Plugin\Framework\App\Action;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;

class AddCustomDataAfterAction
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Sets the current client ID in the HTTP context after the original action has been performed.
     *
     * @param ActionInterface $subject
     * @param mixed $result
     * @param RequestInterface $request
     * @return mixed
     */
    public function afterDispatch(
        ActionInterface $subject,
        $result,
        RequestInterface $request
    ) {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $this->httpContext->setValue('current_customer_id', $customerId, 0);
        }

        return $result;
    }
}
