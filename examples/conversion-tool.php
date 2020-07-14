<?php

require_once __DIR__.'/bootstrap.php';

$akeneoBluePrintDir = __DIR__.'/../src/BluePrint';
$icecatDir = __DIR__.'/akeneo/icecat_demo_dev';

$conversion = Symfony\Component\Yaml\Yaml::parseFile(__DIR__.'/conversion.yaml');

$references = [
    'products',
//    'attribute_groups.csv',
//    'currencies.csv',
//    'association_types.csv',
//    'groups.csv',
//    'channels.csv',
//    'family_variants.csv',
//    'users.csv',
//    'user_roles.csv',
//    'categories.csv',
//    'attribute_options.csv',
    'attributes',
    'families',
//    'user_groups.csv',
//    'group_types.csv',
//    'product_models.csv',
//    'locales.csv',
//    'enabled_locales.csv',
];

// encoders should receive the blueprints

// source collection should receive the encoder
// source collection should not know of the params of encoding or decoding.

// collect all sources
$sources = new \Misery\Component\Source\SourceCollection('akeneo/csv', $encoder, $akeneoBluePrintDir);
foreach ($references as $reference) {
    if (is_file($file = $icecatDir . DIRECTORY_SEPARATOR . $reference. '.csv')) {
        $sources->add(\Misery\Component\Source\Source::promise(
            \Misery\Component\Source\SourceType::file(),
            $file,
            $reference
        ));
    }
}

dump($sources);
dump($sources->get('products')->read());
