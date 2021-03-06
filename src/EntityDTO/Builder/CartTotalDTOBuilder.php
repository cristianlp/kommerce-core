<?php
namespace inklabs\kommerce\EntityDTO\Builder;

use inklabs\kommerce\Entity\CartTotal;
use inklabs\kommerce\EntityDTO\CartTotalDTO;

class CartTotalDTOBuilder implements DTOBuilderInterface
{
    /** @var CartTotal */
    protected $entity;

    /** @var CartTotalDTO */
    protected $entityDTO;

    /** @var DTOBuilderFactoryInterface */
    protected $dtoBuilderFactory;

    public function __construct(CartTotal $cartTotal, DTOBuilderFactoryInterface $dtoBuilderFactory)
    {
        $this->entity = $cartTotal;
        $this->dtoBuilderFactory = $dtoBuilderFactory;

        $this->entityDTO = new CartTotalDTO;
        $this->entityDTO->origSubtotal     = $this->entity->origSubtotal;
        $this->entityDTO->subtotal         = $this->entity->subtotal;
        $this->entityDTO->taxSubtotal      = $this->entity->taxSubtotal;
        $this->entityDTO->discount         = $this->entity->discount;
        $this->entityDTO->shipping         = $this->entity->shipping;
        $this->entityDTO->shippingDiscount = $this->entity->shippingDiscount;
        $this->entityDTO->tax              = $this->entity->tax;
        $this->entityDTO->total            = $this->entity->total;
        $this->entityDTO->savings          = $this->entity->savings;
    }

    /**
     * @return static
     */
    public function withCoupons()
    {
        foreach ($this->entity->coupons as $key => $coupon) {
            $this->entityDTO->coupons[$key] = $this->dtoBuilderFactory
                ->getCouponDTOBuilder($coupon)
                ->build();
        }

        return $this;
    }

    /**
     * @return static
     */
    public function withCartPriceRules()
    {
        foreach ($this->entity->getCartPriceRules() as $cartPriceRule) {
            $this->entityDTO->cartPriceRules[] = $this->dtoBuilderFactory
                ->getCartPriceRuleDTOBuilder($cartPriceRule)
                ->build();
        }

        return $this;
    }

    /**
     * @return static
     */
    public function withAllData()
    {
        return $this
            ->withCoupons()
            ->withCartPriceRules();
    }

    protected function preBuild()
    {
    }

    public function build()
    {
        $this->preBuild();
        unset($this->entity);
        return $this->entityDTO;
    }
}
