<?php
namespace inklabs\kommerce\EntityRepository;

use inklabs\kommerce\Entity;
use inklabs\kommerce\tests\Helper;

class CartPriceRuleTest extends Helper\DoctrineTestCase
{
    protected $metaDataClassNames = [
        'kommerce:CartPriceRule',
        'kommerce:CartPriceRuleDiscount',
        'kommerce:CartPriceRuleItem\Item',
        'kommerce:Product',
    ];

    /** @var CartPriceRuleInterface */
    protected $cartPriceRuleRepository;

    public function setUp()
    {
        $this->cartPriceRuleRepository = $this->repository()->getCartPriceRule();
    }

    public function setupCartPriceRuleDiscount()
    {
        $productShirt = $this->getDummyProduct(1);
        $productPoster = $this->getDummyProduct(2);

        $cartPriceRule = $this->getDummyCartPriceRule();
        $cartPriceRule->addItem(new Entity\CartPriceRuleItem\Product($productShirt, 1));
        $cartPriceRule->addItem(new Entity\CartPriceRuleItem\Product($productPoster, 1));
        $cartPriceRule->addDiscount(new Entity\CartPriceRuleDiscount($productPoster));

        $this->entityManager->persist($productShirt);
        $this->entityManager->persist($productPoster);
        $this->entityManager->persist($cartPriceRule);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testCRUD()
    {
        $cartPriceRule = $this->getDummyCartPriceRule();

        $this->cartPriceRuleRepository->create($cartPriceRule);
        $this->assertSame(1, $cartPriceRule->getId());

        $cartPriceRule->setName('New Name');
        $this->assertSame(null, $cartPriceRule->getUpdated());

        $this->cartPriceRuleRepository->save($cartPriceRule);
        $this->assertTrue($cartPriceRule->getUpdated() instanceof \DateTime);

        $this->cartPriceRuleRepository->remove($cartPriceRule);
        $this->assertSame(null, $cartPriceRule->getId());
    }

    public function testFind()
    {
        $this->setupCartPriceRuleDiscount();

        $this->setCountLogger();

        $cartPriceRule = $this->cartPriceRuleRepository->find(1);

        $cartPriceRule->getCartPriceRuleItems()->toArray();
        $cartPriceRule->getCartPriceRuleDiscounts()->toArray();

        $this->assertTrue($cartPriceRule instanceof Entity\CartPriceRule);
        $this->assertSame(4, $this->countSQLLogger->getTotalQueries());
    }
}
