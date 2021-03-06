<?php
namespace inklabs\kommerce\Lib\Query;

use inklabs\kommerce\Lib\Authorization\AuthorizationContextInterface;
use inklabs\kommerce\Lib\HandlerInterface;
use inklabs\kommerce\Lib\MapperInterface;

class QueryBus implements QueryBusInterface
{
    /** @var AuthorizationContextInterface */
    private $authorizationContext;

    /** @var MapperInterface */
    private $mapper;

    public function __construct(
        AuthorizationContextInterface $authorizationContext,
        MapperInterface $mapper
    ) {
        $this->authorizationContext = $authorizationContext;
        $this->mapper = $mapper;
    }

    /**
     * @param QueryInterface $query
     * @return void
     */
    public function execute(QueryInterface $query)
    {
        $handler = $this->mapper->getQueryHandler($query);
        if ($handler instanceof HandlerInterface) {
            $handler->verifyAuthorization($this->authorizationContext);
            $handler->handle();
        } else {
            // TODO: Remove when #98 is complete
            $handler->handle($query);
        }
    }
}
