<?php

namespace app\graphql\types;


use app\graphql\DataSource;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

class FilmType extends ObjectType
{
    protected $dataSource;

    public function __construct(TypeRegistry $typeRegistry, DataSource $dataSource, array $config = [])
    {
        $this->dataSource = $dataSource;

        $config = [
            'fields' => [
                'film_id' => Type::int(),
                'title' => Type::string(),
                'description' => Type::string(),
                'actors' => [
                    'type' => Type::listOf($typeRegistry->actor),
                    'resolve' => function($film, $args, $context) use($dataSource) {
                        // results in N+1 Problem

                        //$actorQuery = $dataSource->actorsForFilm($film['film_id']);
                        //return $dataSource->all($actorQuery);

                        // use deferred's to solve the problem

                        DataSource::filmActorsBufferAdd($film['film_id']);

                        return new Deferred(function() use ($film) {
                            DataSource::filmActorsBufferResolve();
                            return DataSource::filmActors($film['film_id']);
                        });
                    }
                ]
            ]
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
        $filmQuery = $this->dataSource->film();

        if (array_key_exists('titleBeginsWith', $args)) {
            $filmQuery->andWhere(['like','title', $args['titleBeginsWith'].'%', false]);
        }
        $filmQuery->limit($args['limit']);
        return $this->dataSource->all($filmQuery);
    }
}