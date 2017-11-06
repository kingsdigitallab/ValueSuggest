<?php
namespace ValueSuggest\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UkatDataTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $name = ucfirst(strtolower(substr($requestedName, strrpos($requestedName, ':') + 1)));
        $dataType = sprintf('ValueSuggest\DataType\Ukat\%s', $name);
        return new $dataType($services);
    }
}
