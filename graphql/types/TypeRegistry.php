<?php

namespace app\graphql\types;
use app\graphql\DataSource;
use GraphQL\Type\Definition\ObjectType;


/**
 * Class TypeRegistry
 * @package app\graphql\types
 * @property ActorType $actor
 * @property FilmType $film
 */
class TypeRegistry
{
    protected $types = [
        'actor' => ActorType::class,
        'film' => FilmType::class
    ];

    /**
     * @var DataSource
     */
    protected $dataSource;

    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->types) === false) {

            throw new \InvalidArgumentException($name . ' is not valid Type');

        }

        if ($this->types[$name] instanceof ObjectType) {

            return $this->types[$name];

        } else {

            $class = $this->types[$name];

            return $this->types[$name] = new $class($this, $this->dataSource);

        }
    }
}