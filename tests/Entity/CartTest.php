<?php
namespace inklabs\kommerce;

use inklabs\kommerce\Entity\Cart;
use inklabs\kommerce\Entity\CartTotal;
use inklabs\kommerce\Entity\CartPriceRule;
use inklabs\kommerce\Entity\CartPriceRuleItem;
use inklabs\kommerce\Entity\CartPriceRuleDiscount;
use inklabs\kommerce\Entity\Coupon;
use inklabs\kommerce\Entity\CatalogPromotion;
use inklabs\kommerce\Entity\Product;
use inklabs\kommerce\Entity\TaxRate;
use inklabs\kommerce\Entity\Shipping;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testAddItem()
    {
        $product = new Product;
        $product2 = new Product;

        $cart = new Cart;
        $cart->addItem($product, 5);
        $cart->addItem($product2, 5);

        $this->assertEquals(2, $cart->totalItems());
        $this->assertEquals(10, $cart->totalQuantity());
    }

    public function testGetTotalBasic()
    {
        $pricing = new Pricing;

        $product = new Product;
        $product->setPrice(500);

        $product2 = new Product;
        $product2->setPrice(300);

        $cart = new Cart;
        $cart->addItem($product, 2);
        $cart->addItem($product2, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1300;
        $cart_total->subtotal = 1300;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 0;
        $cart_total->total = 1300;
        $cart_total->savings = 0;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCoupon()
    {
        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);

        $coupon = new Coupon;
        $coupon->name = '20% Off';
        $coupon->discount_type = 'percent';
        $coupon->value = 20;
        $coupon->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $coupon->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $cart = new Cart;
        $cart->addCoupon($coupon);
        $cart->addItem($product, 5);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 2500;
        $cart_total->subtotal = 2500;
        $cart_total->shipping = 0;
        $cart_total->discount = 500;
        $cart_total->tax = 0;
        $cart_total->total = 2000;
        $cart_total->savings = 500;
        $cart_total->coupons = [$coupon];

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCouponWithCatalogPromotion()
    {
        $catalog_promotion = new CatalogPromotion;
        $catalog_promotion->name = '20% Off';
        $catalog_promotion->discount_type = 'percent';
        $catalog_promotion->value = 20;
        $catalog_promotion->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $catalog_promotion->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));
        $pricing->addCatalogPromotion($catalog_promotion);

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);

        $coupon = new Coupon;
        $coupon->name = '20% Off';
        $coupon->discount_type = 'percent';
        $coupon->value = 20;
        $coupon->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $coupon->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $cart = new Cart;
        $cart->addCoupon($coupon);
        $cart->addItem($product, 5);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 2500;
        $cart_total->subtotal = 2000;
        $cart_total->shipping = 0;
        $cart_total->discount = 400;
        $cart_total->tax = 0;
        $cart_total->total = 1600;
        $cart_total->savings = 900;
        $cart_total->coupons = [$coupon];

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCouponValidOrderValue()
    {
        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(2000); // $20

        $coupon = new Coupon;
        $coupon->name = '20% Off orders under $100';
        $coupon->discount_type = 'percent';
        $coupon->value = 20;
        $coupon->min_order_value = 1000; // $10
        $coupon->max_order_value = 10000; // $100
        $coupon->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $coupon->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $cart = new Cart;
        $cart->addCoupon($coupon);
        $cart->addItem($product, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 2000;
        $cart_total->subtotal = 2000;
        $cart_total->shipping = 0;
        $cart_total->discount = 400;
        $cart_total->tax = 0;
        $cart_total->total = 1600;
        $cart_total->savings = 400;
        $cart_total->coupons = [$coupon];

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalWithShipping()
    {
        $pricing = new Pricing;

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);

        $usps_shipping_rate = new Shipping\Rate;
        $usps_shipping_rate->code = '4';
        $usps_shipping_rate->name = 'Parcel Post';
        $usps_shipping_rate->cost = 1000;

        $cart = new Cart;
        $cart->addItem($product, 3);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1500;
        $cart_total->subtotal = 1500;
        $cart_total->shipping = 1000;
        $cart_total->discount = 0;
        $cart_total->tax = 0;
        $cart_total->total = 2500;
        $cart_total->savings = 0;

        $this->assertEquals($cart_total, $cart->getTotal($pricing, $usps_shipping_rate));
    }

    public function testGetTotalWithZip5TaxNotAppliedToShipping()
    {
        $pricing = new Pricing;

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);
        $product->setIsTaxable(true);

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = false;

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addItem($product, 2);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1000;
        $cart_total->subtotal = 1000;
        $cart_total->tax_subtotal = 1000;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 80;
        $cart_total->total = 1080;
        $cart_total->savings = 0;
        $cart_total->tax_rate = $tax_rate;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalWithZip5TaxAppliedToShipping()
    {
        $pricing = new Pricing;

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);
        $product->setIsTaxable(true);

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = true;

        $usps_shipping_rate = new Shipping\Rate;
        $usps_shipping_rate->code = '4';
        $usps_shipping_rate->name = 'Parcel Post';
        $usps_shipping_rate->cost = 1000;

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addItem($product, 2);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1000;
        $cart_total->subtotal = 1000;
        $cart_total->tax_subtotal = 1000;
        $cart_total->shipping = 1000;
        $cart_total->discount = 0;
        $cart_total->tax = 160;
        $cart_total->total = 2160;
        $cart_total->savings = 0;
        $cart_total->tax_rate = $tax_rate;

        $this->assertEquals($cart_total, $cart->getTotal($pricing, $usps_shipping_rate));
    }

    public function testGetTotalWithZip5TaxNotTaxable()
    {
        $pricing = new Pricing;

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(500);
        $product->setIsTaxable(false);

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = false;

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addItem($product, 2);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1000;
        $cart_total->subtotal = 1000;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 0;
        $cart_total->total = 1000;
        $cart_total->savings = 0;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalWithZip5TaxAndCouponReduceSubtotal()
    {
        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(2000); // $20
        $product->setIsTaxable(true);

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = false;

        $coupon = new Coupon;
        $coupon->name = '20% Off orders under $100';
        $coupon->discount_type = 'percent';
        $coupon->value = 20;
        $coupon->min_order_value = 1000; // $10
        $coupon->max_order_value = 10000; // $100
        $coupon->reduces_tax_subtotal = true;
        $coupon->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $coupon->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addCoupon($coupon);
        $cart->addItem($product, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 2000;
        $cart_total->subtotal = 2000;
        $cart_total->tax_subtotal = 1600;
        $cart_total->shipping = 0;
        $cart_total->discount = 400;
        $cart_total->tax = 128;
        $cart_total->total = 1728;
        $cart_total->savings = 400;
        $cart_total->coupons = [$coupon];
        $cart_total->tax_rate = $tax_rate;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalWithZip5TaxAndCouponNoReduceSubtotal()
    {
        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $product = new Product;
        $product->setSku('TST101');
        $product->setName('Test Product');
        $product->setPrice(2000); // $20
        $product->setIsTaxable(true);

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = false;

        $coupon = new Coupon;
        $coupon->name = '20% Off orders under $100';
        $coupon->discount_type = 'percent';
        $coupon->value = 20;
        $coupon->min_order_value = 1000; // $10
        $coupon->max_order_value = 10000; // $100
        $coupon->reduces_tax_subtotal = false;
        $coupon->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $coupon->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addCoupon($coupon);
        $cart->addItem($product, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 2000;
        $cart_total->subtotal = 2000;
        $cart_total->tax_subtotal = 2000;
        $cart_total->shipping = 0;
        $cart_total->discount = 400;
        $cart_total->tax = 160;
        $cart_total->total = 1760;
        $cart_total->savings = 400;
        $cart_total->coupons = [$coupon];
        $cart_total->tax_rate = $tax_rate;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCartPriceRule()
    {
        $product_shirt = new Product;
        // $product_shirt->id = 1;
        $product_shirt->setSku('TS-NAVY-LG');
        $product_shirt->setName('Navy T-shirt (large)');
        $product_shirt->setPrice(1200);

        $product_poster = new Product;
        // $product_poster->id = 2;
        $product_poster->setSku('PST-CKN');
        $product_poster->setName('Citizen Kane (1941) Poster');
        $product_poster->setPrice(500);

        $cart_price_rule = new CartPriceRule;
        $cart_price_rule->name = 'Buy a Shirt get a FREE poster';
        $cart_price_rule->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $cart_price_rule->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_shirt, 1));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_poster, 1));
        $cart_price_rule->addDiscount(new CartPriceRuleDiscount($product_poster, 1));

        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $cart = new Cart;
        $cart->addCartPriceRule($cart_price_rule);
        $cart->addItem($product_shirt, 1);
        $cart->addItem($product_poster, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1700;
        $cart_total->subtotal = 1200;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 0;
        $cart_total->total = 1200;
        $cart_total->savings = 500;
        $cart_total->cart_price_rules = [$cart_price_rule];

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCartPriceRuleTaxReduceSubtotal()
    {
        $product_shirt = new Product;
        // $product_shirt->id = 1;
        $product_shirt->setSku('TS-NAVY-LG');
        $product_shirt->setName('Navy T-shirt (large)');
        $product_shirt->setPrice(1200);
        $product_shirt->setIsTaxable(true);

        $product_poster = new Product;
        // $product_poster->id = 2;
        $product_poster->setSku('PST-CKN');
        $product_poster->setName('Citizen Kane (1941) Poster');
        $product_poster->setPrice(500);
        $product_poster->setIsTaxable(true);

        $cart_price_rule = new CartPriceRule;
        $cart_price_rule->name = 'Buy a Shirt get a FREE poster';
        $cart_price_rule->reduces_tax_subtotal = true;
        $cart_price_rule->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $cart_price_rule->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_shirt, 1));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_poster, 1));
        $cart_price_rule->addDiscount(new CartPriceRuleDiscount($product_poster, 1));

        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $tax_rate = new TaxRate;
        $tax_rate->zip5 = 92606;
        $tax_rate->rate = 8.0;
        $tax_rate->apply_to_shipping = false;

        $cart = new Cart;
        $cart->setTaxRate($tax_rate);
        $cart->addCartPriceRule($cart_price_rule);
        $cart->addItem($product_shirt, 1);
        $cart->addItem($product_poster, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 1700;
        $cart_total->subtotal = 1200;
        $cart_total->tax_subtotal = 1200;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 96;
        $cart_total->total = 1296;
        $cart_total->savings = 500;
        $cart_total->cart_price_rules = [$cart_price_rule];
        $cart_total->tax_rate = $tax_rate;

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }

    public function testGetTotalCartPriceRuleInvalidCartItems()
    {
        $product_shirt = new Product;
        // $product_shirt->id = 1;
        $product_shirt->setSku('TS-NAVY-LG');
        $product_shirt->setName('Navy T-shirt (large)');
        $product_shirt->setPrice(1200);

        $product_poster = new Product;
        // $product_poster->id = 2;
        $product_poster->setSku('PST-CKN');
        $product_poster->setName('Citizen Kane (1941) Poster');
        $product_poster->setPrice(500);

        $product_jacket = new Product;
        // $product_jacket->id = 3;
        $product_jacket->setSku('JKT001');
        $product_jacket->setName('Navy Jacket');
        $product_jacket->setPrice(2500);

        $cart_price_rule = new CartPriceRule;
        $cart_price_rule->name = 'Buy a Shirt get a FREE poster';
        $cart_price_rule->start = new \DateTime('2014-01-01', new \DateTimeZone('UTC'));
        $cart_price_rule->end   = new \DateTime('2014-12-31', new \DateTimeZone('UTC'));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_shirt, 1));
        $cart_price_rule->addItem(new CartPriceRuleItem($product_poster, 1));
        $cart_price_rule->addDiscount(new CartPriceRuleDiscount($product_poster, 1));

        $pricing = new Pricing(new \DateTime('2014-02-01', new \DateTimeZone('UTC')));

        $cart = new Cart;
        $cart->addCartPriceRule($cart_price_rule);
        $cart->addItem($product_shirt, 1);
        $cart->addItem($product_jacket, 1);

        // Expect:
        $cart_total = new CartTotal;
        $cart_total->orig_subtotal = 3700;
        $cart_total->subtotal = 3700;
        $cart_total->shipping = 0;
        $cart_total->discount = 0;
        $cart_total->tax = 0;
        $cart_total->total = 3700;
        $cart_total->savings = 0;
        $cart_total->cart_price_rules = [];

        $this->assertEquals($cart_total, $cart->getTotal($pricing));
    }
}
