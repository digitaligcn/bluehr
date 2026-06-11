<?php
function load_env($path){ if(!is_file($path)) return; foreach(file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){ if(str_starts_with(trim($line),'#') || !str_contains($line,'=')) continue; [$k,$v]=explode('=',$line,2); putenv(trim($k).'='.trim($v)); $_ENV[trim($k)]=trim($v); }}
load_env(dirname(__DIR__,2).'/.env');
function config($key){ [$file,$item]=explode('.',$key,2); $data=require dirname(__DIR__,2).'/config/'.$file.'.php'; return $data[$item] ?? null; }
function app_url($path=''){ return rtrim(config('app.url'),'/').'/'.ltrim($path,'/'); }
function redirect($path){ header('Location: '.app_url($path)); exit; }
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function flash($type=null,$msg=null){ if($type && $msg){ $_SESSION['flash'][]=['type'=>$type,'msg'=>$msg]; return; } $f=$_SESSION['flash']??[]; unset($_SESSION['flash']); return $f; }
function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_check(){ if($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['_csrf']??'') !== ($_SESSION['csrf']??''))) { http_response_code(419); exit('Invalid CSRF token'); } }
