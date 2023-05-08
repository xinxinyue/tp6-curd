<?php
/**
 * File model.php
 * @author: xinxinyue
 * @Time: 2021/3/4   14:04
 */

namespace xinxinyue\curd\command;

use think\console\command\Make;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\App;
use think\facade\Db;

class Model extends Make
{
    protected $type = 'Model';

    public function __construct($input = false, $output = false)
    {
        parent::__construct();
        $input && $this->input = $input;
        $output && $this->output = $output;
    }

    protected function configure()
    {
        $this->setName('make:curd-model')
            ->addArgument('name', Argument::REQUIRED, 'Please input your class name')
            ->addArgument('tableName', Argument::REQUIRED, 'Please input your table name')
            ->setDescription('Creat a new model class');
    }

    protected function getStub(): string
    {
        //根据配置更换
        $dir = config('curd.stub_path') ? App::getRootPath().config('curd.stub_path') : __DIR__;
        return $dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\model';
    }

    /**
     * 获取表主键
     * @param $tableName
     * @return string
     * @throws Exception
     */
    protected function getPrimaryKey($tableName)
    {
        $field = Db::query("SELECT column_name FROM INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` WHERE table_name='{$tableName}' AND constraint_name='PRIMARY'");
        if(empty($field)){
            throw new Exception(sprintf("The '{$tableName}' table Primary key does not exist"));
        }
        return $field[0]['column_name'];
    }

    public function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $tableField = Db::query("show COLUMNS FROM ". $this->input->getArgument('tableName'));
        $tableFields = array_column($tableField, 'Field');
        $deleteField = '';
        if(in_array(config('curd.model.delete_field'), $tableFields)){
            $deleteFieldName = config('curd.model.delete_field');
            $deleteField = "use SoftDelete;\n    protected \$deleteTime = '{$deleteFieldName}';\n";
        }

        $autoField = '';
        if(array_intersect([config('curd.model.create_field'),config('curd.model.update_field')], $tableFields)){
            if(config('curd.model.auto_timestamp') != 'int'){
                $autoField .= "    protected \$autoWriteTimestamp  = '" . config('curd.model.auto_timestamp') . "';\n";
            }else{
                $autoField .= "    protected \$autoWriteTimestamp = true;\n";
            }
            if(in_array(config('curd.model.create_field'), $tableFields)){
                $autoField .=  "    protected \$createTime = '" . config('curd.model.create_field') . "';\n";
            }else{
                $autoField .=  "    protected \$createTime = false;\n";
            }
            if(in_array(config('curd.model.update_field'), $tableFields)){
                $autoField .= "    protected \$updateTime = '" . config('curd.model.update_field') . "';\n";
            }else{
                $autoField .= "    protected \$updateTime = false;\n";
            }

        }
        $primaryKey = $this->getPrimaryKey($this->input->getArgument('tableName'));
        $search = [
            '{%className%}',
            '{%namespace%}',
            '{%deleteField%}',
            '{%autoField%}',
            '{%tableName%}',
            '{%primaryKey%}',
        ];

        $replace = [
            $class,
            $namespace,
            $deleteField,
            $autoField,
            $this->input->getArgument('tableName'),
            $primaryKey,
        ];
        return str_replace($search, $replace, $stub);
    }
}