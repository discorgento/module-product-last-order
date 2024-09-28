<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Model\Resolver;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class HasCustomerPurchasedProduct implements ResolverInterface
{
    protected $hasCustomerPurchasedProductDataProvider;
    protected $customerRepository;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $urlHelper;
    protected $timezone;
    private $logger;


    /**
     * Constructor
     *
     * @param DataProvider\HasCustomerPurchasedProduct $hasCustomerPurchasedProductDataProvider
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlInterface $urlHelper
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataProvider\HasCustomerPurchasedProduct $hasCustomerPurchasedProductDataProvider,
        CustomerRepositoryInterface $customerRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlInterface $urlHelper,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->hasCustomerPurchasedProductDataProvider = $hasCustomerPurchasedProductDataProvider;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlHelper = $urlHelper;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $customerId = (int)($args['customerId'] ?? 0);
        $productId = (int)($args['productId'] ?? 0);

        if ($customerId === 0 || $productId === 0) {
            return $this->getPurchaseDataSkeleton();
        }

        try {
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_email', $customerEmail, 'eq')
                ->create();

            $orders = $this->orderRepository->getList($searchCriteria);
            $ordersItems = $orders->getItems();

            foreach ($ordersItems as $order) {
                if ($this->hasProductInOrder($order, $productId)) {
                    return [
                        'hasPurchased' => true,
                        'orderLink' => $this->getOrderLink($order),
                        'orderDate' => $this->formatOrderDate($order->getCreatedAt()),
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this->getPurchaseDataSkeleton();
    }

    /**
     * Check if the given product ID is present in the given order.
     *
     * This method will iterate over all visible items in the order and check if
     * any of them have the given product ID.
     *
     * @param OrderInterface $order The order to check.
     * @param int            $productId The product ID to search for.
     *
     * @return bool TRUE if the product is present in the order, FALSE otherwise.
     */
    private function hasProductInOrder(OrderInterface $order, int $productId): bool
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if ((int)$item->getProductId() === $productId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the skeleton of the purchase data array with default values.
     *
     * @return array
     */
    private function getPurchaseDataSkeleton(): array
    {
        return [
            'hasPurchased' => false,
            'orderLink' => '',
            'orderDate' => '',
        ];
    }

    /**
     * Get the link to the order page
     *
     * @param OrderInterface $order
     * @return string
     */
    private function getOrderLink(OrderInterface $order): string
    {
        try {
            return $this->urlHelper->getUrl('sales/order/view', ['order_id' => $order->getEntityId()]);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Format the order date from the given datetime string.
     *
     * @param string $createdAt The datetime string in the format that the Magento
     *                           timezone object expects.
     * @return string The formatted datetime string.
     */
    private function formatOrderDate(string $createdAt): string
    {
        return $this->timezone->formatDateTime(
            new \DateTime($createdAt, new \DateTimeZone($this->timezone->getConfigTimezone())),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE
        );
    }
}
