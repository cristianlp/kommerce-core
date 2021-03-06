<?php
namespace inklabs\kommerce\Action\Product\Query;

use inklabs\kommerce\EntityDTO\Builder\ProductDTOBuilder;
use inklabs\kommerce\EntityDTO\ProductDTO;
use inklabs\kommerce\Lib\PricingInterface;

class GetRelatedProductsResponse implements GetRelatedProductsResponseInterface
{
    /** @var ProductDTOBuilder[] */
    private $productDTOBuilders = [];

    /** @var PricingInterface */
    private $pricing;

    public function __construct(PricingInterface $pricing)
    {
        $this->pricing = $pricing;
    }

    public function addProductDTOBuilder(ProductDTOBuilder $productDTOBuilder)
    {
        $this->productDTOBuilders[] = $productDTOBuilder;
    }

    /**
     * @return ProductDTO[]
     */
    public function getProductDTOs()
    {
        $productDTOs = [];
        foreach ($this->productDTOBuilders as $productDTOBuilder) {
            $productDTOs[] = $productDTOBuilder
                ->withPrice($this->pricing)
                ->build();
        }
        return $productDTOs;
    }
}
