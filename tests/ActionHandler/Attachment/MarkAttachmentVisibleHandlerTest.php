<?php
namespace inklabs\kommerce\ActionHandler\Attachment;

use inklabs\kommerce\Action\Attachment\MarkAttachmentVisibleCommand;
use inklabs\kommerce\tests\Helper\TestCase\ActionTestCase;

class MarkAttachmentVisibleHandlerTest extends ActionTestCase
{
    public function testHandle()
    {
        $attachment = $this->dummyData->getAttachment();
        $attachment->setNotVisible();

        $attachmentService = $this->mockService->getAttachmentService();
        $this->serviceShouldGetOneById($attachmentService, $attachment);
        $this->serviceShouldUpdate($attachmentService, $attachment);

        $command = new MarkAttachmentVisibleCommand($attachment->getId()->getHex());
        $handler = new MarkAttachmentVisibleHandler($attachmentService);
        $handler->handle($command);

        $this->assertTrue($attachment->isVisible());
    }
}
