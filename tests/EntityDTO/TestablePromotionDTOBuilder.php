<?php
namespace inklabs\kommerce\tests\EntityDTO;

use inklabs\kommerce\EntityDTO\Builder\AbstractPromotionDTOBuilder;
use inklabs\kommerce\tests\Entity\TestablePromotion;

class TestablePromotionDTOBuilder extends AbstractPromotionDTOBuilder
{
    public function __construct(TestablePromotion $testablePromotion)
    {
        $this->promotionDTO = new TestablePromotionDTO;

        parent::__construct($testablePromotion);
    }
}