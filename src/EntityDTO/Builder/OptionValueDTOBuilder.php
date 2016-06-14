<?php
namespace inklabs\kommerce\EntityDTO\Builder;

use inklabs\kommerce\Entity\OptionValue;
use inklabs\kommerce\EntityDTO\OptionValueDTO;

class OptionValueDTOBuilder implements DTOBuilderInterface
{
    use IdDTOBuilderTrait, TimeDTOBuilderTrait;

    /** @var OptionValue */
    protected $entity;

    /** @var OptionValueDTO */
    protected $entityDTO;

    /** @var DTOBuilderFactoryInterface */
    protected $dtoBuilderFactory;

    public function __construct(OptionValue $optionValue, DTOBuilderFactoryInterface $dtoBuilderFactory)
    {
        $this->entity = $optionValue;
        $this->dtoBuilderFactory = $dtoBuilderFactory;

        $this->entityDTO = new OptionValueDTO;
        $this->setId();
        $this->setTime();
        $this->entityDTO->name           = $this->entity->getname();
        $this->entityDTO->sku            = $this->entity->getSku();
        $this->entityDTO->shippingWeight = $this->entity->getShippingWeight();
        $this->entityDTO->sortOrder      = $this->entity->getSortOrder();
    }

    public function withOption()
    {
        $this->entityDTO->option = $this->dtoBuilderFactory
            ->getOptionDTOBuilder($this->entity->getOption())
            ->build();

        return $this;
    }

    public function withPrice()
    {
        $this->entityDTO->price = $this->dtoBuilderFactory
            ->getPriceDTOBuilder($this->entity->getPrice())
            ->build();

        return $this;
    }

    public function withAllData()
    {
        return $this
            ->withOption()
            ->withPrice();
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
