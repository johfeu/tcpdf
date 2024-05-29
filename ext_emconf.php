<?php

$EM_CONF['tcpdf'] = [
    'title' => 'TCPDF',
    'description' => 'Wrapper Extension for tcpdf',
    'category' => 'services',
    'author' => 'Daniel Gohlke',
    'author_email' => 'ext.tcpdf@extco.de',
    'author_company' => 'extco.de UG (haftungsbeschränkt)',
    'state' => 'stable',
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.999'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
