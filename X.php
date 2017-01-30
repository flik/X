<?php

 class X
{
	/*
    private $dom = null;  
    public $nodes = array();
    public $parent = null;
    public $children = array();
    public $tag_start = 0;

	*/
	public static $conn ;
	public static $db = '' ;
	public static $tbl = '' ;
	
    function __construct( )
    {
        
    }

    function __destruct()
    {
       // $this->clear();
    }
 
	 ///////////////////////////////////////////////////////////////////////////////
	 public static function setup($constr, $user, $pass) {
		 try {
			$conn = new PDO($constr, $user, $pass);
			// set the PDO error mode to exception
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$conn = $conn;
			//echo "Connected successfully"; 
			return true;
			}
		catch(PDOException $e)
			{
			echo "Connection failed: " . $e->getMessage();
			}
		 
	 }
	 
	 ///////////////////////////////////////////////////////////////////////////////
	 public static function getAll($sql) {
		 
			try{ 
				//return self::$conn->query($sql, PDO::FETCH_ASSOC);
				$stmt = self::$conn->prepare($sql); 
				$stmt->execute();

				// set the resulting array to associative
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
				return $result;
			}
			catch(PDOException $e)
			{ 
				echo $sql . "<br>" . $e->getMessage();
			}
	 }
	 
	 public static function load($tbl,$id) {
		 
			try{ 
				 $sql = 'SELECT * FROM '.$tbl.' WHERE id='.$id;
				$stmt = self::$conn->prepare($sql); 
				$stmt->execute();

				// set the resulting array to associative
				$result = $stmt->fetch(PDO::FETCH_ASSOC); 
				return $result;
			}
			catch(PDOException $e)
			{ 
				echo $sql . "<br>" . $e->getMessage();
			}
	 }
	 
	 
	 public static function manage($tbl) {
			self::$tbl = $tbl; 
			self::setTable();
			$sql = 'DESCRIBE '.$tbl;
			try{ 
					  
				$stmt = self::$conn->prepare($sql); 
				$stmt->execute();
				
				 

				// set the resulting array to associative
				$result = $stmt->fetchAll(PDO::FETCH_COLUMN); 
				
				foreach($result as $v)
					 $obj[$v] = $v;
					 
				$obj = (Object)$obj	;
				return $obj;
			}
			catch(PDOException $e)
			{ 
				echo $sql . "<br>" . $e->getMessage();
			}
			
	 }
	 
	 public static function setTable($arr=''){
		
		if(self::$conn->query('SHOW TABLES LIKE \''.self::$tbl.'\'')->rowCount() > 0){
			$sql = '';
		}else{
			
			$sql  = 'CREATE TABLE '.self::$tbl.' ( ';
			$sql .= 'id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY ';
			$i = 1;
			if(!empty($arr)){
				foreach($arr as $k=>$v){
					
					if(count($arr)!=$i) 
						$sql .= $k.' VARCHAR(255) NULL, ';
					else
						$sql .= $k.' VARCHAR(255) NULL ';
						
					$i++;
				}
			}
			
			$sql .= ');';
			
			//return $sql;
			try{
				// use exec() because no results are returned
				self::$conn->exec($sql);
				 
			}
			catch(PDOException $e)
			{ 
				echo $sql . "<br>" . $e->getMessage();
			}
		}
	 }
	 
	 public static function save($arr) {
		 
		  self::setTable($arr);
		 
	   if(!isset($arr['id'])) 
		 $sql = self::arrayToSql($arr);
		else{
			$result = self::load(self::$tbl,$arr['id']);
			if($result)
				$sql = self::arrayToSql($arr,2);
			else
				$sql = self::arrayToSql($arr);
		}
		 
		
		try{ 
			 self::$conn->exec($sql); 
			 return self::$conn->lastInsertId();
		}
		catch(PDOException $e)
		{ 
			echo $sql . "<br>" . $e->getMessage();
		}
     

	 }
	 
	 public static function update($arr,$keyVal) {
		/*
		echo self::setTable($arr);
		
		if(!isset($arr['id']))
			return false;
			
		 $sql = self::arrayToSql($arr,2);
		// echo $sql;
		
		try{ 
			//self::$conn->exec($sql); 
		}
		catch(PDOException $e)
		{ 
			echo $sql . "<br>" . $e->getMessage();
		}
		*/
 	 }
	 
	 public static function delete($tbl,$id) {
		 $sql = 'DELETE FROM '.$tbl.' WHERE id='.$id;
		try{ 
			self::$conn->exec($sql); 
		}
		catch(PDOException $e)
		{ 
			echo $sql . "<br>" . $e->getMessage();
		}
	 }
	 
	 public static function arrayToSql($arr='', $type = 1){
		 
		   if(empty($arr))
			return '';
		$sql = $keys = $values = '';
		$i = 1;
		if($type == 1){
		   $sql = 'INSERT INTO '.self::$tbl.' (';
		     
		   foreach($arr as $k=>$v){
			    
			   if(count($arr)!=$i){
					$keys .= $k.', ';
					$values .= '"'.$v.'", ';
				}else{
					$keys .= $k.')';
					$values .= '"'.$v.'")';
				}
				 
				$i++;
		   }
		    
		   $sql .= $keys. ' VALUES ('.$values.';';
		 }
		
		 if($type == 2){
			 //UPDATE `myguests` SET `firstname` = 'fa', `lastname` = 'na' WHERE `myguests`.`id` = 1;
			 $sql = 'UPDATE '.self::$tbl.' SET ';
			 foreach($arr as $k=>$v){
					
				   if(count($arr)!=$i){
						$sql .= $k.'= ';
						$sql .= '"'.$v.'", ';
					}else{
						$sql .= $k.'= ';
						$sql .= '"'.$v.'" ';
					}
					
					$i++;
				}
			 
			 $sql .= ' WHERE id='.$arr['id'];
		 
		 }
		   return $sql;
	 }
	 
	 
}
