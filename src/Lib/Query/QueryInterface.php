<?php
namespace inklabs\kommerce\Lib\Query;

use inklabs\kommerce\Lib\ActionInterface;

interface QueryInterface extends ActionInterface
{
    /**
     * @return mixed
     */
    public function getRequest();

    /**
     * @return mixed
     */
    public function getResponse();
}
