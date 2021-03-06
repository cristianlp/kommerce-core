<?php
namespace inklabs\kommerce\Action\Attribute;

use inklabs\kommerce\Entity\AttributeChoiceType;
use inklabs\kommerce\Lib\Command\CommandInterface;
use inklabs\kommerce\Lib\Uuid;
use inklabs\kommerce\Lib\UuidInterface;

abstract class AbstractAttributeCommand implements CommandInterface
{
    /** @var UuidInterface */
    protected $attributeId;

    /** @var string */
    protected $name;

    /** @var string */
    private $choiceTypeSlug;

    /** @var int */
    private $sortOrder;

    /** @var null|string */
    protected $description;

    /**
     * @param string $name
     * @param string $choiceTypeSlug
     * @param int $sortOrder
     * @param null|string $description
     * @param string $attributeId
     */
    public function __construct(
        $name,
        $choiceTypeSlug,
        $sortOrder,
        $description,
        $attributeId
    ) {
        $this->attributeId = Uuid::fromString($attributeId);
        $this->name = $name;
        $this->choiceTypeSlug = $choiceTypeSlug;
        $this->sortOrder = $sortOrder;
        $this->description = $description;
    }

    public function getAttributeId()
    {
        return $this->attributeId;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return AttributeChoiceType
     */
    public function getChoiceType()
    {
        return AttributeChoiceType::createBySlug($this->choiceTypeSlug);
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
