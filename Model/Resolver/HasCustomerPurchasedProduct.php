<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class HasCustomerPurchasedProduct implements ResolverInterface
{

    protected $hasCustomerPurchasedProductDataProvider;
    protected $customerRepository;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $urlHelper;
    protected $dateTimeFormatter;
    protected $dateTimeFactory;


    /**
     * Class constructor.
     *
     * @param DataProvider\HasCustomerPurchasedProduct $hasCustomerPurchasedProductDataProvider The data provider for checking if the customer has purchased a product.
     * @param CustomerRepositoryInterface $customerRepository The repository for accessing customer data.
     * @param OrderRepositoryInterface $orderRepository The repository for accessing order data.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder The search criteria builder.
     * @param UrlInterface $urlHelper The url helper.
     * @param DateTimeFormatterInterface $dateTimeFormatter The date time formatter.
     * @param DateTimeFactory $dateTimeFactory The date time factory.
     */
    public function __construct(
        DataProvider\HasCustomerPurchasedProduct $hasCustomerPurchasedProductDataProvider,
        CustomerRepositoryInterface $customerRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlInterface $urlHelper,
        DateTimeFormatterInterface $dateTimeFormatter,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->hasCustomerPurchasedProductDataProvider = $hasCustomerPurchasedProductDataProvider;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlHelper = $urlHelper;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $args['customerId'];
        $productId = $args['productId'];

        try {
            $customer = $this->customerRepository->getById($customerId);
            $customerEmail = $customer->getEmail();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_email', $customerEmail, 'eq')
                ->create();

            $orders = $this->orderRepository->getList($searchCriteria);

            foreach ($orders as $order) {
                foreach ($order->getAllVisibleItems() as $item) {
                    if ($item->getProductId() == $productId) {

                        return [
                            'hasPurchased' => true,
                            'orderLink' => $this->getOrderLink($order),
                            'orderDate' => $this->getFormattedOrderDate($order)
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return [
                'hasPurchased' => false,
                'orderLink' => '',
                'orderDate' => ''
            ];
        }

        return [
            'hasPurchased' => false,
            'orderLink' => '',
            'orderDate' => ''
        ];
    }

    /**
     * Helper method to get order preview link.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    private function getOrderLink(OrderInterface $order)
    {
        try {
            return $this->urlHelper->getUrl('sales/order/view', ['order_id' => $order->getEntityId()]);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Helper method to get the formatted purchase date.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    private function getFormattedOrderDate(OrderInterface $order)
    {
        try {
            $createdAt = $order->getCreatedAt();
            $createdAtObject = new \DateTime($createdAt);
            return $createdAtObject->format('Y-m-d H:i:s');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return '';
        }
    }
}
