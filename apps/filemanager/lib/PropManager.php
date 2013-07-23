<?php
class PropManager {
  // Stores last error occurance
  public static $error;
  // Returns last error occurance
	public static function error () {
		return self::$error;
	}
  // gets the data of the requested property
  public static function get ($id) {
		return DB::single ('select * from #prefix#filemanager_propman where id = ?', $id);
	}
  // gets the data of each property that exists
  public static function all () {
  	return DB::fetch ('select * from #prefix#filemanager_propman');
	}
  // sets and updates any new changes to the property data
  public static function set ($id, $type, $label) {
			// takes an extra select query, but works cross-database
			$res = self::get ($id);
			if ($res->type === $type && $res->label === $label) {
				return 'No changes made';
			} elseif (!$res) {
				// doesn't exist yet
				if (DB::execute (
					'insert into #prefix#filemanager_propman (id, type, label) values (?, ?, ?)',
					$id,
					$type,
          $label
				)) {
          DB::execute ('alter table #prefix#filemanager_prop add '.$id.' char(255) not null');
        } else {
					self::$error = DB::error ();
					return false;
				}
			} else {
				// already exists, update
				if (! DB::execute (
					  'update #prefix#filemanager_propman set type = ?, label = ? where id = ?',
					  $type,
					  $label,
					  $id
				)) {
					self::$error = DB::error ();
					return false;
				}
			}
		return self::get($id);
	}
  public static function delete ($id) {
    //check if exists
    $res = self::get ($id);
    //return if doesn't exist
    if (! $res->id === $id) { 
      return 'Property does not exist.';
    //if exists, execute removal
    } elseif (DB::execute ('delete from #prefix#filemanager_propman where id = ?', $id)) {
      DB::execute ('alter table #prefix#filemanager_prop drop column '.$id);
      return true;
    } else {
			self::$error = DB::error ();
      return self::$error;
    }
  }
}
?>