<?php namespace Blue\Core;
class Database {
  private static $pdo;
  public static function pdo(){ if(!self::$pdo){ $cfg=require dirname(__DIR__,2).'/config/database.php'; self::$pdo=new \PDO('mysql:host='.$cfg['host'].';dbname='.$cfg['name'].';charset=utf8mb4',$cfg['user'],$cfg['pass'],[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC]); } return self::$pdo; }
  public static function all($sql,$params=[]){ $s=self::pdo()->prepare($sql); $s->execute($params); return $s->fetchAll(); }
  public static function one($sql,$params=[]){ $s=self::pdo()->prepare($sql); $s->execute($params); return $s->fetch(); }
  public static function exec($sql,$params=[]){ $s=self::pdo()->prepare($sql); return $s->execute($params); }
  public static function insert($sql,$params=[]){ self::exec($sql,$params); return self::pdo()->lastInsertId(); }
}
