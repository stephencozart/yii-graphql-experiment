<?php

namespace app\graphql\types;


use app\graphql\DataSource;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class ActorType extends ObjectType
{
    protected $dataSource;

    public function __construct(TypeRegistry $typeRegistry, DataSource $dataSource, array $config = [])
    {
        $this->dataSource = $dataSource;

        $config['fields'] = [
            'actor_id' => Type::int(),
            'first_name' => Type::string(),
            'last_name' => Type::string(),
            'last_update' => Type::string()
        ];

        parent::__construct($config);
    }

    /**
     * @param $value
     * @param $args
     * @param $context
     * @param ResolveInfo $resolveInfo
     * @return array
     * @throws \yii\db\Exception
     */
    public function queryResolver($value, $args, $context, ResolveInfo $resolveInfo)
    {
        $actor = $this->dataSource->actor();

        if (array_key_exists('limit', $args)) {
            $actor->limit($args['limit']);
        }

        return $this->dataSource->all($actor);
    }
}