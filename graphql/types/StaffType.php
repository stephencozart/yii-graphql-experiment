<?php

namespace app\graphql\types;

use app\models\Staff;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class StaffType extends ObjectType
{
    public function __construct(TypeRegistry $typeRegistry, array $config = [])
    {
        $config['fields'] = [
			'staff_id' => Type::int(),
			'first_name' => Type::string(),
			'last_name' => Type::string(),
			'address_id' => Type::int(),
			'picture' => Type::string(),
			'email' => Type::string(),
			'store_id' => Type::int(),
			'active' => Type::int(),
			'username' => Type::string(),
			'password' => Type::string(),
			'last_update' => Type::string(),
		];

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
        $query = Staff::find();

        if (array_key_exists('limit', $args)) {
            $query->limit($args['limit']);
        }

        return $query->all();
    }
}