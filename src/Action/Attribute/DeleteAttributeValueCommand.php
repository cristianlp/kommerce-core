<?php
namespace inklabs\kommerce\Action\Attribute;

use inklabs\kommerce\Lib\Command\CommandInterface;
use inklabs\kommerce\Lib\Uuid;
use inklabs\kommerce\Lib\UuidInterface;

final class DeleteAttributeValueCommand implements CommandInterface
{
    /** @var UuidInterface */
    private $attributeIValued;

    /**
     * @param string $attributeIValued
     */
    public function __construct($attributeIValued)
    {
        $this->attributeIValued = Uuid::fromString($attributeIValued);
    }

    public function getAttributeValueId()
    {
        return $this->attributeIValued;
    }
}
