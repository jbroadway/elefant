<?php

require_once ('lib/Database.php');
require_once ('lib/Model.php');
require_once ('apps/admin/models/Versions.php');

class Foo extends Model {}

class VersionsTest extends PHPUnit_Framework_TestCase {
	function test_versions () {
		db_open (array ('driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table foo (id int not null, name char(32) not null)');
		if (! db_execute ('create table versions (
			id integer primary key,
			class char(72) not null,
			pkey char(72) not null,
			user int not null,
			ts datetime not null,
			serialized text not null
		)')) {
			die ('Failed to create versions table');
		}
		if (! db_execute ('create index versions_class on versions (class, pkey, ts)')) {
			die ('Failed to create versions_class index');
		}
		if (! db_execute ('create index versions_user on versions (user, ts)')) {
			die ('Failed to create versions_user index');
		}

		
		$foo = new Foo (array ('id' => 1, 'name' => 'Test'));
		$foo->put ();

		$v = Versions::add ($foo);
		$this->assertEquals (db_shift ('select count(*) from versions'), 1);
		$this->assertEquals ($v->class, 'Foo');
		$this->assertEquals ($v->pkey, 1);
		$this->assertEquals ($v->user, 0);
		$foo2 = Versions::restore ($v);
		$this->assertEquals ($foo, $foo2);

		// test diff
		$foo->name = 'Test2';
		$foo->put ();

		$v = Versions::add ($foo);
		$this->assertEquals (db_shift ('select count(*) from versions'), 2);

		$modified = Versions::diff ($foo2, $foo);
		$this->assertEquals ($modified[0], 'name');

		// test history
		$history = Versions::history ($foo);
		$this->assertEquals (count ($history), 2);

		$modified = Versions::diff ($history[0], $history[1]);
		$this->assertEquals ($modified[0], 'name');

		// test recent
		$recent = Versions::recent ();
		$this->assertEquals (count ($recent), 1);

		$restored = Versions::restore ($recent[0]);
		$this->assertEquals ($restored->name, 'Test2');
	}
}

?>