<?php

namespace app\graphql;



use app\models\Actor;

class DataSource
{
    protected static $filmActorsBuffer = [];

    protected static $filmActors;


    public static function filmActorsBufferAdd($filmId)
    {
        self::$filmActorsBuffer[$filmId] = [];
    }

    public static function filmActorsBufferResolve()
    {
        $filmIds = array_keys(self::$filmActorsBuffer);

        if ($filmIds) {

            $reader = Actor::find()
                ->join('join', '{{%film_actor}} fa', 'fa.actor_id = {{%actor}}.actor_id')
                ->addSelect(['actor.*','fa.film_id'])
                ->andWhere(['fa.film_id'=>$filmIds])
                ->createCommand()->query();

            foreach($reader as $row) {

                self::$filmActors[$row['film_id']][] = $row;

            }

            self::$filmActorsBuffer = [];
        }

    }

    public static function filmActors($filmId)
    {
        return isset(self::$filmActors[$filmId]) ? self::$filmActors[$filmId] : [];
    }
}