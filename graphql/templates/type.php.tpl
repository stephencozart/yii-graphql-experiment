<?php

namespace {namespace};

use app\models\{modelName};
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class {className} extends ObjectType
{
    public function __construct(TypeRegistry $typeRegistry, array $config = [])
    {
        $config['fields'] = {fieldConfig}

        parent::__construct($config);
    }

    public function queryType()
    {
        return [
            'type' => Type::listOf($this),
            'args' => [
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 20
                ]
            ],
            'resolve' => [$this, 'queryResolver']
        ];
    }

    /**
    * @param $value
    * @param $args
    * @param $context
    * @param ResolveInfo $resolveInfo
    * @return array|\yii\db\ActiveRecord[]
    */
    public function queryResolver($value, $args, $context, ResolveInfo $resolveInfo)
    {
        $query = {modelName}::find();

        if (array_key_exists('limit', $args)) {
            $query->limit($args['limit']);
        }

        return $query->all();
    }
}