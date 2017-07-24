MyBench - MySQL Query Benchmark
========
Symfony 3.3 project with Doctrine
---------------------------------------------
Installation: ```composer install```



Run: 
----
Create task or modify demo one in ```src/MyBenchBundle/Command/RunBenchCommand.php```

```
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
		;
		return $benchTask;
	}
```




```bin/console bench:run --threads=4 --verbosity=0 --bench=1```

A Symfony project created on July 21, 2017, 6:52 pm.
