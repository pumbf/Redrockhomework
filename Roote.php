<?php

/**
* 单一入口路由，根据传值调用类
*/
class Roote 
{
	private static $roote 	//存放对象
	private $modul;			//模块
	private $class;			//得到类存在的位置
	private $func;			//得到使用的函数名
	private $value = array();	//函数参数
	private $method;		//数据传递方式


	private function __construct(){
		$this->getmethod();
		$this->getval();
		$this->path();
		$this->runit();
	}
	/**
	 * 判断数据传递方式，并得到传递的数据
	 * @return void
	 */
	protected function getmethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		switch ($method) {
			case 'POST':
				$this->method = $_POST;
				unset($_POST);
				break;
			
			default:
				$this->method = $_GET;
				unset($_GET);
				break;
		}
	}

	/**
	 * 从传递数据分离模块，类和方法的值
	 * @return void
	 */
	protected function getval()
	{
		$this->modul = isset($this->method['m'])?addslashes($this->method['m']):'clinets';		//模块
		$this->class = isset($this->method['c'])?addslashes($this->method['c']):'index';		//类
		$this->func = isset($this->method['c']?addslashes($this->method['f']):'';		//方法
		unset($this->method['m']);
		unset($this->method['c']);
		unset($this->method['f']);

		$this->value = $this->method;		//得到函数参数的值
	}

	/**
	 * 得到地址url将目标文件引入
	 * @return [type] [description]
	 */
	protected function path()
	{
		$path = './'.$this->modul.$this->class.'class.php';		//相对地址
		if(!file_exists($path))		die("不存在该文件");
		require_once($path);

	}

	/**
	 * 运行目标函数
	 * @return void
	 */
	protected function runit()
	{
		$pos = strrpos($this->class,'/');		//获取class 名字的位置
		$class = substr($this->class, $pos + 1);//获取到class

		if(!isset($this->func) && isset($this->value))	$obj = new $class($value);
		
		else $obj = new $class();

		if(isset($this->func))	{
			$mark  = call_user_func_array(array($obj, $this->func), $this->value);	//判断是否成功
			if($mark === false)			exit("函数执行失败");
		}	

	}	
}