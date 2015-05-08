<?php
namespace inklabs\kommerce\View;

use inklabs\kommerce\Entity;
use inklabs\kommerce\Lib;

class TagTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $entityTag = new Entity\Tag;
        $entityTag->addImage(new Entity\Image);
        $entityTag->addProduct(new Entity\Product);
        $entityTag->addOption(new Entity\Option);
        $entityTag->addTextOption(new Entity\TextOption);

        $tag = $entityTag->getView()
            ->withAllData(new Lib\Pricing)
            ->export();

        $this->assertTrue($tag->images[0] instanceof Image);
        $this->assertTrue($tag->products[0] instanceof Product);
        $this->assertTrue($tag->options[0] instanceof Option);
        $this->assertTrue($tag->textOptions[0] instanceof TextOption);
    }
}