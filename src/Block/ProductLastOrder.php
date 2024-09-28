<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Registry;

class ProductLastOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * Constructor.
     *
     * @param Context $context The context of the block.
     * @param Product $product The current product.
     * @param SessionFactory $customerSessionFactory The customer session factory.
     * @param Registry $registry The registry of the page.
     */
    public function __construct(
        Context $context,
        Product $product,
        SessionFactory $customerSessionFactory,
        Registry $registry
    ) {
        /**
         * Call the parent constructor.
         */
        parent::__construct($context);
        $this->product = $product;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->registry = $registry;
    }

    /**
     * Retrieves the product ID.
     *
     * @return int|null The product ID if available, null otherwise.
     */
    public function getProductId()
    {
        if ($product = $this->registry->registry('current_product')) {
            return $product->getId();
        }

        return null;
    }

    /**
     * Retrieves the customer ID from the current session.
     *
     * @return int The customer ID if the customer is logged in, null otherwise.
     */
    public function getCustomerId()
    {
        $customerSession = $this->customerSessionFactory->create();
        return $customerSession->getCustomerId();
    }
}
