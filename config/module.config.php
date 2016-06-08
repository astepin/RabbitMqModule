<?php

namespace RabbitMqModule;

return [
    'rabbitmq_module' => [
        'connection' => [
            'default' => []
        ],
        'producer' => [],
        'consumer' => [],
        'rpc_server' => [],
        'rpc_client' => [],

        'factories' => [
            'connection' => 'RabbitMqModule\\Service\\ConnectionFactory',
            'producer' => 'RabbitMqModule\\Service\\ProducerFactory',
            'consumer' => 'RabbitMqModule\\Service\\ConsumerFactory',
            'rpc_server' => 'RabbitMqModule\\Service\\RpcServerFactory',
            'rpc_client' => 'RabbitMqModule\\Service\\RpcClientFactory'
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'rabbitmq_module-setup-fabric' => [
                    'options' => [
                        'route'    => 'rabbitmq-module setup-fabric',
                        'defaults' => [
                            'controller' => 'RabbitMqModule\\Controller\\SetupFabricController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'rabbitmq_module-list_consumers' => [
                    'options' => [
                        'route'    => 'rabbitmq-module list consumers',
                        'defaults' => [
                            'controller' => 'RabbitMqModule\\Controller\\ConsumerController',
                            'action' => 'list'
                        ]
                    ]
                ],
                'rabbitmq_module-consumer' => [
                    'options' => [
                        'route'    => 'rabbitmq-module consumer <name> [--without-signals|-w]',
                        'defaults' => [
                            'controller' => 'RabbitMqModule\\Controller\\ConsumerController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'rabbitmq_module-rpc_server' => [
                    'options' => [
                        'route'    => 'rabbitmq-module rpc_server <name> [--without-signals|-w]',
                        'defaults' => [
                            'controller' => 'RabbitMqModule\\Controller\\RpcServerController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'rabbitmq_module-stdin-producer' => [
                    'options' => [
                        'route'    => 'rabbitmq-module stdin-producer <name> [--route=] <msg>',
                        'defaults' => [
                            'controller' => 'RabbitMqModule\\Controller\\StdInProducerController',
                            'action' => 'index'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            'RabbitMqModule\\Controller\\SetupFabricController' =>
                'RabbitMqModule\\Controller\\Factory\\SetupFabricControllerFactory',
            'RabbitMqModule\\Controller\\ConsumerController' =>
                'RabbitMqModule\\Controller\\Factory\\ConsumerControllerFactory',
            'RabbitMqModule\\Controller\\RpcServerController' =>
                'RabbitMqModule\\Controller\\Factory\\RpcServerControllerFactory',
            'RabbitMqModule\\Controller\\StdInProducerController' =>
                'RabbitMqModule\\Controller\\Factory\\StdInProducerControllerFactory'
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'RabbitMqModule\\Service\\RabbitMqService' => 'RabbitMqModule\\Service\\RabbitMqService',
            'RabbitMqModule\\Service\\Connection\\StreamConnectionFactory' =>
                'RabbitMqModule\\Service\\Connection\\StreamConnectionFactory',
            'RabbitMqModule\\Service\\Connection\\SslConnectionFactory' =>
                'RabbitMqModule\\Service\\Connection\\SslConnectionFactory',
            'RabbitMqModule\\Service\\Connection\\SocketConnectionFactory' =>
                'RabbitMqModule\\Service\\Connection\\SocketConnectionFactory',
            'RabbitMqModule\\Service\\Connection\\LazyConnectionFactory' =>
                'RabbitMqModule\\Service\\Connection\\LazyConnectionFactory'
        ],
        'abstract_factories' => [
            'RabbitMqModule\\Service\\AbstractServiceFactory' => 'RabbitMqModule\\Service\\AbstractServiceFactory'
        ]
    ]
];
