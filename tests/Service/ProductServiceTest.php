<?php
namespace inklabs\kommerce\Service;

use inklabs\kommerce\Entity\Product;
use inklabs\kommerce\Entity\Tag;
use inklabs\kommerce\Exception\EntityNotFoundException;
use inklabs\kommerce\tests\Helper;
use inklabs\kommerce\tests\Helper\EntityRepository\FakeProductRepository;
use inklabs\kommerce\tests\Helper\EntityRepository\FakeTagRepository;
use inklabs\kommerce\tests\Helper\EntityRepository\FakeImageRepository;

class ProductServiceTest extends Helper\DoctrineTestCase
{
    /** @var FakeProductRepository */
    protected $productRepository;

    /** @var FakeTagRepository */
    protected $tagRepository;

    /** @var FakeImageRepository */
    protected $imageRepository;

    /** @var ProductService */
    protected $productService;

    public function setUp()
    {
        parent::setUp();

        $this->productRepository = new FakeProductRepository;
        $this->tagRepository = new FakeTagRepository;
        $this->imageRepository = new FakeImageRepository;

        $this->productService = new ProductService(
            $this->productRepository,
            $this->tagRepository,
            $this->imageRepository
        );
    }

    public function testCreate()
    {
        $product = $this->dummyData->getProduct();
        $this->productService->create($product);
        $this->assertTrue($product instanceof Product);
    }

    public function testEdit()
    {
        $newName = 'New Name';
        $product = $this->dummyData->getProduct();
        $this->assertNotSame($newName, $product->getName());

        $product->setName($newName);
        $this->productService->edit($product);
        $this->assertSame($newName, $product->getName());
    }

    public function testFind()
    {
        $this->productRepository->create(new Product);

        $product = $this->productService->findOneById(1);
        $this->assertTrue($product instanceof Product);
    }

    public function testAddTag()
    {
        $product = new Product;
        $this->productRepository->create($product);

        $tag = new Tag;
        $this->tagRepository->create($tag);

        $tag = $this->productService->addTag($product->getId(), $tag->getId());

        $this->assertTrue($tag instanceof Tag);
        $this->assertTrue($product->getTags()[0] instanceof Tag);
    }

    public function testAddTagWithMissingProductThrowsException()
    {
        $productId = 1;
        $tagEncodedId = '1';

        $this->setExpectedException(
            EntityNotFoundException::class,
            'Product not found'
        );

        $this->productService->addTag($productId, $tagEncodedId);
    }

    public function testAddTagWithMissingTagThrowsException()
    {
        $product = new Product;
        $this->productRepository->create($product);

        $this->setExpectedException(
            EntityNotFoundException::class,
            'Tag not found'
        );

        $this->productService->addTag($product->getId(), 1);
    }

    public function testRemoveTag()
    {
        $tag = $this->dummyData->getTag();
        $product = $this->dummyData->getProduct();
        $product->addTag($tag);

        $this->tagRepository->create($tag);
        $this->productRepository->create($product);

        $this->productRepository->setReturnValue($product);
        $this->imageRepository->setReturnValue($tag);

        $this->productService->removeTag($product->getId(), $tag->getId());
    }

    public function testRemoveImage()
    {
        $image = $this->dummyData->getImage();
        $product = $this->dummyData->getProduct();
        $product->addImage($image);

        $this->imageRepository->create($image);
        $this->productRepository->create($product);

        $this->productService->removeImage($product->getId(), $image->getId());
    }

    public function testRemoveImageWithMissingImageThrowsException()
    {
        $product = new Product;
        $this->productRepository->create($product);

        $this->setExpectedException(
            EntityNotFoundException::class,
            'Image not found'
        );

        $this->productService->removeImage($product->getId(), 1);
    }

    public function testGetAllProducts()
    {
        $products = $this->productService->getAllProducts();
        $this->assertTrue($products[0] instanceof Product);
    }

    public function testGetRelatedProducts()
    {
        $product = new Product;
        $product->addTag(new Tag);

        $products = $this->productService->getRelatedProducts($product);
        $this->assertTrue($products[0] instanceof Product);
    }

    public function testGetProductsByTag()
    {
        $products = $this->productService->getProductsByTag(new Tag);
        $this->assertTrue($products[0] instanceof Product);
    }

    public function testGetProductsByIds()
    {
        $products = $this->productService->getProductsByIds([1]);
        $this->assertTrue($products[0] instanceof Product);
    }

    public function testGetAllProductsByIds()
    {
        $products = $this->productService->getAllProductsByIds([1]);
        $this->assertTrue($products[0] instanceof Product);
    }

    public function testGetRandomProducts()
    {
        $products = $this->productService->getRandomProducts(1);
        $this->assertTrue($products[0] instanceof Product);
    }
}
