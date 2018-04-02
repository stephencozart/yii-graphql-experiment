<?php

namespace app\graphql;


use yii\db\Query;

class DataSource
{
    protected static $filmActorsBuffer = [];

    protected static $fileActors;
    /**
     * @return Query
     */
    public function actor()
    {
        $query = new Query();
        $query->from('{{%actor}}');
        return $query;
    }

    /**
     * @param $filmId
     * @return Query
     */
    public function actorsForFilm($filmId)
    {
        return $this->actor()->join('join', '{{%film_actor}} fa', 'fa.actor_id = {{%actor}}.actor_id')->andWhere(['fa.film_id'=>$filmId]);
    }

    public function film()
    {
        $query = new Query();
        $query->from('{{%film}}');
        return $query;
    }

    /**
     * @param Query $query
     * @return \yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function reader(Query $query)
    {
        return $query->createCommand()->query();
    }

    /**
     * @param Query $query
     * @return array
     * @throws \yii\db\Exception
     */
    public function all(Query $query)
    {
        return $query->createCommand()->queryAll();
    }

    /**
     * @param Query $query
     * @return array|bool
     */
    public function one(Query $query)
    {
        return $query->one();
    }

    public static function filmActorsBufferAdd($filmId)
    {
        self::$filmActorsBuffer[$filmId] = [];
    }

    public static function filmActorsBufferResolve()
    {
        $filmIds = array_keys(self::$filmActorsBuffer);

        if ($filmIds) {

            $source = new self;

            $reader = $source->actor()
                ->join('join', '{{%film_actor}} fa', 'fa.actor_id = {{%actor}}.actor_id')
                ->addSelect(['actor.*','fa.film_id'])
                ->andWhere(['fa.film_id'=>$filmIds])
                ->createCommand()->query();

            foreach($reader as $row) {

                self::$fileActors[$row['film_id']][] = $row;

            }

            self::$filmActorsBuffer = [];
        }

    }

    public static function filmActors($filmId)
    {
        return isset(self::$fileActors[$filmId]) ? self::$fileActors[$filmId] : [];
    }
}