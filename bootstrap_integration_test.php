<?php

require_once(__DIR__ . '/vendor/autoload.php');

$diConfig = (new \tests\integration\TestConfigProvider())();
$builder = new \DI\ContainerBuilder();
$builder->useAnnotations(false);
$builder->addDefinitions($diConfig);

try {
    $container = $builder->build();
    return $container;
} catch (Exception $e) {
    var_dump($e);
}
