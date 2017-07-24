<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 7/21/17
 * Time: 7:24 PM
 */

namespace MyBenchBundle\Model;


class BenchTask {
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $benchId;

	/**
	 * @var string
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $query;

	/**
	 * @var enum
	 */
	protected $feedType;

	/**
	 * @var array[]
	 */
	protected $feedData;

	/**
	 * @var int
	 */
	protected $iterations;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return BenchTask
	 */
	public function setId(int $id): BenchTask {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getBenchId(): int {
		return $this->benchId;
	}

	/**
	 * @param int $benchId
	 *
	 * @return BenchTask
	 */
	public function setBenchId(int $benchId): BenchTask {
		$this->benchId = $benchId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDatabase(): string {
		return $this->database;
	}

	/**
	 * @param string $database
	 *
	 * @return BenchTask
	 */
	public function setDatabase(string $database): BenchTask {
		$this->database = $database;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuery(): string {
		return $this->query;
	}

	/**
	 * @param string $query
	 *
	 * @return BenchTask
	 */
	public function setQuery(string $query): BenchTask {
		$this->query = $query;

		return $this;
	}

	/**
	 * @return enum
	 */
	public function getFeedType(): enum {
		return $this->feedType;
	}

	/**
	 * @param enum $feedType
	 *
	 * @return BenchTask
	 */
	public function setFeedType(enum $feedType): BenchTask {
		$this->feedType = $feedType;

		return $this;
	}

	/**
	 * @return array[]
	 */
	public function getFeedData(): array {
		return $this->feedData;
	}

	/**
	 * @param array[] $feedData
	 *
	 * @return BenchTask
	 */
	public function setFeedData(array $feedData): BenchTask {
		$this->feedData = $feedData;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getIterations(): int {
		return $this->iterations;
	}

	/**
	 * @param int $iterations
	 *
	 * @return BenchTask
	 */
	public function setIterations(int $iterations): BenchTask {
		$this->iterations = $iterations;

		return $this;
	}
}