<?php

namespace app\graphql\types;


use app\graphql\DataSource;
use app\models\Film;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

class FilmType extends ObjectType
{
    protected $dataSource;

    public function __construct(TypeRegistry $typeRegistry, array $config = [])
    {
        $config = [
            'fields' => [
                'film_id' => Type::int(),
                'title' => Type::string(),
                'description' => Type::string(),
                'actors' => [
                    'type' => Type::listOf($typeRegistry->actor),
                    'resolve' => function($film, $args, $context) {
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

    public function queryType()
    {
        return [
            'type' => Type::listOf($this),
            'args' => [
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 20
                ],
                'titleBeginsWith' => [
                    'type' => Type::string()
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
     * @return array
     */
    public function queryResolver($value, $args, $context, ResolveInfo $resolveInfo)
    {
        $filmQuery = Film::find();

        if (array_key_exists('titleBeginsWith', $args)) {
            $filmQuery->andWhere(['like','title', $args['titleBeginsWith'].'%', false]);
        }

        $filmQuery->limit($args['limit']);

        return $filmQuery->all();
    }
}