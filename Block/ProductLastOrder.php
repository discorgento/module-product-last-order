<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\App\Http\Context as HttpContext;

class ProductLastOrder extends \Magento\Framework\View\Element\Template
{
    protected $registry;
    protected $customerSession;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Product $product
     * @param Session $customerSession
     * @param Registry $registry
     * @param HttpContext $httpContext
     */
    public function __construct(
        Context $context,
        Product $product,
        Session $customerSession,
        Registry $registry,
        HttpContext $httpContext
    ) {
        parent::__construct($context);
        $this->product = $product;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->httpContext = $httpContext;
    }
    
    /**
     * Retrieves the product ID.
     *
     * @return int|null The product ID if available, null otherwise.
     */
    public function getProductId()
    {
        // Verifica se existe um produto no registro e recupera o ID do produto
        if ($product = $this->registry->registry('current_product')) {
            return $product->getId();
        }

        return null;
    }

    /**
     * Retrieves the customer ID.
     *
     * @return int|null The customer ID if available, null otherwise.
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('current_customer_id');
    }
}
