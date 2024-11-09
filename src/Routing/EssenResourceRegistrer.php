<?php
namespace Trueandfalse\Essential\Routing;

use Illuminate\Routing\ResourceRegistrar;

class EssenResourceRegistrer extends ResourceRegistrar
{
    protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'data'];

    protected function addResourceData($name, $base, $controller, $options)
    {
        $uri    = $this->getResourceUri($name) . '/data';
        $action = $this->getResourceAction($name, $controller, 'data', $options);

        return $this->router->post($uri, $action);
    }
}
