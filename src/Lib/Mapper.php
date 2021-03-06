<?php
namespace inklabs\kommerce\Lib;

use inklabs\kommerce\EntityDTO\Builder\DTOBuilderFactoryInterface;
use inklabs\kommerce\EntityRepository\AttributeRepositoryInterface;
use inklabs\kommerce\EntityRepository\AttributeValueRepositoryInterface;
use inklabs\kommerce\EntityRepository\CartPriceRuleDiscountRepositoryInterface;
use inklabs\kommerce\EntityRepository\CartPriceRuleItemRepositoryInterface;
use inklabs\kommerce\EntityRepository\CartPriceRuleRepositoryInterface;
use inklabs\kommerce\EntityRepository\CatalogPromotionRepositoryInterface;
use inklabs\kommerce\EntityRepository\ConfigurationRepositoryInterface;
use inklabs\kommerce\EntityRepository\CouponRepositoryInterface;
use inklabs\kommerce\EntityRepository\OptionRepositoryInterface;
use inklabs\kommerce\EntityRepository\ProductAttributeRepositoryInterface;
use inklabs\kommerce\EntityRepository\ProductRepositoryInterface;
use inklabs\kommerce\EntityRepository\RepositoryFactory;
use inklabs\kommerce\EntityRepository\ShipmentTrackerRepositoryInterface;
use inklabs\kommerce\EntityRepository\TagRepositoryInterface;
use inklabs\kommerce\EntityRepository\TaxRateRepositoryInterface;
use inklabs\kommerce\EntityRepository\TextOptionRepositoryInterface;
use inklabs\kommerce\EntityRepository\UserRepositoryInterface;
use inklabs\kommerce\EntityRepository\UserTokenRepositoryInterface;
use inklabs\kommerce\Lib\Command\CommandInterface;
use inklabs\kommerce\Lib\Query\QueryInterface;
use inklabs\kommerce\Lib\ShipmentGateway\ShipmentGatewayInterface;
use inklabs\kommerce\Service\AttachmentServiceInterface;
use inklabs\kommerce\Service\CartPriceRuleServiceInterface;
use inklabs\kommerce\Service\CartServiceInterface;
use inklabs\kommerce\Service\CatalogPromotionServiceInterface;
use inklabs\kommerce\Service\CouponServiceInterface;
use inklabs\kommerce\Service\ImageServiceInterface;
use inklabs\kommerce\Service\Import\ImportOrderItemServiceInterface;
use inklabs\kommerce\Service\Import\ImportOrderServiceInterface;
use inklabs\kommerce\Service\Import\ImportPaymentServiceInterface;
use inklabs\kommerce\Service\Import\ImportUserServiceInterface;
use inklabs\kommerce\Service\InventoryServiceInterface;
use inklabs\kommerce\Service\OptionServiceInterface;
use inklabs\kommerce\Service\OrderServiceInterface;
use inklabs\kommerce\Service\ProductServiceInterface;
use inklabs\kommerce\Service\ServiceFactory;
use inklabs\kommerce\Service\TagServiceInterface;
use inklabs\kommerce\Service\TaxRateServiceInterface;
use inklabs\kommerce\Service\UserServiceInterface;
use ReflectionClass;

class Mapper implements MapperInterface
{
    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var ServiceFactory */
    private $serviceFactory;

    /** @var Pricing */
    private $pricing;

    /** @var DTOBuilderFactoryInterface */
    private $dtoBuilderFactory;

    public function __construct(
        RepositoryFactory $repositoryFactory,
        ServiceFactory $serviceFactory,
        Pricing $pricing,
        DTOBuilderFactoryInterface $dtoBuilderFactory
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->serviceFactory = $serviceFactory;
        $this->pricing = $pricing;
        $this->dtoBuilderFactory = $dtoBuilderFactory;
    }

    public function getCommandHandler(CommandInterface $command)
    {
        $handlerClassName = $this->getCommandHandlerClassName($command);
        return $this->getHandler($handlerClassName, $command);
    }

    public function getQueryHandler(QueryInterface $query)
    {
        $handlerClassName = $this->getQueryHandlerClassName($query);
        return $this->getHandler($handlerClassName, $query);
    }

    /**
     * @param string $handlerClassName
     * @param ActionInterface|CommandInterface|QueryInterface $action TODO: Switch to ActionInterface after #98
     * @return null|object
     */
    public function getHandler($handlerClassName, $action)
    {
        $reflection = new ReflectionClass($handlerClassName);

        $constructorParameters = [];
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $key => $parameter) {
                // TODO: Switch back when #98 is complete
                // if ($key === 0 && $action instanceof ActionInterface) {
                if ($key === 0 && $parameter->getClass()->isSubclassOf(ActionInterface::class)) {
                    $constructorParameters[] = $action;
                    continue;
                }

                $parameterClassName = $parameter->getClass()->getName();
                if ($parameterClassName === AttributeRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getAttributeRepository();
                } elseif ($parameterClassName === AttributeValueRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getAttributeValueRepository();
                } elseif ($parameterClassName === CartPriceRuleRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getCartPriceRuleRepository();
                } elseif ($parameterClassName === CartPriceRuleItemRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getCartPriceRuleItemRepository();
                } elseif ($parameterClassName === CartPriceRuleDiscountRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getCartPriceRuleDiscountRepository();
                } elseif ($parameterClassName === CatalogPromotionRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getCatalogPromotionRepository();
                } elseif ($parameterClassName === ConfigurationRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getConfigurationRepository();
                } elseif ($parameterClassName === CouponRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getCouponRepository();
                } elseif ($parameterClassName === OptionRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getOptionRepository();
                } elseif ($parameterClassName === ProductRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getProductRepository();
                } elseif ($parameterClassName === ProductAttributeRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getProductAttributeRepository();
                } elseif ($parameterClassName === ShipmentTrackerRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getShipmentTrackerRepository();
                } elseif ($parameterClassName === TagRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getTagRepository();
                } elseif ($parameterClassName === TextOptionRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getTextOptionRepository();
                } elseif ($parameterClassName === TaxRateRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getTaxRateRepository();
                } elseif ($parameterClassName === UserRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getUserRepository();
                } elseif ($parameterClassName === UserTokenRepositoryInterface::class) {
                    $constructorParameters[] = $this->repositoryFactory->getUserTokenRepository();
                } elseif ($parameterClassName === AttachmentServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getAttachmentService();
                } elseif ($parameterClassName === CartCalculatorInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getCartCalculator();
                } elseif ($parameterClassName === CartPriceRuleServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getCartPriceRule();
                } elseif ($parameterClassName === CartServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getCart();
                } elseif ($parameterClassName === CatalogPromotionServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getCatalogPromotion();
                } elseif ($parameterClassName === CouponServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getCoupon();
                } elseif ($parameterClassName === DTOBuilderFactoryInterface::class) {
                    $constructorParameters[] = $this->dtoBuilderFactory;
                } elseif ($parameterClassName === ImportUserServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getImportUser();
                } elseif ($parameterClassName === ImportOrderServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getImportOrder();
                } elseif ($parameterClassName === ImportOrderItemServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getImportOrderItem();
                } elseif ($parameterClassName === ImportPaymentServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getImportPayment();
                } elseif ($parameterClassName === InventoryServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getInventoryService();
                } elseif ($parameterClassName === ImageServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getImageService();
                } elseif ($parameterClassName === OptionServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getOption();
                } elseif ($parameterClassName === OrderServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getOrder();
                } elseif ($parameterClassName === ProductServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getProduct();
                } elseif ($parameterClassName === ShipmentGatewayInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getShipmentGateway();
                } elseif ($parameterClassName === TagServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getTagService();
                } elseif ($parameterClassName === TaxRateServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getTaxRate();
                } elseif ($parameterClassName === UserServiceInterface::class) {
                    $constructorParameters[] = $this->serviceFactory->getUser();
                }
            }
        }

        $handler = null;

        if (! empty($constructorParameters)) {
            $handler = $reflection->newInstanceArgs($constructorParameters);
        } else {
            $handler = $reflection->newInstance();
        }

        return $handler;
    }

    /**
     * @param CommandInterface $command
     * @return string
     */
    private function getCommandHandlerClassName($command)
    {
        $className = get_class($command);
        $className = str_replace('\\Action\\', '\\ActionHandler\\', $className);
        $pieces = explode('\\', $className);

        $baseName = array_pop($pieces);
        $handlerBaseName = substr($baseName, 0, -7) . 'Handler';

        $pieces[] = $handlerBaseName;

        return implode('\\', $pieces);
    }

    /**
     * @param QueryInterface
     * @return string
     */
    private function getQueryHandlerClassName($query)
    {
        $className = get_class($query);
        $className = str_replace('\\Action\\', '\\ActionHandler\\', $className);
        $pieces = explode('\\', $className);

        $baseName = array_pop($pieces);
        $handlerBaseName = substr($baseName, 0, -5) . 'Handler';

        $pieces[] = $handlerBaseName;

        return implode('\\', $pieces);
    }
}
