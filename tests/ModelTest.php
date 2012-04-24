<?php

require_once ('lib/Functions.php');
require_once ('lib/Autoloader.php');

class Qwerty extends Model {
	var $key = 'foo';
}

class Foo extends Model {}

class Bar extends Model {
	var $fields = array (
		'foo' => array ('belongs_to' => 'Foo')
	);
}

class Gallery extends Model {
	public $fields = array (
		'cover' => array ('has_one' => 'Cover'),
		'items' => array ('has_many' => 'Item', 'field_name' => 'gallery_id')
	);
}

class Cover extends Model {
	public $fields = array (
		'gallery' => array ('belongs_to' => 'Gallery')
	);
}

class Item extends Model {
	public $fields = array (
		'gallery' => array ('belongs_to' => 'Gallery', 'field_name' => 'gallery_id')
	);
}

class Author extends Model {
	public $fields = array (
		'books' => array (
			'many_many' => 'Book',
			'join_table' => 'book_author',
			'this_field' => 'author',
			'that_field' => 'book',
			'order_by' => 'name'
		)
	);
}

class Book extends Model {
	public $fields = array (
		'authors' => array (
			'many_many' => 'Author',
			'join_table' => 'book_author',
			'this_field' => 'book',
			'that_field' => 'author',
			'order_by' => 'name'
		)
	);
}

class ModelTest extends PHPUnit_Framework_TestCase {
	protected static $q;

	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		$sql = sql_split ("create table qwerty ( foo char(12), bar char(12) );
		create table foo(id int, name char(12));
		create table bar(id int, name char(12), foo int);
		create table gallery (
			id integer primary key,
			title char(48)
		);
		create table cover (
			id integer primary key,
			gallery integer unique,
			title char(48)
		);
		create table item (
			id integer primary key,
			gallery_id integer,
			title char(48)
		);
		insert into gallery (id, title) values (1, 'Gallery One');
		insert into cover (id, gallery, title) values (1, 1, 'Cover One');
		insert into item (id, gallery_id, title) values (1, 1, 'Item One');
		insert into item (id, gallery_id, title) values (2, 1, 'Item Two');
		insert into item (id, gallery_id, title) values (3, 1, 'Item Three');
		insert into gallery (id, title) values (2, 'Gallery Two');
		insert into cover (id, gallery, title) values (2, 2, 'Cover Two');
		insert into item (id, gallery_id, title) values (4, 2, 'Item Four');
		insert into item (id, gallery_id, title) values (5, 2, 'Item Five');
		insert into item (id, gallery_id, title) values (6, 2, 'Item Six');
		create table author (
			id integer primary key,
			name char(32)
		);
		create table book (
			id integer primary key,
			name char(32)
		);
		create table book_author (
			book int not null,
			author int not null
		);
		insert into author (id, name) values (1, 'Johnny Fast Fingers');
		insert into author (id, name) values (2, 'Frankie Bazzar');
		insert into book (id, name) values (1, 'Johnny & Frankie');
		insert into book (id, name) values (2, 'Jamaican Me Crazy');
		insert into book_author (book, author) values (1, 1);
		insert into book_author (book, author) values (1, 2);
		insert into book_author (book, author) values (2, 1);
		insert into book_author (book, author) values (2, 2);
		");
		foreach ($sql as $query) {
			DB::execute ($query);
		}

		self::$q = new Qwerty ();
	}

	function test_construct () {
		self::$q->foo = 'asdf';
		self::$q->bar = 'qwerty';
		$this->assertTrue (self::$q->is_new);
		$this->assertEquals (self::$q->foo, 'asdf');
		$this->assertTrue (self::$q->put ());
		$this->assertEquals (DB::shift ('select count() from qwerty'), 1);
		$this->assertFalse (self::$q->is_new);
	}

	function test_sql () {
		// And clauses
		$sql = self::$q->query ()
			->where ('foo', 'one')
			->where ('foo', 'two')
			->sql ();
		$this->assertEquals ('select * from `qwerty` where `foo` = ? and `foo` = ?', $sql);

		// Or clauses
		$sql = self::$q->query ()
			->where ('foo', 'one')
			->or_where ('bar', 'two')
			->sql ();
		$this->assertEquals ('select * from `qwerty` where `foo` = ? or `bar` = ?', $sql);

		// Associative arrays
		$sql = self::$q->query ()
			->where (array (
				'foo' => 'one',
				'bar' => 'two'
			))
			->sql ();
		$this->assertEquals ('select * from `qwerty` where (`foo` = ? and `bar` = ?)', $sql);

		// Closures
		$sql = self::$q->query ()
			->where (function ($query) {
				$query->where ('foo', 'one');
				$query->where ('bar', 'two');
			})
			->sql ();
		$this->assertEquals ('select * from `qwerty` where (`foo` = ? and `bar` = ?)', $sql);

		// Custom fields
		$sql = self::$q->query ('count(*)')
			->where (function ($query) {
				$query->where ('foo', 'one');
				$query->where ('bar', 'two');
			})
			->sql ();
		$this->assertEquals ('select count(*) from `qwerty` where (`foo` = ? and `bar` = ?)', $sql);

		// Custom clauses
		$sql = self::$q->query ()
			->where ('foo = "one"')
			->sql ();
		$this->assertEquals ('select * from `qwerty` where foo = "one"', $sql);

		// Field array
		$sql = self::$q->query (array ('foo', 'bar'))->sql ();
		$this->assertEquals ('select `foo`, `bar` from `qwerty`', $sql);

		// Group by
		$sql = self::$q->query ()
			->group ('foo')
			->group ('bar')
			->sql ();
		$this->assertEquals ('select * from `qwerty` group by `foo`, `bar`', $sql);

		// Order by
		$sql = self::$q->query ()
			->order ('foo', 'asc')
			->order ('bar desc')
			->sql ();
		$this->assertEquals ('select * from `qwerty` order by `foo` asc, bar desc', $sql);

		// Invalid limit/offset
		$sql = self::$q->query ()->sql (';delete from qwerty where 1=1');
		$this->assertFalse ($sql);
		$sql = self::$q->query ()->sql (20, ';delete from qwerty where 1=1');
		$this->assertFalse ($sql);

		// Valid limit/offset
		$sql = self::$q->query ()->sql (20, 0);
		$this->assertEquals ('select * from `qwerty` limit 20 offset 0', $sql);
	}

	function test_orig () {
		// orig()
		$orig = new StdClass;
		$orig->foo = 'asdf';
		$orig->bar = 'qwerty';
		$this->assertEquals (self::$q->orig (), $orig);
	}

	function test_fetch_orig () {
		// fetch_orig()
		$orig = new StdClass;
		$orig->foo = 'asdf';
		$orig->bar = 'qwerty';
		$test = self::$q->query ()->fetch_orig ();
		$res = array_shift ($test);
		$this->assertEquals ($res, $orig);
	}

	function test_count () {
		// count()
		$this->assertEquals (self::$q->query ()->count (), 1);
	}

	function test_single () {
		// single()
		$single = Qwerty::query ()->single ();
		$this->assertEquals ($single->foo, 'asdf');

		// test requesting certain fields
		$single = Qwerty::query ('bar')->single ();
		$this->assertEquals ($single->foo, null);
		$this->assertEquals ($single->bar, 'qwerty');
	}

	function test_put () {
		// put()
		self::$q->bar = 'foobar';
		$this->assertTrue (self::$q->put ());
		$this->assertEquals (DB::shift ('select bar from qwerty where foo = ?', 'asdf'), 'foobar');
	}

	function test_get () {
		// get()
		$n = self::$q->get ('asdf');
		$this->assertEquals ($n, self::$q);
		$this->assertEquals ($n->bar, 'foobar');
	}

	function test_fetch_assoc () {
		// fetch_assoc()
		$res = self::$q->query ()->fetch_assoc ('foo', 'bar');
		$this->assertEquals ($res, array ('asdf' => 'foobar'));
	}

	function test_fetch_field () {
		// fetch_field()
		$res = self::$q->query ()->fetch_field ('bar');
		$this->assertEquals ($res, array ('foobar'));
	}

	function test_remove () {
		// should be the same since they're both
		// Qwerty objects with the same database row
		$test = self::$q->query ()->where ('foo', 'asdf')->order ('foo asc')->fetch ();
		$res = array_shift ($test);
		$this->assertEquals ($res, self::$q);

		// remove()
		$this->assertTrue ($res->remove ());
		$this->assertEquals (DB::shift ('select count() from qwerty'), 0);
	}

	function test_verify () {
		$f = new Foo (array ('id' => 1, 'name' => 'Joe'));

		$f->verify = array (
			'id' => array (
				'type' => 'numeric',
				'skip_if_empty' => 1
			),
			'name' => array (
				'email' => 1
			)
		);

		$f->put ();
		$this->assertEquals ($f->error, 'Validation failed for: name');
	}

	function test_batch () {
		// Clear existing records
		$this->assertTrue (DB::execute ('delete from foo'));
		
		// Test batch array of insertions
		$this->assertTrue (Foo::batch (array (
			array ('id' => 1, 'name' => 'One'),
			array ('id' => 2, 'name' => 'Two'),
			array ('id' => 3, 'name' => 'Three')
		)));
		$this->assertEquals (3, Foo::query ()->count ());

		// Test closure batch
		$this->assertTrue (Foo::batch (function () {
			// Update an existing item
			$one = new Foo (1);
			$one->name = 'Joe';
			if (! $one->put ()) {
				$f->error = $one->error;
				return false;
			}

			// Add a new one too
			$four = new Foo (array ('name' => 'Four'));
			if (! $four->put ()) {
				$f->error = $four->error;
				return false;
			}
		}));
		$this->assertEquals (4, Foo::query ()->count ());
		$one = new Foo (1);
		$this->assertEquals ('Joe', $one->name);

		// Test rollback on false
		$this->assertFalse (Foo::batch (function () {
			$five = new Foo (array ('name' => 'Five'));
			$five->put ();
			return false;
		}));
		$this->assertEquals (4, Foo::query ()->count ());
	}

	function test_references () {
		// basic references (belongs_to)		
		$f = new Foo (array ('id' => 1, 'name' => 'Joe'));
		$f->put ();
		$b = new Bar (array ('id' => 1, 'name' => 'Jim', 'foo' => 1));
		$b->put ();

		$this->assertEquals ($b->name, 'Jim');
		$this->assertEquals ($b->foo, 1);
		$this->assertEquals ($b->foo ()->name, 'Joe');
		$this->assertEquals ($b->foo ()->name, 'Joe');
		
		// fake reference should fail
		try {
			$this->assertTrue ($b->fake ());
		} catch (Exception $e) {
			$this->assertRegExp (
				'/Call to undefined method Bar::fake in .+tests\/ModelTest\.php on line [0-9]+/',
				$e->getMessage ()
			);
		}
	}

	function test___call () {
		// test more advanced references

		// fetch a gallery
		$gallery = new Gallery (1);
		$this->assertEquals ('Gallery One', $gallery->title);

		// get its cover (has_one)
		$cover = $gallery->cover ();
		$this->assertEquals ('Cover One', $cover->title);

		// get the cover's gallery now (belongs_to)
		$gallery = $cover->gallery ();
		$this->assertEquals ('Gallery One', $gallery->title);

		// get the gallery's items (has_many)
		$items = $gallery->items ();
		$this->assertEquals (3, count ($items));
		$this->assertEquals ('Item One', $items[0]->title);

		// test order_by
		$gallery->fields['items'] = array (
			'has_many' => 'Item',
			'field_name' => 'gallery_id',
			'order_by' => 'id desc'
		);
		$items = $gallery->items (true);
		$this->assertEquals (3, count ($items));
		$this->assertEquals ('Item Three', $items[0]->title);

		// and chaining them all together
		$this->assertEquals ('Gallery One', $items[1]->gallery ()->cover ()->gallery ()->title);
	}

	function test_many_many () {
		// test many to many relationships

		// get an author
		$author = new Author (1);
		$this->assertEquals ('Johnny Fast Fingers', $author->name);

		// get his books
		$books = $author->books ();
		$this->assertEquals (2, count ($books));
		$this->assertEquals ('Jamaican Me Crazy', $books[1]->name);

		// now get all authors from a book
		$authors = $books[1]->authors ();
		$this->assertEquals (2, count ($authors));
		$this->assertEquals ('Frankie Bazzar', $authors[1]->name);
	}
}

?>