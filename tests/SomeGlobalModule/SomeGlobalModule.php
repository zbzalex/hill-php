<?php

namespace SomeGlobalModule;

use Hill\Module;

class SomeGlobalModule implements \Hill\IModule, \Hill\IOnModuleInit {
    public static function create(array $options = []) {
        return [
            'moduleClass' => SomeGlobalModule::class,
            'global'      => isset($options['global']) ? $options['global'] : false,
            'providers'   => [
                \SomeGlobalModule\Service\SomeGlobalService::class,
            ],
            'exportProviders' => [
                \SomeGlobalModule\Service\SomeGlobalService::class,
            ],
            'importModules' => [
                \SomeVendorModule\SomeVendorModule::class,
            ],
            'controllers' => [
                \SomeGlobalModule\Controller\SomeGlobalController::class,
            ]
        ];
    }

    public static function onInit(Module $module)
    {
        
    }
}