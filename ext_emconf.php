<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Configure your TYPO3 site using YAML files',
    'description' => 'Export and import any table to and from a YAML file. Generate TSConfig from YAML files.',
    'category' => 'BE',
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@maxserv.com',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/tx_yamlconfiguration/',
    'clearCacheOnLoad' => 1,
    'author_company' => 'MaxServ B.V.',
    'version' => '1.0.2',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
