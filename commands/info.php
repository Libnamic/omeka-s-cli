<?php

$subcommands = [
    "system" => function(){
        global $application;
        global $argv;
        $format = 'table';
        
        $serviceLocator = $application->getServiceManager();
        $controller = new Omeka\Controller\Admin\SystemInfoController(
            $serviceLocator->get('Omeka\Connection'),
            $serviceLocator->get('Config'),
            $serviceLocator->get('Omeka\Cli'),
            $serviceLocator->get('Omeka\ModuleManager')
        );
        $model = $controller->browseAction();
        $info = $model->getVariable('info');



        // output($humanInfo, $format);
        output($info, 'list');
    },
    "db" => function(){
        global $application;
        global $argv;
        $format = 'list';
        
        $serviceLocator = $application->getServiceManager();
        
        $settings = $serviceLocator->get('ApplicationConfig');
        output($settings['connection'], $format);
    },
];