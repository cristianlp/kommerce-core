<?php
namespace inklabs\kommerce\Action;

use inklabs\kommerce\Action\Tag\EditTag;
use inklabs\kommerce\Action\Tag\EditTagCommand;
use inklabs\kommerce\tests\Helper\DoctrineTestCase;
use inklabs\kommerce\tests\Helper\EntityRepository\FakeTagRepository;

class EditTagTest extends DoctrineTestCase
{
    public function testExecute()
    {
        $tag = $this->getDummyTag();
        $updated = $tag->getUpdated();

        $tag->setName('New Name');
        $action = new EditTag(new FakeTagRepository);
        $action->execute(new EditTagCommand($tag));

        $this->assertNotSame($updated, $tag->getUpdated());
    }
}