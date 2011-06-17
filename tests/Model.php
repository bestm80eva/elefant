<?php

require_once ('lib/Database.php');
require_once ('lib/Model.php');

class Qwerty extends Model {
	var $key = 'foo';
}

class Foo extends Model {}
class Bar extends Model {
	var $fields = array (
		'foo' => array ('ref' => 'Foo')
	);
}

class ModelTest extends PHPUnit_Framework_TestCase {
	function test_model () {
		db_open (array ('driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table qwerty ( foo char(12), bar char(12) )');

		$q = new Qwerty ();

		$q->foo = 'asdf';
		$q->bar = 'qwerty';
		$this->assertTrue ($q->is_new);
		$this->assertEquals ($q->foo, 'asdf');
		$this->assertTrue ($q->put ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 1);
		$this->assertFalse ($q->is_new);

		// orig()
		$orig = new StdClass;
		$orig->foo = 'asdf';
		$orig->bar = 'qwerty';
		$this->assertEquals ($q->orig (), $orig);

		// fetch_orig()
		$res = array_shift ($q->query ()->fetch_orig ());
		$this->assertEquals ($res, $orig);

		// count()
		$this->assertEquals ($q->query ()->count (), 1);

		// single()
		$single = Qwerty::query ()->single ();
		$this->assertEquals ($single->foo, 'asdf');

		// test requesting certain fields
		$single = Qwerty::query ('bar')->single ();
		$this->assertEquals ($single->foo, null);
		$this->assertEquals ($single->bar, 'qwerty');

		// put()
		$q->bar = 'foobar';
		$this->assertTrue ($q->put ());
		$this->assertEquals (db_shift ('select bar from qwerty where foo = ?', 'asdf'), 'foobar');

		// get()
		$n = $q->get ('asdf');
		$this->assertEquals ($n, $q);
		$this->assertEquals ($n->bar, 'foobar');

		// fetch_assoc()
		$res = $q->query ()->fetch_assoc ('foo', 'bar');
		$this->assertEquals ($res, array ('asdf' => 'foobar'));

		// fetch_field()
		$res = $q->query ()->fetch_field ('bar');
		$this->assertEquals ($res, array ('foobar'));

		// should be the same since they're both
		// Qwerty objects with the same database row
		$res = array_shift ($q->query ()->where ('foo', 'asdf')->order ('foo asc')->fetch ());
		$this->assertEquals ($res, $q);

		// remove()
		$this->assertTrue ($res->remove ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 0);

		// references
		db_execute ('create table foo(id int, name char(12))');
		db_execute ('create table bar(id int, name char(12), foo int)');
		
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
				'/Call to undefined method Bar::fake in .+tests\/Model\.php on line [0-9]+/',
				$e->getMessage ()
			);
		}
	}
}

?>