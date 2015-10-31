<?php
namespace inklabs\kommerce\EntityDTO\Builder;

use inklabs\kommerce\Entity\ShipmentTracker;
use inklabs\kommerce\EntityDTO\ShipmentTrackerDTO;
use inklabs\kommerce\Lib\BaseConvert;

class ShipmentTrackerDTOBuilder
{
    /** @var ShipmentTracker */
    protected $shipmentTracker;

    /** @var ShipmentTrackerDTO */
    protected $shipmentTrackerDTO;

    public function __construct(ShipmentTracker $shipmentTracker)
    {
        $this->shipmentTracker = $shipmentTracker;

        $this->shipmentTrackerDTO = new ShipmentTrackerDTO;
        $this->shipmentTrackerDTO->id           = $this->shipmentTracker->getId();
        $this->shipmentTrackerDTO->encodedId    = BaseConvert::encode($this->shipmentTracker->getId());
        $this->shipmentTrackerDTO->created      = $this->shipmentTracker->getCreated();
        $this->shipmentTrackerDTO->updated      = $this->shipmentTracker->getUpdated();
        $this->shipmentTrackerDTO->carrier      = $this->shipmentTracker->getCarrier();
        $this->shipmentTrackerDTO->carrierText  = $this->shipmentTracker->getCarrierText();
        $this->shipmentTrackerDTO->trackingCode = $this->shipmentTracker->getTrackingCode();
        $this->shipmentTrackerDTO->externalId   = $this->shipmentTracker->getExternalId();

        if ($this->shipmentTracker->getShipmentRate() !== null) {
            $this->shipmentTrackerDTO->shipmentRate = $this->shipmentTracker->getShipmentRate()->getDTOBuilder()
                ->build();
        }

        if ($this->shipmentTracker->getShipmentLabel() !== null) {
            $this->shipmentTrackerDTO->shipmentLabel = $this->shipmentTracker->getShipmentLabel()->getDTOBuilder()
                ->build();
        }
    }

    public function build()
    {
        return $this->shipmentTrackerDTO;
    }
}
