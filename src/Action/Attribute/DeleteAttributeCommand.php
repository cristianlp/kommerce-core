<?php
namespace inklabs\kommerce\Action\Attribute;

use inklabs\kommerce\Lib\Command\CommandInterface;
use inklabs\kommerce\Lib\Uuid;
use inklabs\kommerce\Lib\UuidInterface;

final class DeleteAttributeCommand implements CommandInterface
{
    /** @var UuidInterface */
    private $attributeId;

    /**
     * @param string $attributeId
     */
    public function __construct($attributeId)
    {
        $this->attributeId = Uuid::fromString($attributeId);
    }

    public function getAttributeId()
    {
        return $this->attributeId;
    }
}
