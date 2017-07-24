<?php

namespace MyBenchBundle\Command;

use MyBenchBundle\Model\BenchQueryTask;
use MyBenchBundle\Model\BenchTask;
use MyBenchBundle\Service\TaskRunner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunBenchCommand extends ContainerAwareCommand {
	/** @var  \Doctrine\DBAL\Connection */
	protected $connection;

	/** @var  TaskRunner */
	protected $runner;

	protected function configure() {
		$this
			->setName('bench:run-instance')
			->setDescription('Spawns bench process.')
			->setHelp('This command is intended for internal use.')
			->addOption('bench', 'b', InputOption::VALUE_REQUIRED,'Id of bench.')
			->addArgument('--iterations', InputArgument::OPTIONAL, 'Number of iterations.')
		;
	}

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$this->setConnection($this->getContainer()->get('doctrine.dbal.default_connection'))
			 ->setRunner($this->getContainer()->get('my_bench.task_runner'));

		parent::initialize($input, $output);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$task = $this->getTask();


		$resultRun = $this->getRunner()->run($task);
		$output->writeln(json_encode($resultRun));
	}

	protected function getTask() {
		$benchTask = new BenchTask();
		$benchTask
			->setId(1)
			->setBenchId(1)
			->setDatabase('dev_partner_uloz')
			->setIterations(300)
			->setFeedData([
				[1,2],
				[4,1],
				[3,2],
				[1,2],
				[2,3],
				[1,4],
			])
			//->setQuery(' SELECT SQL_NO_CACHE * FROM `receive` WHERE user_id=%d and branch_id=%d');
			//->setQuery(' SELECT SQL_NO_CACHE * FROM `receive` WHERE user_branch_hash = md5(concat_ws("-",%d,%d)) AND `time_closed` IS NULL');
			->setQuery('SELECT DISTINCT ucs.`consignment_id` FROM `user_consignment_scan` ucs 
WHERE ucs.`branch_id` = 1 AND ucs.`consignment_id` 
	IN  (
		SELECT DISTINCT z.`id` FROM `zasilky` z INNER JOIN `route` r ON z.`route_id` = r.`id` WHERE 
		(
			((z.`ukonceni` > DATE_SUB(NOW(), INTERVAL 100 HOUR)))
			OR ((z.`ukonceni` = now()))
		) 
		AND (r.`destination_branch_id` = 1) 
		AND (z.`ukonceno` = 1)
		AND (z.`time_handed_to_transport` IS NULL) 
		AND (z.`status_id` <> 1) 
		AND (z.`transport_service_id` NOT IN (2, 3))
	) 
	AND 
	(
		SELECT MAX(cucs.`id`) FROM `user_consignment_scan` cucs WHERE cucs.`consignment_id` = ucs.`consignment_id`
	)')
		->setQuery('
	SELECT DISTINCT z.`id` FROM `zasilky` z INNER JOIN `route` r ON z.`route_id` = r.`id` WHERE 
		(
			((z.`ukonceni` > DATE_SUB(NOW(), INTERVAL 5 HOUR)))
			OR ((z.`ukonceni` = now()))
		) 
		AND (r.`destination_branch_id` = 1) 
		AND (z.`ukonceno` = 1)
		AND (z.`time_handed_to_transport` IS NULL) 
		AND (z.`status_id` <> 1) 
		AND (z.`transport_service_id` NOT IN (2, 3))
		')


		;
		return $benchTask;
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection(): \Doctrine\DBAL\Connection {
		return $this->connection;
	}

	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 *
	 * @return RunBenchCommand
	 */
	public function setConnection(\Doctrine\DBAL\Connection $connection): RunBenchCommand {
		$this->connection = $connection;

		return $this;
	}

	/**
	 * @return TaskRunner
	 */
	public function getRunner(): TaskRunner {
		return $this->runner;
	}

	/**
	 * @param TaskRunner $runner
	 *
	 * @return RunBenchCommand
	 */
	public function setRunner(TaskRunner $runner): RunBenchCommand {
		$this->runner = $runner;

		return $this;
	}
}