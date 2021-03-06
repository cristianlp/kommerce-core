<?php
namespace inklabs\kommerce\Action\Attribute\Query;

use inklabs\kommerce\Lib\Uuid;
use inklabs\kommerce\Lib\UuidInterface;

final class GetAttributeValueRequest
{
    /** @var UuidInterface */
    private $attributeValueId;

    /**
     * @param string $attributeValueId
     */
    public function __construct($attributeValueId)
    {
        $this->attributeValueId = Uuid::fromString($attributeValueId);
    }

    public function getAttributeValueId()
    {
        return $this->attributeValueId;
    }
}
