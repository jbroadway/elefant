<?php

use Pheanstalk\Pheanstalk;
use Pheanstalk\Contract\PheanstalkInterface;

/**
 * Implements a job queue service using Beanstalkd via Pheanstalk.
 */
class BeanstalkdJobQueue {
	private static $pheanstalk = null;
	
	private static function conn () {
		if (self::$pheanstalk === null) {
			self::$pheanstalk = Pheanstalk::create (
				envconf ('JobQueue', 'host'),
				envconf ('JobQueue', 'port')
			);
		}

		return self::$pheanstalk;
	}
	
	/**
	 * Post a task to the queue.
	 */
	public static function enqueue ($tube, $data) {
		return self::conn ()->useTube ($tube)->put (
			json_encode ($data),
			PheanstalkInterface::DEFAULT_PRIORITY,
			PheanstalkInterface::DEFAULT_DELAY,
			PheanstalkInterface::DEFAULT_TTR
		);
	}
	
	/**
	 * Watch the specified job queue.
	 */
	public function watch ($tube) {
		return self::conn ()->watch ($tube);
	}

	/**
	 * Ignore the specified job queue.
	 */
	public function ignore ($tube) {
		return self::conn ()->ignore ($tube);
	}
	
	/**
	 * Look for the next job in the queue.
	 */
	public function reserve () {
		return self::conn ()->reserve ();
	}
	
	/**
	 * Delete a job after it's been completed.
	 */
	public function delete ($job) {
		return self::conn ()->delete ($job);
	}
	
	/**
	 * Bury a job that couldn't be completed.
	 */
	public function bury ($job) {
		return self::conn ()->bury ($job);
	}
}
