<?php
/**
 * File Curd.php

 * @Time: 2021/3/4   14:11
 */

namespace xinxinyue\curd\command;

use think\console\command\Make;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Curd extends Make
{

    protected function getStub()
    {
        return true;
    }

    protected function configure()
    {
        $this->setName('make:curd')
            ->addArgument('name', Argument::REQUIRED, 'Please input your class name')
            ->addArgument('tableName', Argument::REQUIRED, 'Please input your table name')
            ->addOption('require', null, Option::VALUE_REQUIRED, 'Please input your require name')
            ->setDescription('Creat a new curd class');
    }

    public function execute(Input $input, Output $output)
    {
        //create-model
        $this->ExecuteCommand($input, $output, 'Model');
        //create-validate
        $this->ExecuteCommand($input, $output, 'Validate');
        //create-service
        $this->ExecuteCommand($input, $output, 'Service');
        //create-controller
        $this->ExecuteCommand($input, $output, 'Controller');
    }

    protected function ExecuteCommand(Input $input, Output $output, $type)
    {
        $name = $input->getArgument('name');
        $inputClone = clone $input;
        $inputClone->setArgument('name', $name . $type);
        switch ($type) {
            case 'Service':
                $inputClone->setOption('require', $name . 'Model');
                $model = new Service($inputClone, $output);
                break;
            case 'Controller':
                $inputClone->setArgument('name', $name);
                $inputClone->setOption('require', $name . 'Service');
                $model = new Controller($inputClone, $output);
                break;
            case 'Model':
                $model = new Model($inputClone, $output);
                break;
            case 'Validate':
                $model = new Validate($inputClone, $output);
                break;
            default:
                return false;
        }
        $model->setApp($this->app);
        $model->execute($inputClone, $output);
    }
}