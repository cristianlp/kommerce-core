<?php
namespace inklabs\kommerce\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use inklabs\kommerce\Lib\UuidInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Tag implements IdEntityInterface, EnabledAttachmentInterface
{
    use TimeTrait, IdTrait, StringSetterTrait;

    /** @var string */
    protected $name;

    /** @var string */
    protected $code;

    /** @var string */
    protected $description;

    /** @var string */
    protected $defaultImage;

    /** @var int */
    protected $sortOrder;

    /** @var boolean */
    protected $isActive;

    /** @var boolean */
    protected $isVisible;

    /** @var boolean */
    protected $areAttachmentsEnabled;

    /** @var Product[] */
    protected $products;

    /** @var Image[] */
    protected $images;

    /** @var Option[] */
    protected $options;

    /** @var TextOption[] */
    protected $textOptions;

    public function __construct(UuidInterface $id = null)
    {
        $this->setId($id);
        $this->setCreated();
        $this->products = new ArrayCollection;
        $this->images = new ArrayCollection;
        $this->options = new ArrayCollection;
        $this->textOptions = new ArrayCollection;

        $this->disableAttachments();

        $this->sortOrder = 0;
        $this->isActive = false;
        $this->isVisible = false;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank);
        $metadata->addPropertyConstraint('name', new Assert\Length([
            'max' => 255,
        ]));

        $metadata->addPropertyConstraint('code', new Assert\Length([
            'max' => 64,
        ]));

        $metadata->addPropertyConstraint('description', new Assert\Length([
            'max' => 65535,
        ]));

        $metadata->addPropertyConstraint('sortOrder', new Assert\NotNull);
        $metadata->addPropertyConstraint('sortOrder', new Assert\Range([
            'min' => 0,
            'max' => 65535,
        ]));
    }

    public function addProduct(Product $product)
    {
        $product->addTag($this);
        $this->products[] = $product;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function addImage(Image $image)
    {
        if ($this->images->isEmpty()) {
            $this->setDefaultImage($image->getPath());
        }

        $image->setTag($this);
        $this->images->add($image);
    }

    public function removeImage(Image $image)
    {
        $result = $this->images->removeElement($image);

        if ($this->getDefaultImage() === $image->getPath()) {
            if (! $this->images->isEmpty()) {
                $this->setDefaultImage($this->images->first()->getPath());
            } else {
                $this->setDefaultImage(null);
            }
        }

        return $result;
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        return $this->images;
    }

    public function addOption(Option $option)
    {
        $option->addTag($this);
        $this->options->add($option);
    }

    public function removeOption(Option $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function addTextOption(TextOption $textOption)
    {
        $textOption->addTag($this);
        $this->textOptions[] = $textOption;
    }

    /**
     * @return TextOption[]
     */
    public function getTextOptions()
    {
        return $this->textOptions;
    }

    public function setName($name)
    {
        $this->name = (string) $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->setStringOrNull($this->code, $code);
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->setStringOrNull($this->description, $description);
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $defaultImage
     */
    public function setDefaultImage($defaultImage)
    {
        $this->setStringOrNull($this->defaultImage, $defaultImage);
    }

    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function setIsVisible($isVisible)
    {
        $this->isVisible = (bool) $isVisible;
    }

    public function isVisible()
    {
        return $this->isVisible;
    }

    /**
     * @param bool $areAttachmentsEnabled
     */
    public function setAreAttachmentsEnabled($areAttachmentsEnabled)
    {
        $this->areAttachmentsEnabled = (bool) $areAttachmentsEnabled;
    }

    public function disableAttachments()
    {
        $this->areAttachmentsEnabled = false;
    }

    public function enableAttachments()
    {
        $this->areAttachmentsEnabled = true;
    }

    public function areAttachmentsEnabled()
    {
        return $this->areAttachmentsEnabled;
    }
}
