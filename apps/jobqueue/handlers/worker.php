<?php

/**
 * Example job queue background worker.
 *
 * Listens on the "test-tube" Beanstalkd tube and logs the data
 * it's given to /var/log/elefant-jobqueue-worker.log.
 *
 * Enqueue test jobs via:
 *
 *     JobQueue::enqueue ('test-tube', ['hello' => 'worker']);
 */

if (! $this->cli) exit;

$page->layout = false;

set_time_limit (0);
@ob_end_flush ();

$log_file = '/var/log/elefant-jobqueue-worker.log';

Analog::handler (Analog\Handler\File::init ($log_file));

$worker = JobQueue::worker ();

if ($worker == null) {
	die ("Failed to create a worker instance, job queue not configured.\n");
}

$worker->watch ('test-tube');
$worker->ignore ('default');

while ($job = $worker->reserve ()) {
	Analog::log ($job->getData ());
	
	$worker->delete ($job);
}
