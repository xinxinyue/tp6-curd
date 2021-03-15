<?php
/**
 * File validate.php
 * @author: xinxinyue
 * @Time: 2021/3/4   14:04
 */

/**
 * File model.php
 * @author: xinxinyue
 * @Time: 2021/3/4   14:04
 */

namespace xinxinyue\curd\command;

use think\console\command\Make;
use think\console\input\Argument;
use think\facade\App;
use think\facade\Db;

class Validate extends Make
{
    protected $type = 'Validate';

    public function __construct($input = false, $output = false)
    {
        parent::__construct();
        $input && $this->input = $input;
        $output && $this->output = $output;
    }

    protected function configure()
    {
        $this->setName('make:curd-validate')
            ->addArgument('name', Argument::REQUIRED, 'Please input your class name')
            ->addArgument('tableName', Argument::REQUIRED, 'Please input your table name')
            ->setDescription('Creat a new validate class');
    }

    protected function getStub(): string
    {
        //根据配置更换
        $dir = config('curd.stub_path') ? App::getRootPath() . config('curd.stub_path') : __DIR__;
        return $dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'validate.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\validate';
    }

    public function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $tableName = $this->input->getArgument('tableName');
        $tableField = Db::query("SELECT COLUMN_NAME,COLUMN_KEY,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME  = '{$tableName}'");
        $autoField = [
            config('curd.model.delete_field'),
            config('curd.model.create_field'),
            config('curd.model.update_field'),
        ];
        $rule = '';
        $message = '';
        $scene = '';
        $sceneInsert = [];
        $sceneUpdate = [];
        foreach ($tableField as $key => $value) {
            if(in_array($value['COLUMN_NAME'], $autoField)) {
                continue;
            }
            $rules = $this->getFieldToRule($value);
            if(empty($rules)){
                continue;
            }
            $fieldRule = implode('|', $rules);
            $rule .= "        '{$value['COLUMN_NAME']}' => '{$fieldRule}',\n";
            foreach ($rules as $k => $v) {
                $msg = $this->getRuleMessage($v);
                $fieldName = $value['COLUMN_COMMENT'] ? : $value['COLUMN_NAME'];
                $message .= "        '{$value['COLUMN_NAME']}.{$k}' => '{$fieldName}{$msg}',\n";
            }
            //secen
            $sceneUpdate[] = "'{$value['COLUMN_NAME']}'";
            if($value['COLUMN_KEY'] != 'PRI') {
                $sceneInsert[] = "'{$value['COLUMN_NAME']}'";
            }
        }
        $insert = implode($sceneInsert, ', ');
        $update = implode($sceneUpdate, ', ');
        $scene .= "        'create' => [{$insert}],\n        'update' => [{$update}],";

        $search = [
            '{%className%}',
            '{%namespace%}',
            '{%rule%}',
            '{%message%}',
            '{%scene%}',
        ];

        $replace = [
            $class,
            $namespace,
            $rule,
            $message,
            $scene,
        ];
        return str_replace($search, $replace, $stub);
    }

    /**
     * 获取字段规则
     * @param $field
     * @return array
     */
    protected function getFieldToRule($field)
    {
        $rules = [];
        if($field['COLUMN_KEY'] == 'PRI'){
            $rules['require'] = 'require';
        }
        switch ($field['DATA_TYPE']) {
            case 'int':
            case 'bigint':
            case 'tinyint':
            case 'smallint':
                $rules['number'] = 'number';
                break;
            case 'decimal':
                $rules['float'] = 'float';
                break;
            case 'char':
                $rules['length'] = 'length:'.$field['CHARACTER_MAXIMUM_LENGTH'];
                break;
            case 'varchar':
                $rules['max'] = 'max:'.$field['CHARACTER_MAXIMUM_LENGTH'];
                break;
            case 'datetime':
                $rules['dateFormat'] = 'dateFormat:Y-m-d H:i:s';
                break;
            case 'date':
                $rules['dateFormat'] = 'dateFormat:Y-m-d';
                break;
            case 'time':
                $rules['dateFormat'] = 'dateFormat:H:i:s';
                break;
            default:
        }
        return $rules;
    }

    /**
     *获取规则错误提示信息
     * @param $rule
     * @return string
     */
    protected function getRuleMessage($rule)
    {
        switch ($rule) {
            case 'require':
                $message = '必须填写';
                break;
            case 'number':
                $message = '数据格式必须为数字';
                break;
            case 'float':
                $message = '数据格式必须为数字或浮点数';
                break;
            case 'dateFormat:Y-m-d H:i:s':
                $message = '必须为yyyy-mm-dd hh:ii:ss格式';
                break;
            case 'dateFormat:Y-m-d':
                $message = '必须为yyyy-mm-dd格式';
                break;
            case 'dateFormat:H:i:s':
                $message = '必须为hh:ii:ss格式';
                break;
            default:
                list($ruleName, $num) = explode(':', $rule);
                switch ($ruleName) {
                    case 'length':
                        $message = "长度必须为{$num}个字符";
                        break;
                    case 'max':
                        $message = "最大长度为{$num}个字符";
                        break;
                    default:
                        $message = '数据有误';
                }
        }

        return $message;
    }
}