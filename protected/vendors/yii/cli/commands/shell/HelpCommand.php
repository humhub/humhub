<?php
/**
 * HelpCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * HelpCommand displays help information for commands under yiic shell.
 *
 * @property string $help The command description.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands.shell
 * @since 1.0
 */
class HelpCommand extends CConsoleCommand
{
	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 * @return integer non zero application exit code for help
	 */
	public function run($args)
	{
		$runner=$this->getCommandRunner();
		$commands=$runner->commands;
		if(isset($args[0]))
			$name=strtolower($args[0]);
		if(!isset($args[0]) || !isset($commands[$name]))
		{
			echo <<<EOD
At the prompt, you may enter a PHP statement or one of the following commands:

EOD;
			$commandNames=array_keys($commands);
			sort($commandNames);
			echo ' - '.implode("\n - ",$commandNames);
			echo <<<EOD


Type 'help <command-name>' for details about a command.

To expand the above command list, place your command class files
under 'protected/commands/shell', or a directory specified
by the 'YIIC_SHELL_COMMAND_PATH' environment variable. The command class
must extend from CConsoleCommand.

EOD;
		}
		else
			echo $runner->createCommand($name)->getHelp();
		return 1;
	}

	/**
	 * Provides the command description.
	 * @return string the command description.
	 */
	public function getHelp()
	{
		return <<<EOD
USAGE
  help [command-name]

DESCRIPTION
  Display the help information for the specified command.
  If the command name is not given, all commands will be listed.

PARAMETERS
 * command-name: optional, the name of the command to show help information.

EOD;
	}
}