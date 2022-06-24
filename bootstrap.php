<?php

require_once(__DIR__ . '/vendor/autoload.php');

$diConfig = (new \xTest\ConfigProvider())();
$builder = new \DI\ContainerBuilder();
$builder->useAnnotations(false);
$builder->addDefinitions($diConfig);

try {
    $container = $builder->build();
    return $container->get(\xTest\Service\UserService::class);
} catch (Exception $e) {
    var_dump($e);
}
