<?php

return [
    'autoload' => false,
    'hooks' => [
        'config_init' => [
            'qcloudsms',
            'summernote',
        ],
        'sms_send' => [
            'qcloudsms',
        ],
        'sms_notice' => [
            'qcloudsms',
        ],
        'sms_check' => [
            'qcloudsms',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
