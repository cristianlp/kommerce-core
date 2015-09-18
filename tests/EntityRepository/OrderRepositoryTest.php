<?php
namespace inklabs\kommerce\EntityRepository;

use inklabs\kommerce\Entity;
use inklabs\kommerce\Lib\ReferenceNumber\HashSegmentGenerator;
use inklabs\kommerce\Lib\ReferenceNumber\SequentialGenerator;
use inklabs\kommerce\Lib\ReferenceNumber\RepositoryInterface;
use inklabs\kommerce\Service;
use inklabs\kommerce\tests\Helper;

class OrderRepositoryTest extends Helper\DoctrineTestCase
{
    protected $metaDataClassNames = [
        'kommerce:Coupon',
        'kommerce:Order',
        'kommerce:OrderItem',
        'kommerce:Payment\Payment',
        'kommerce:Product',
        'kommerce:User',
        'kommerce:Cart',
        'kommerce:TaxRate',
    ];

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    public function setUp()
    {
        $this->orderRepository = $this->repository()->getOrderRepository();
    }

    public function setupOrder($referenceNumber = null)
    {
        $uniqueId = crc32($referenceNumber);

        $product = $this->getDummyProduct($uniqueId);
        $price = $this->getDummyPrice();

        $user = $this->getDummyUser($uniqueId);
        $orderItem = $this->getDummyOrderItem($product, $price);
        $cartTotal = $this->getDummyCartTotal();

        $taxRate = $this->getDummyTaxRate();

        $order = $this->getDummyOrder($cartTotal, [$orderItem]);
        $order->setUser($user);
        $order->setReferenceNumber($referenceNumber);
        $order->setTaxRate($taxRate);

        $this->entityManager->persist($product);
        $this->entityManager->persist($user);
        $this->entityManager->persist($taxRate);

        $this->orderRepository->create($order);

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $order;
    }

    public function testCRUD()
    {
        $order = $this->setupOrder();

        $order->setExternalId('newExternalId');
        $this->assertSame(null, $order->getUpdated());
        $this->orderRepository->save($order);
        $this->assertTrue($order->getUpdated() instanceof \DateTime);

        $this->orderRepository->remove($order);
        $this->assertSame(null, $order->getId());
    }

    public function testFind()
    {
        $this->setupOrder();

        $this->setCountLogger();

        $order = $this->orderRepository->find(1);

        $order->getOrderItems()->toArray();
        $order->getPayments()->toArray();
        $order->getUser()->getCreated();
        $order->getCoupons()->toArray();
        $order->getTaxRate()->getCreated();

        $this->assertTrue($order instanceof Entity\Order);
        $this->assertSame(5, $this->countSQLLogger->getTotalQueries());
    }

    public function testGetLatestOrders()
    {
        $this->setupOrder();

        $orders = $this->orderRepository->getLatestOrders();

        $this->assertTrue($orders[0] instanceof Entity\Order);
    }

    public function testCreateWithSequentialReferenceNumber()
    {
        $this->orderRepository = $this->repository()->getOrderWithSequentialGenerator();

        $order = $this->setupOrder();

        $this->assertSame(1, $order->getId());
        $this->assertSame('0000000001', $order->getReferenceNumber());
    }

    public function testCreateWithHashReferenceNumber()
    {
        mt_srand(0);

        $this->orderRepository = $this->repository()->getOrderWithHashSegmentGenerator();

        $order = $this->setupOrder();

        $this->assertSame(1, $order->getId());
        $this->assertSame('963-1273124-1535857', $order->getReferenceNumber());
    }

    public function testCreateWithHashReferenceNumberAndDuplicateFailure()
    {
        mt_srand(0);

        // Simulate 3 duplicates in a row.
        $this->setupOrder('963-1273124-1535857');
        $this->setupOrder('324-1294424-1842424');
        $this->setupOrder('117-1819459-9097917');

        $this->orderRepository->setReferenceNumberGenerator(
            new HashSegmentGenerator($this->orderRepository)
        );

        $order = $this->setupOrder();

        $this->assertSame(4, $order->getId());
        $this->assertSame(null, $order->getReferenceNumber());
    }

    public function testGetOrdersByUserId()
    {
        $this->setupOrder();

        $orders = $this->orderRepository->getOrdersByUserId(1);

        $this->assertTrue($orders[0] instanceof Entity\Order);
        $this->assertSame(1, $orders[0]->getUser()->getId());
    }
}