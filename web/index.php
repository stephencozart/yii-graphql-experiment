<?php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Server\StandardServer;
use app\graphql\DataSource;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Error\Debug;

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

$app = new yii\web\Application($config);

$dataSource = new DataSource();

$typeRegistry = new \app\graphql\types\TypeRegistry($dataSource);

$rawInputMd5 = md5(Yii::$app->request->rawBody);

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'actors' => [
            'type' => Type::listOf($typeRegistry->actor),
            'resolve' => [$typeRegistry->actor, 'queryResolver'],
            'args' => [
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 20
                ]
            ]
        ],
        'films' => [
            'type' => Type::listOf($typeRegistry->film),
            'args' => [
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 20
                ],
                'titleBeginsWith' => [
                    'type' => Type::string()
                ]
            ],
            'resolve' => [$typeRegistry->film, 'queryResolver']
        ],
        'md5' => [
            'type' => Type::string(),
            'resolve' => function($value, $args, $context, ResolveInfo $resolveInfo)  {
                return $value['md5'];
            }
        ]
    ]
]);

$schema = new Schema([
    'query' => $queryType
]);

$rootValue = [
    'md5' => $rawInputMd5
];

$context = [
    'user' => Yii::$app->user->identity,
];

$server = new StandardServer([
    'schema' => $schema,
    'rootValue' => $rootValue,
    'context' => $context,
    'debug' => Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE
]);

$data = Yii::$app->cache->getOrSet($rawInputMd5, function() use ($server) {
    return $server->executeRequest()->toArray();
}, 360);

$response = new \yii\web\Response([
    'format' => \yii\web\Response::FORMAT_JSON,
    'data' => $data
]);

$response->send();
