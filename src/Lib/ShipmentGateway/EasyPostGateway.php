<?php
namespace inklabs\kommerce\Lib\ShipmentGateway;

use DateTime;
use EasyPost;
use inklabs\kommerce\Entity\Money;
use inklabs\kommerce\Entity\ShipmentCarrierType;
use inklabs\kommerce\Entity\ShipmentLabel;
use inklabs\kommerce\Entity\ShipmentRate;
use inklabs\kommerce\Entity\ShipmentTracker;
use inklabs\kommerce\EntityDTO\OrderAddressDTO;
use inklabs\kommerce\EntityDTO\ParcelDTO;
use inklabs\kommerce\Lib\UuidInterface;

class EasyPostGateway implements ShipmentGatewayInterface
{
    /** @var OrderAddressDTO */
    private $fromAddress;

    /**
     * @param string $apiKey
     * @param OrderAddressDTO $fromAddress
     */
    public function __construct($apiKey, OrderAddressDTO $fromAddress)
    {
        $this->fromAddress = $fromAddress;
        EasyPost\EasyPost::setApiKey($apiKey);
    }

    /**
     * @param OrderAddressDTO $toAddress
     * @param ParcelDTO $parcel
     * @param null|OrderAddressDTO $fromAddress
     * @return ShipmentRate[]
     */
    public function getRates(OrderAddressDTO $toAddress, ParcelDTO $parcel, OrderAddressDTO $fromAddress = null)
    {
        if ($fromAddress === null) {
            $fromAddress = $this->fromAddress;
        }

        $shipment = EasyPost\Shipment::create([
            'from_address' => $this->getEasyPostAddress($fromAddress),
            'to_address' => $this->getEasyPostAddress($toAddress),
            'parcel' => $this->getEasyPostParcel($parcel),
        ]);

        $shipmentRates = [];
        foreach ($shipment->rates as $rate) {
            $shipmentRates[] = $this->getShipmentRateFromEasyPostRate($rate);
        }

         $this->sortShipmentRatesLowestToHighest($shipmentRates);

        return $shipmentRates;
    }

    /**
     * @param OrderAddressDTO $toAddress
     * @param ParcelDTO $parcel
     * @return ShipmentRate[]
     */
    public function getTrimmedRates(OrderAddressDTO $toAddress, ParcelDTO $parcel)
    {
        $shipmentRates = $this->getRates($toAddress, $parcel);

        /** @var ShipmentRate[] $newShipmentRates */
        $newShipmentRates = [];
        foreach ($shipmentRates as $shipmentRate) {
            $deliveryMethodId = $shipmentRate->getDeliveryMethod()->getId();

            if (! isset($newShipmentRates[$deliveryMethodId])) {
                $newShipmentRates[$deliveryMethodId] = $shipmentRate;
                continue;
            }

            if ($shipmentRate->getRate()->getAmount() < $newShipmentRates[$deliveryMethodId]->getRate()->getAmount()) {
                $newShipmentRates[$deliveryMethodId] = $shipmentRate;
            }
        }

        return $newShipmentRates;
    }

    /**
     * @param string $shipmentRateExternalId
     * @return ShipmentRate
     */
    public function getShipmentRateByExternalId($shipmentRateExternalId)
    {
        $rate = EasyPost\Rate::retrieve($shipmentRateExternalId);
        $shipmentRate = $this->getShipmentRateFromEasyPostRate($rate);
        return $shipmentRate;
    }

    /**
     * @param string $shipmentExternalId
     * @param string $rateExternalId
     * @param null|UuidInterface $id
     * @return ShipmentTracker
     */
    public function buy($shipmentExternalId, $rateExternalId, UuidInterface $id = null)
    {
        $shipment = new EasyPost\Shipment($shipmentExternalId);
        $shipment->buy([
            'rate' => [
                'id' => $rateExternalId
            ]
        ]);

        return $this->getShipmentTrackerFromEasyPostShipment($shipment, $id);
    }

    /**
     * @param OrderAddressDTO $address
     * @return array
     */
    protected function getEasyPostAddress(OrderAddressDTO $address)
    {
        return [
            'name' => $address->fullName,
            'company' => $address->company,
            'street1' => $address->address1,
            'street2' => $address->address2,
            'city' => $address->city,
            'state' => $address->state,
            'zip' => $address->zip5,
            'phone' => $address->phone,
            'country' => $address->country,
            'residential' => $address->isResidential
        ];
    }

    private function getEasyPostParcel(ParcelDTO $parcel)
    {
        return [
            'length' => $parcel->length,
            'width' => $parcel->width,
            'height' => $parcel->height,
            'weight' => $parcel->weight,
            'predefined_package' => $parcel->predefinedPackage,
        ];
    }

    private function getShipmentRateFromEasyPostRate($rate)
    {
        $shipmentRate = new ShipmentRate(new Money($rate->rate * 100, $rate->currency));
        $shipmentRate->setExternalId($rate->id);
        $shipmentRate->setShipmentExternalId($rate->shipment_id);
        $shipmentRate->setCarrier($rate->carrier);
        $shipmentRate->setService($rate->service);
        $shipmentRate->setIsDeliveryDateGuaranteed($rate->delivery_date_guaranteed);

        if ($rate->list_rate !== null) {
            $shipmentRate->setListRate(new Money($rate->list_rate * 100, $rate->list_currency));
        }

        if ($rate->retail_rate !== null) {
            $shipmentRate->setRetailRate(new Money($rate->retail_rate * 100, $rate->retail_currency));
        }

        if (! empty($rate->delivery_date)) {
            $shipmentRate->setDeliveryDate(new DateTime($rate->delivery_date));
        }

        if (! empty($rate->delivery_days)) {
            $shipmentRate->setDeliveryDays($rate->delivery_days);
        }

        return $shipmentRate;
    }

    /**
     * @param ShipmentRate[] & $shipmentRates
     */
    protected function sortShipmentRatesLowestToHighest(& $shipmentRates)
    {
        usort(
            $shipmentRates,
            function (ShipmentRate $a, ShipmentRate $b) {
                return ($a->getRate()->getAmount() > $b->getRate()->getAmount());
            }
        );
    }

    private function getShipmentTrackerFromEasyPostShipment($shipment, UuidInterface $id = null)
    {
        switch (strtolower($shipment->tracker->carrier)) {
            case 'ups':
                $carrier = ShipmentCarrierType::ups();
                break;
            case 'usps':
                $carrier = ShipmentCarrierType::usps();
                break;
            case 'fedex':
                $carrier = ShipmentCarrierType::fedex();
                break;
            default:
                $carrier = ShipmentCarrierType::unknown();
        }

        $shipmentTracker = new ShipmentTracker($carrier, $shipment->tracking_code, $id);
        $shipmentTracker->setExternalId($shipment->id);
        $shipmentTracker->setShipmentLabel($this->getShipmentLabelFromEasyPostShipment($shipment));
        $shipmentTracker->setShipmentRate($this->getShipmentRateFromEasyPostRate($shipment->selected_rate));
        return $shipmentTracker;
    }

    private function getShipmentLabelFromEasyPostShipment($shipment)
    {
        $label = $shipment->postage_label;

        $shipmentLabel = new ShipmentLabel;
        $shipmentLabel->setExternalId($label->id);
        $shipmentLabel->setResolution($label->label_resolution);
        $shipmentLabel->setSize($label->label_size);
        $shipmentLabel->setType($label->label_type);
        $shipmentLabel->setUrl($label->label_url);
        $shipmentLabel->setFileType($label->label_file_type);
        $shipmentLabel->setPdfUrl($label->label_pdf_url);
        $shipmentLabel->setEpl2Url($label->label_epl2_url);
        $shipmentLabel->setZplUrl($label->zpl_url);

        return $shipmentLabel;
    }
}
