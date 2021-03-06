<?php
namespace inklabs\kommerce\ActionHandler\Cart;

use inklabs\kommerce\Action\Cart\SetExternalShipmentRateCommand;
use inklabs\kommerce\tests\Helper\TestCase\ActionTestCase;
use inklabs\kommerce\Lib\Uuid;

class SetExternalShipmentRateHandlerTest extends ActionTestCase
{
    public function testHandle()
    {
        $cartService = $this->mockService->getCartService();
        $cartService->shouldReceive('setExternalShipmentRate')
            ->once();

        $cartId = self::UUID_HEX;
        $shipmentRateExternalId = 'shp_xxxxxxxx';
        $orderAddressDTO = $this->getDTOBuilderFactory()
            ->getOrderAddressDTOBuilder($this->dummyData->getOrderAddress())
            ->build();

        $command = new SetExternalShipmentRateCommand(
            $cartId,
            $shipmentRateExternalId,
            $orderAddressDTO
        );

        $handler = new SetExternalShipmentRateHandler($cartService);
        $handler->handle($command);
    }
}
