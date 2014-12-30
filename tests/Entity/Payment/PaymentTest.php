<?php
namespace inklabs\kommerce\Entity\Payment;

use inklabs\kommerce\Entity as Entity;
use inklabs\kommerce\Service as Service;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $order = new Entity\Order(new Entity\Cart, new Service\Pricing);

        /* @var $mock Payment */
        $mock = $this->getMockForAbstractClass('inklabs\kommerce\Entity\Payment\Payment');
        $mock->setId(1);
        $mock->setAmount(100);
        $mock->addOrder($order);

        $this->assertSame(1, $mock->getId());
        $this->assertSame(100, $mock->getAmount());
        $this->assertTrue($mock->getOrder() instanceof Entity\Order);
    }
}
