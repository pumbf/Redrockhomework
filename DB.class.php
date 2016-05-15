<?php
namespace Core;
use \PDO;
class DB
{
	private static $_db;			//单例
	private $_connect;				//数据库
	private $_data = array();		//数据
	private $_table;					//数据表名
	private $_join = array(); 				//join
	private $_field = array();		//字段
	private $_condition = array();	//条件
	private $_orderby = array();	//排序

	/**
	 * 连接数据库
	 * @param [type] $driver   数据库引擎
	 * @param [type] $host     连接名
	 * @param [type] $dbname   数据库名
	 * @param [type] $user     用户名
	 * @param [type] $password 密码
	 */
	private function __construct( $driver, $host, $dbname, $user, $password) {
		//编写dsn
		$dsn = $driver.':dbname='.$dbname.';host='.$host;
		//得到连接
		$db = new PDO($dsn, $user, $password);
		$db->exec('set names utf8');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->_connect = $db;
	}

	/**
	 * 防止被克隆
	 */
	function __clone() {
		echo "this can't be clone";
	}

	/**
	 * 单例模式，创建db
	 * @param  object $config 配置文件
	 * @return object   DB连接      
	 */
	static function getDb() {
		if(!self::$_db instanceof self) {	
			//提取config
			$config = require_once('Config.php');
			extract($config);
			self::$_db = new DB($driver, $host, $dbname, $user, $password);
		}
		return self::$_db;

	}
	/**
	 * 获取需要添加的数据，如果是update只会加入第一个
	 * @param  array $data 数据
	 * @return object       数据库连接
	 */
	function data($data) {
		
		$this->_data = array_merge($this->data, $data);
		return self::$_db;

	}

	function table($table) {

		$this->_table = $table;
		return self::$_db;
	}

	function filed($filed) {
		$this->_field = array_merge($filed, $this->_field);
		return self::$_db;
	}

	function where($condition) {
		$this->_condition = array_merge($this->_condition, $condition);
		return self::$_db;
	}
	// $order = array($filed1, $filed2,...); 前面的优先级高
	function order($order) {
		$this->_orderby = array_merge($this->_orderby, $order);
		return self::$_db;
	}
	// $join = array(array('type'=>'','table'=>'','equa'=>''))
	function join($join) {
		$this->_join = array_merge($join, $this->_join);
		return self::$_db;
	}
	
	/**
	 * 初始化
	 */
	private function reset() {
		$this->_connect;				
		$this->_data = array();		
		$this->_table	= '';				
		$this->_join = array(); 				
		$this->_field = array();		
		$this->_condition = array();	
		$this->_orderby = array();	

	}
	function select() {

		//是否指定表
		if(!$this->_table) {
			echo '表为空';
			return false;
		}
		//选择字段
		if($this->_field) {
			$filed;
			
			//解析数组
			foreach ($this->_field as $value) {
				$filed .= $value.',';
			}

			$filed = rtrim($filed,',');
		}else{
			$filed = '*';
		}

		//区域
		if($this->_condition) {
			
			$condition = ' WHERE ';
			
			//解析数组
			foreach ($this->_condition as $value) {
				$condition .= $value.' AND ';
			}

			$condition = rtrim($condition, ' AND ');
		}else{
			$condition = '';
		}

		//join
		if($this->_join) {			
			$join;
			
			//解析数组
			foreach ($this->_join as $value) {
				extract($value);
				$join .= ' '.$type.' JOIN '.$table;
				if($equa) {
					$join .= ' ON '.$equa;
				}
			}
		}else{
			$join = '';
		}

		//orderby
		if($this->_orderby) {
			$orderby = " ORDER BY ";

			//解析数组
			foreach ($this->_orderby as $value) {
				$orderby .= $value.',';
			}

			$orderby = rtrim($orderby, ',');
		}else{
			$orderby = '';
		}

		//组成sql语句
		$sql = 'SELECT '.$filed.' FROM '.$this->_table.$join.$condition.$orderby;
		//echo $sql;
		//执行查询
		$result = $this->_connect->query($sql);
		//初始化
		$this->reset();
		//结果集
		if($result) 
			return $result->fetchAll(PDO::FETCH_ASSOC);
		else
			return false;
	}

	function insert() {
		
		//是否指定表
		if(!$this->_table) {
			echo '缺少表格';
			return false;
		}

		//数据
		if($this->_data) {
			$filed;
			$Value;

			foreach ($this->_data as $key => $value) {
				$filed .= $key.',';
				$Value .= '\''.addcslashes($value).'\',';
			}
			$filed = rtrim($filed, ',');
			$Value = rtrim($Value, ',');
		}else{
			echo '缺少数据';
			return false;
		}

		//组成sql
		$sql = 'INSERT INTO '.$this->_table.'('.$filed.') VALUES('.$Value.')';
		//初始化
		$this->reset();
		//查询
		return $result = $this->_connect->exec($sql);
	}

	function update() {

		//是否指定表
		if(!$this->_table) {
			echo '缺少表格';
			return false;
		}

		//data
		if($this->_data) {
			$data;
			
			foreach ($this->_data as $key => $value) {
				$data .= $key.'\'='.addcslashes($value).'\',';
			}

			$data = rtrim($data, ',');
		}else{
			echo '缺少数据';
			return false;
		}

		//位置
		if($this->_condition) {
			$condition = ' WHERE ';
			
			//解析数组
			foreach ($this->_condition as $value) {
				$condition .= $value.' AND ';
			}

			$condition = rtrim($condition, ' AND ');
		}else{
			echo '缺少条件';
			return false;
		}

		$sql = 'UPDATE '.$this->_table.' SET '.$data.$condition;
		//初始化
		$this->reset();
		//返回结果
		return $result = $this->_connect->exec($sql);

	}

	function delete() {
		//是否指定表
		if(!$this->_table) {
			echo '缺少表格';
			return false;
		}

		//位置
		if($this->_condition) {
			$condition = ' WHERE ';
			
			//解析数组
			foreach ($this->_condition as $value) {
				$condition .= $value.' AND ';
			}

			$condition = rtrim($condition, ' AND ');
		}else{
			echo '缺少条件';
			return false;
		}

		//构建sql;
		$sql = 'DELETE FROM '.$this->_table.$condition;
		//初始化
		$this->reset();
		//返回结果
		return $this->_connect->exec($sql);
	}

}