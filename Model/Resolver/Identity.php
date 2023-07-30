<?php declare(strict_types=1);

namespace Discorgento\ProductLastOrder\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{
    private string $cacheTag = 'disc_productlastorder';

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $data["id"] = empty($resolvedData["customer_id"]) && empty($resolvedData["product_id"]) ? [] : [
            'customer_id' => $resolvedData["customer_id"],
            'product_id' => $resolvedData['product_id']];

        return empty($data['id']) ?
            [] : array_merge([$this->cacheTag], array_map(function ($item) {
                return sprintf('%s_%s_%s', $this->cacheTag, $item['customer_id'], $item['product_id']);
            }, $data));
    }
}
