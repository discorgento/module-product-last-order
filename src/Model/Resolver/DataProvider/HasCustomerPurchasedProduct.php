<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Model\Resolver\DataProvider;

class HasCustomerPurchasedProduct
{
    /**
     * This is the constructor method.
     */
    public function __construct()
    {
    }

    /**
     * Retrieves whether the customer has purchased the product.
     *
     * @return string The provided data.
     */
    public function getHasCustomerPurchasedProduct()
    {
        return 'proviced data';
    }
}
