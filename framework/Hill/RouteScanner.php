<?php

namespace Hill;

/**
 * Router clanner class
 */
class RouteScanner
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var Route[] $routes
     */
    private $routes;

    /**
     * 
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routes = [];
    }

    /**
     * 
     */
    public function scan($basePath)
    {
        // базовый путь приложения всегда должен содержать слэш в конце
        $basePath = rtrim($basePath, '/') . "/";

        $modules = array_merge(
            $this->container->getModules(),
            $this->container->getGlobalModules()
        );
        
        foreach ($modules as $module) {
            
            $controllers = $module->getControllers();
            foreach ($controllers as $wrapper) {
                try {
                    $reflectionClass = new \ReflectionClass($wrapper->instanceClass);
                    $config = $reflectionClass->getMethod('getConfig')->invoke(null);

                    $controllerBasePath = isset($config['path'])
                        ? $config['path']
                        : "/";
                    $controllerBasePath = trim($controllerBasePath, '/') . "/";
                    $mapping = isset($config['mapping'])
                        ? $config['mapping']
                        : [];
                    $middlewares = isset($config['middlewares'])
                        ? $config['middlewares']
                        : [];
                    $interceptors = isset($config['interceptors'])
                        ? $config['interceptors']
                        : [];
                    
                    $path = rtrim($basePath . $controllerBasePath, "/") . "/";

                    $this->registerRoutes(
                        $module,
                        $wrapper,
                        $path,
                        $mapping,
                        $middlewares,
                        $interceptors
                    );
                } catch (\ReflectionException $e) {
                }
            }
        }

        return $this->routes;
    }

    /**
     * @param InstanceResolver $instanceResolver
     */
    private function registerRoutes(
        Module $module,
        $wrapper,
        $basePath,
        array $mapping,
        array $middlewares,
        array $interceptors
    ) {
        foreach ($mapping as $map) {
            /** @var RequestMapping $map */
            
            $path = $basePath . trim($map->path, '/');
            if ($path != "/") {
                $path = rtrim($path, "/");
            }

            $route = new Route(
                $module,
                $map->requestMethod,
                $path,
                [
                    $wrapper->instance,
                    $map->action
                ],
                array_merge(
                    $middlewares,
                    $map->middlewares,
                ),
                array_merge(
                    $interceptors,
                    $map->interceptors,
                )
            );

            // compile pattern
            $route->compile();

            $this->routes[] = $route;
        }
    }
}
