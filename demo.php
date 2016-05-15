<?php
namespace Test;
require_once('Core/DB.class.php');
use \Core\DB;
$a = DB::getDb();
$b = $a->table('album')->where(array('album_id<10'))->order(array('artist_id DESC'))->select();
var_dump($b);