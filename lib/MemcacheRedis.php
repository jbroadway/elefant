<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Provides a Memcache-compatible wrapper for the Redis PHP extension, available
 * at https://github.com/nicolasff/phpredis
 *
 * This allows you to use Redis as a drop-in replacement for Memcache as a cache
 * store in Elefant.
 *
 * To enable, add 
 */
class MemcacheRedis {
	/**
	 * Redis connection object.
	 */
	public static $redis;

	/**
	 * Constructor method receives or creates a new Redis object.
	 */
	public function __construct ($redis = false) {
		if ($redis !== false) {
			self::$redis = $redis;
		} else {
			self::$redis = new Redis ();
		}
	}

	/**
	 * Emulates `Memcache::addServer` via `connect`. Also adds
	 * serialization via PHP's serialize/unserialize functions.
	 */
	public function addServer ($server, $port = 6379, $password = false) {
		$res = self::$redis->connect ($server, $port);
		if ($res) {
			if ($password !== false) {
				self::$redis->auth ($password);
			}
			self::$redis->setOption (Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		}
		return $res;
	}

	/**
	 * Emulates `MemcacheExt::cache`.
	 */
	public function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			$val = $function ();
			$this->set ($key, $val, 0, $timeout);
		}
		return $val;
	}

	/**
	 * Emulates `Memcache::get`.
	 */
	public function get ($key) {
		return self::$redis->get ($key);
	}

	/**
	 * Emulates `Memcache::add`.
	 */
	public function add ($key, $value, $flag = 0, $expire = false) {
		if (self::$redis->exists ($key)) {
			return false;
		}
		$this->setnx ($key, $value, $flag, $expire);
	}

	/**
	 * Emulates `Memcache::set` via `set` and `setex`.
	 */
	public function set ($key, $value, $flag = 0, $expire = false) {
		if ($expire) {
			return self::$redis->setex ($key, $expire, $value);
		}
		return self::$redis->set ($key, $value);
	}

	/**
	 * Emulates `Memcache::replace` via `exists` and `set()`.
	 */
	public function replace ($key, $value, $flag = 0, $expire = false) {
		if (! self::$redis->exists ($key)) {
			return false;
		}
		return $this->set ($key, $value, $flag, $expire);
	}

	/**
	 * Emulates `Memcache::delete`.
	 */
	public function delete ($key) {
		return self::$redis->delete ($key);
	}

	/**
	 * Emulates `Memcache::increment` via `incr` and `incrBy`.
	 */
	public function increment ($key, $value = 1) {
		if ($value === 1) {
			return self::$redis->incr ($key);
		}
		return self::$redis->incrBy ($key, $value);
	}

	/**
	 * Emulates `Memcache::decrement` via `decr` and `decrBy`.
	 */
	public function decrement ($key, $value = 1) {
		if ($value === 1) {
			return self::$redis->decr ($key);
		}
		return self::$redis->decrBy ($key, $value);
	}

	/**
	 * Emulates `Memcache::flush` via `flushDB`.
	 */
	public function flush () {
		return self::$redis->flushDB ();
	}
}

?>