<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 7/21/17
 * Time: 7:43 PM
 */

namespace MyBenchBundle\Service;


use Doctrine\DBAL\Connection;
use MyBenchBundle\Model\BenchTask;

class TaskRunner {
	/** @var  \Doctrine\DBAL\Connection */
	protected $connection;

	/**
	 * TaskRunner constructor.
	 */
	public function __construct(Connection $connection) {
		$this->setConnection($connection);
	}

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection {
		return $this->connection;
	}

	/**
	 * @param Connection $connection
	 *
	 * @return TaskRunner
	 */
	public function setConnection(Connection $connection): TaskRunner {
		$this->connection = $connection;

		return $this;
	}

	public function run(BenchTask $task) {
		$benchMark  = [];
		$connection = $this->getConnection();
		$connection->exec('use ' . $task->getDatabase());
		$queryTemplate = $task->getQuery();
		for ($i = 0; $i < $task->getIterations(); $i++) {
			foreach ($task->getFeedData() as $feedData) {
				//var_dump($queryTemplate);
				//var_dump($feedData);
				$query = vsprintf($queryTemplate, $feedData);
				//die();
				$start = microtime(true);
				try {
					$stmt = $connection->executeQuery($query);
					$stmt->execute();
				} catch (\Exception $e) {
					var_dump($e);
					die();
				}
				$benchMark[] = microtime(true) - $start;
			}

		}

		$total = 0;
		$count = 0;
		$min   = 1000000;
		$max   = 0;
		foreach ($benchMark as $item) {
			$total += $item;
			$min   = min($min, $item);
			$max   = max($max, $item);
			$count++;
		}

		$summary = [
			'count' => $count,
			'total' => $total,
			'min'   => $min,
			'max'   => $max,
			'avg'   => $total / $count,
			'date'	=> date('Ymd His')
		];

		return $summary;
	}
}