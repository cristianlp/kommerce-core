<?php
namespace inklabs\kommerce\Action\CartPriceRule\Query;

use inklabs\kommerce\EntityDTO\Builder\CartPriceRuleDTOBuilder;
use inklabs\kommerce\EntityDTO\Builder\PaginationDTOBuilder;
use inklabs\kommerce\EntityDTO\CartPriceRuleDTO;
use inklabs\kommerce\EntityDTO\PaginationDTO;

class ListCartPriceRulesResponse implements ListCartPriceRulesResponseInterface
{
    /** @var CartPriceRuleDTOBuilder[] */
    protected $couponDTOBuilders = [];

    /** @var PaginationDTOBuilder */
    protected $paginationDTOBuilder;

    public function addCartPriceRuleDTOBuilder(CartPriceRuleDTOBuilder $couponDTOBuilder)
    {
        $this->couponDTOBuilders[] = $couponDTOBuilder;
    }

    public function setPaginationDTOBuilder(PaginationDTOBuilder $paginationDTOBuilder)
    {
        $this->paginationDTOBuilder = $paginationDTOBuilder;
    }

    /**
     * @return CartPriceRuleDTO[]|\Generator
     */
    public function getCartPriceRuleDTOs()
    {
        $couponDTOs = [];
        foreach ($this->couponDTOBuilders as $couponDTOBuilder) {
            $couponDTOs[] = $couponDTOBuilder->build();
        }
        return $couponDTOs;
    }

    /**
     * @return PaginationDTO
     */
    public function getPaginationDTO()
    {
        return $this->paginationDTOBuilder->build();
    }
}
