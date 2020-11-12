<?php

/**
 * Implements a basic job queue for executing background tasks.
 *
 * Usage:
 *
 *     // Post a task to be done
 *     JobQueue::enqueue ('tube-name', ['data' => '...']);
 *
 *     // Look for tasks to be done
 *     $worker = JobQueue::worker ();
 *
 *     $worker->watch ('tube-name')
 *     $worker->ignore ('default');
 *
 *     while ($job = $worker->reserve ()) {
 *         $payload = $job->getData ();
 *         $data = json_decode ($payload);
 *
 *         // Do something with $data
 *
 *         $worker->delete ($job);
 *     }
 */
class JobQueue {
	private static $backend = null;
	private static $service = null;
	
	private static function conn () {
		if (self::$backend != null) {
			return self::$service;
		}
		
		self::$backend = envconf ('JobQueue', 'backend');
		
		switch (self::$backend) {
			case 'beanstalkd':
				require_once ('lib/JobQueueServices/Beanstalkd.php');
				self::$service = new BeanstalkdJobQueue ();
				break;
			default:
				break;
		}
		
		return self::$service;
	}
	
	/**
	 * Post a task to the queue.
	 */
	public static function enqueue ($tube, $data) {
		$conn = self::conn ();
		
		if ($conn === null) return false;
		
		return $conn->enqueue ($tube, $data);
	}
	
	/**
	 * Return the job queue service that can be used to watch
	 * for tasks in the queue.
	 */
	public static function worker () {
		return self::conn ();
	}
}
