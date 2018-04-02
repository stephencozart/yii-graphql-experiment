<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Inflector;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionType($tableName)
    {
        $schema = \Yii::$app->db->getTableSchema($tableName);

        $map = [
            'integer' => 'Type::int()',
            'string' => 'Type::string()',
            'decimal' => 'Type::float()',
            'float' => 'Type::float()'
        ];

        $columnTemplate = "[\n";

        foreach($schema->columns as $column) {
            $type = isset($map[$column->phpType]) ? $map[$column->phpType] : 'Type::string()';
            $columnTemplate .= "\t\t\t'{$column->name}' => $type,\n";
        }

        $columnTemplate .= "\t\t];";

        $modelName = Inflector::classify($tableName);

        $className = $modelName .'Type';

        $namespace = 'app\graphql\types';

        $template = file_get_contents(\Yii::getAlias('@app/graphql').'/templates/type.php.tpl');

        $template = \Yii::t('app', $template, [
            'fieldConfig'=>$columnTemplate,
            'namespace'=>$namespace,
            'className' => $className,
            'modelName' => $modelName
        ]);



        $path = \Yii::getAlias('@graphql') . '/types/';
        $destination = $path . $className . '.php';

        file_put_contents($destination, $template);

    }
}
