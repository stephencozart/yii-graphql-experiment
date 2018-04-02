<?php

namespace app\graphql\types;

use app\models\Customer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class CustomerType extends ObjectType
{
    public function __construct(TypeRegistry $typeRegistry, array $config = [])
    {
        $config['fields'] = [
			'customer_id' => Type::int(),
			'store_id' => Type::int(),
			'first_name' => Type::string(),
			'last_name' => Type::string(),
			'email' => Type::string(),
			'address_id' => Type::int(),
			'active' => Type::int(),
			'create_date' => Type::string(),
			'last_update' => Type::string(),
		];

        parent::__construct($config);
    }

    public function queryType()
    {
        $config = [
            'type' => Type::listOf($this),
            'args' => [
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 20
                ]
            ],
            'resolve' => [$this, 'queryResolver']
        ];

        return $config;
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
        $query = Customer::find();

        if (array_key_exists('limit', $args)) {
            $query->limit($args['limit']);
        }

        return $query->all();
    }
}