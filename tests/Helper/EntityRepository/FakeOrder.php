<?php
namespace inklabs\kommerce\tests\Helper\EntityRepository;

use inklabs\kommerce\EntityRepository\OrderInterface;
use inklabs\kommerce\Lib\ReferenceNumber;
use inklabs\kommerce\Entity;

class FakeOrder extends AbstractFake implements OrderInterface
{
    public function __construct()
    {
        $this->setReturnValue(new Entity\Order);
    }

    public function find($id)
    {
        return $this->getReturnValue();
    }

    public function getLatestOrders(Entity\Pagination & $pagination = null)
    {
        return $this->getReturnValueAsArray();
    }

    public function getOrdersByUserId($userId)
    {
        return $this->getReturnValueAsArray();
    }

    public function create(Entity\Order & $order)
    {
    }

    public function save(Entity\Order & $order)
    {
    }

    public function persist(Entity\Order & $order)
    {
    }

    public function setReferenceNumberGenerator(ReferenceNumber\GeneratorInterface $referenceNumberGenerator)
    {
    }

    public function referenceNumberExists($referenceNumber)
    {
    }
}