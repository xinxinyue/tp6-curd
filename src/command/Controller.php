<?php
/**
 * File service.php
 * @author: xinxinyue
 * @Time: 2021/3/4   14:04
 */

/**
 * File model.php
 * @author: xinxinyue
 * @Time: 2021/3/11   16:16
 */

namespace xinxinyue\curd\command;

use think\console\command\Make;
use think\console\input\Argument;
use think\console\input\Option;
use think\Exception;
use think\facade\App;
use think\facade\Db;

class Controller extends Make
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
        $this->setName('make:curd-controller')
            ->addArgument('name', Argument::REQUIRED, 'Please input your class name')
            ->addArgument('tableName', Argument::REQUIRED, 'Please input your table name')
            ->addOption('require', null, Option::VALUE_REQUIRED, 'Please input your model name')
            ->setDescription('Creat a new controller class');
    }

    protected function getStub(): string
    {
        //根据配置更换
        $dir = config('curd.stub_path') ? App::getRootPath() . config('curd.stub_path') : __DIR__;
        return $dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\controller';
    }

    public function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $class = str_replace($namespace . '\\', '', $name);

        if (!$this->input->hasOption('require')) {
            throw new Exception('service is null');
        }
        $service = $this->getServicePath($this->input->getOption('require'), $name);

        $event = '';
        $search = [
            '{%className%}',
            '{%namespace%}',
            '{%serviceNamespace%}',
            '{%service%}',
        ];
        $serviceArr = explode('\\',$service);
        $replace = [
            $class,
            $namespace,
            $service,
            end($serviceArr),
        ];
        return str_replace($search, $replace, $stub);
    }

    /**
     * 获取service命名空间
     * @param $serviceName
     * @param $className
     * @return string
     */
    protected function getServicePath($serviceName, $className):string
    {
        if(stripos($serviceName, '\\')){
            return $serviceName;
        }else{
            $app = '';
            if (strpos($serviceName, '@')) {
                [$app, $serviceName] = explode('@', $serviceName);
            }
            $validateArr = array_slice(explode('\\', $className), 0, -2);
            array_push($validateArr,$app,'service', $serviceName);
            return trim(implode('\\', $validateArr), '\\');
        }

    }

    protected function getValidate($class):string
    {
        $name = $this->input->getArgument('name');
        if ($len = strpos($name, '\\') !== false) {
            return substr($name,0 ,$len) . '.' . $class;
        }

        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }
        return $app . $class;
    }
}