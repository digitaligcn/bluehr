<?php namespace Blue\Core;
class View { public static function render($view,$data=[]){ extract($data); $viewFile=dirname(__DIR__).'/Views/'.$view.'.php'; include dirname(__DIR__).'/Views/layouts/main.php'; } public static function partial($view,$data=[]){ extract($data); include dirname(__DIR__).'/Views/'.$view.'.php'; } }
