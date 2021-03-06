<?php
namespace inklabs\kommerce\Action\Image;

use inklabs\kommerce\EntityDTO\UploadFileDTO;
use inklabs\kommerce\Lib\Command\CommandInterface;
use inklabs\kommerce\Lib\Uuid;
use inklabs\kommerce\Lib\UuidInterface;

final class CreateImageForTagCommand implements CommandInterface
{
    /** @var UploadFileDTO */
    private $uploadFileDTO;

    /** @var UuidInterface */
    protected $tagId;

    /**
     * @param UploadFileDTO $uploadFileDTO
     * @param string $tagId
     */
    public function __construct(UploadFileDTO $uploadFileDTO, $tagId)
    {
        $this->tagId = Uuid::fromString($tagId);
        $this->uploadFileDTO = $uploadFileDTO;
    }

    public function getUploadFileDTO()
    {
        return $this->uploadFileDTO;
    }

    public function getTagId()
    {
        return $this->tagId;
    }
}
