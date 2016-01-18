<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Generate TSConfig content permissions from YAML file',
    'description' => 'Generate a complex set of content permission rules from a simple YAML file.',
    'category' => 'BE',
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@maxserv.com',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/tx_permissions/',
    'clearCacheOnLoad' => 1,
    'author_company' => 'MaxServ B.V.',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
