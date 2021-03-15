<?php
/**
 * File service.php
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
use think\console\input\Option;
use think\Exception;
use think\facade\App;
use think\facade\Db;

class Service extends Make
{
    protected $type = 'Service';

    public function __construct($input = false, $output = false)
    {
        parent::__construct();
        $input && $this->input = $input;
        $output && $this->output = $output;
    }

    protected function configure()
    {
        $this->setName('make:curd-service')
            ->addArgument('name', Argument::REQUIRED, 'Please input your class name')
            ->addArgument('tableName', Argument::REQUIRED, 'Please input your table name')
            ->addOption('require', null, Option::VALUE_REQUIRED, 'Please input your model name')
            ->setDescription('Creat a new service class');
    }

    protected function getStub(): string
    {
        //根据配置更换
        $dir = config('curd.stub_path') ? App::getRootPath() . config('curd.stub_path') : __DIR__;
        return $dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'service.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\service';
    }

    public function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        
        $class = str_replace($namespace . '\\', '', $name);
        if (!$this->input->hasOption('require')) {
            throw new Exception('model is null');
        }
        $model = $this->getModelPath($this->input->getOption('require'), $name);
        $primaryKey = $this->getPrimaryKey($this->input->getArgument('tableName'));

        $event = '';
        if(config('curd.service.admin_event')){
            $event = "event('AdminEvent', ['event' => \MyHelper::getAdminEvent(), 'id' => \$_SESSION['admin_id'], 'data' => \$data]);";
        }
        $search = [
            '{%className%}',
            '{%namespace%}',
            '{%modelNamespace%}',
            '{%model%}',
            '{%primaryKey%}',
            '{%validate%}',
            '{%event%}',
        ];
        $modelArr = explode('\\',$model);
        $replace = [
            $class,
            $namespace,
            $model,
            end($modelArr),
            $primaryKey,
            $this->getValidate($class),
            $event,
        ];
        return str_replace($search, $replace, $stub);
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

    /**
     * 获取model命名空间
     * @param $modelName
     * @param $className
     * @return string
     */
    protected function getModelPath($modelName, $className):string
    {
        if(stripos('\\', $modelName)){
            return $modelName;
        }else{
            if (strpos($modelName, '@')) {
                [$app, $modelName] = explode('@', $modelName);
            }
            $modelArr = array_slice(explode('\\', $className), 0, -2);
            array_push($modelArr,'model', $modelName);
            return trim(implode('\\', $modelArr), '\\');
        }
    }
    
    protected function getValidate($class):string
    {
        $name = $this->input->getArgument('name');
        if ($len = strpos($name, '\\') !== false) {
            return substr($name,0 ,$len) . '/' . str_replace('Service', 'Validate', $class);
        }

        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }
        return $app . '/' . str_replace('Service', 'Validate', $class);
    }
}