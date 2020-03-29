<?php


namespace lirui\NoLimitTree\command;


class Init extends \think\console\Command
{
    protected function configure()
    {
        $this->setName('no-limit-tree:init')
             ->addArgument('name', \think\console\input\Argument::OPTIONAL, "your table name");
    }

    protected function execute(\think\console\Input $input, \think\console\Output $output)
    {
        $name = trim($input->getArgument('name'));
        $name = $name ?: '';

        if (\lirui\NoLimitTree\Init::init($name))
            $message = "init success";
        else
            $message = "check database config";

        $output->writeln($message);
    }
}