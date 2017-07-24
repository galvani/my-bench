<?php

namespace MyBenchBundle\Command;

use MyBenchBundle\Model\BenchQueryTask;
use MyBenchBundle\Model\BenchTask;
use MyBenchBundle\Service\TaskRunner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class RunCommand extends ContainerAwareCommand {
	/** @var  \Doctrine\DBAL\Connection */
	protected $connection;

	/** @var  TaskRunner */
	protected $runner;

	protected function configure() {
		$this
			->setName('bench:run')
			->setDescription('Runs multiple benches')
			->setHelp('This command runs several parallel threads')
			->addOption('threads', 't', InputOption::VALUE_REQUIRED, 'Number of threads.')
			->addOption('bench', 'b', InputOption::VALUE_REQUIRED, 'Bench ID', 1)
			->addOption('verbosity', 'l', InputOption::VALUE_REQUIRED, 'Verbosity', false)
		;
	}

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$this->setConnection($this->getContainer()->get('doctrine.dbal.default_connection'))
			 ->setRunner($this->getContainer()->get('my_bench.task_runner'));

		parent::initialize($input, $output);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$fs = new Filesystem();
		$logDir = $this->getContainer()->get('kernel')->getRootDir() . '/../var/logs';

		$threadCount =  $input->getOption('threads');
		if (is_null($threadCount)) {
			throw new InvalidArgumentException('Missing threads argument');
		}

		$benchId = $input->getOption('bench');


		for ($i=0; $i<$threadCount; $i++) {
			$process = new Process('bin/console bench:run-instance --bench=' . intval($benchId));
			$process->setTimeout(3600);
			$process->start();
			$processes[] = $process;
		}

		if ($input->getOption('verbosity')) {
			echo 'Spawned: ' .$i . " processes\n";
		}


		$outputP = [];
		foreach ($processes as $process) {
			$process->wait(function ($type, $buffer) {
				if (Process::ERR === $type) {
					echo 'ERR > '.$buffer;
				}
			});
			$out = $process->getOutput();
			$fs->appendToFile($logDir . '/bench_' . $benchId . '.log', $out);
			if ($input->getOption('verbosity')) {
				$output->writeln(trim($out));
			}

			$outputP[] = $out;
		}

		$data = [];
		foreach ($outputP as $item) {
			try {
				$obj = json_decode(trim($item));
				$obj->threads = $threadCount;
				$data[] = $obj;
			} catch (\Exception $e) {
				var_dump($e); die();
			}
		}

		$summary = array_merge(['threads'=>$threadCount],(array) $data[0]);
		foreach ($data as $row) {
			$summary['count'] += $row->count;
			$summary['total'] += $row->total;
			$summary['min'] = min($summary['min'],$row->min);
			$summary['max'] = max($summary['max'],$row->max);
			$summary['avg'] = $summary['total'] / $summary['count'];
		}

		$summaryText = vsprintf('TH: %d, C:%d,T:%f,MIN:%f,MAX:%f,AVG:%f', $summary);
		$output->writeln($summaryText);
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
	 * @return RunCommand
	 */
	public function setConnection(\Doctrine\DBAL\Connection $connection): RunCommand {
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
	 * @return RunCommand
	 */
	public function setRunner(TaskRunner $runner): RunCommand {
		$this->runner = $runner;

		return $this;
	}


}