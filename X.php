<?php

 class X
{
	
	public static $conn ;
	public static $db = '' ;
	public static $tbl = '' ;
	public static $selectStr = '' ;
	public static $whereStr = '' ;
	public static $whereOrStr = '' ;
	public static $orderByStr = '' ;
	public static $groupByStr = '' ;
	public static $limitStr = '' ;
	public static $debugConfig = 0 ;
	
	
	
    function __construct( )
    {
        
    }

    function __destruct()
    {
        $this->clear();
    }
	
	public function clear(){
		
		self::$conn = null;
		self::$db = null ;
		self::$tbl = null ;
		self::$selectStr = null ;
		self::$whereStr = null;
		self::$whereOrStr = null;
		self::$debugConfig = null ;
				
	}
	 ///////////////////////////////////////////////////////////////////////////////
	 public static function setup($constr, $user, $pass, $debugConfig=0) {
		 try {
			 self::$debugConfig = $debugConfig;
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
	 
	 //
	 
	 ///////////////////////////////////////////////////////////////////////////////
	 public static function getAll($sql) {
		 
			try{ 
				//return self::$conn->query($sql, PDO::FETCH_ASSOC);
				if(self::$debugConfig)
			     self::dx($sql);
			     
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
	  public static function getPrimeryKey($tbl) {
		  $id = self::getAll('SHOW KEYS FROM '.$tbl.' WHERE Key_name = "PRIMARY"');
		  return $id[0]['Column_name'];
		}
		
		
public static function debug($v,$ex=1){
     
   echo '<pre>';
   print_r($v); 
   echo '</pre>';
   if($ex)
       exit;
}
public static function dx($v){
   echo '<hr>***** ';
   print_r($v); 
   echo ' ***** <br>';
}


		
	 public static function load($tbl,$id='') {
		 
		 
		 $pid = self::getPrimeryKey($tbl);
		  
			try{ 
				
				if(!empty($id))
				  $sql = 'SELECT * FROM '.$tbl.' WHERE '.$pid.'='.$id;
				else
				  $sql = 'SELECT * FROM '.$tbl.' WHERE 1';
				  
				 if(self::$debugConfig)
					self::dx($sql);
			     
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
	 
	 
	 public static function manage($tbl,$c=0) {
			self::$tbl = $tbl; 
			 
			if($c)
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
				echo  "<br>" . $e->getMessage();
			}
			
	 }
	 
	  public static function select($fieldsStr) {
		   
		    if(!empty($fieldsStr)){ 
				
			    self::$selectStr .=  ' SELECT '.$fieldsStr .' FROM '.self::$tbl;
			}
			
			return self::getResults() ;
	  }
	  
	  public static function orderBy($key='',$otype = 'DESC') {
		   
		   if(!empty($key) ){
			    self::$orderByStr .= ' ORDER BY '.$key.'  '.$otype;
			}
			return self::getResults() ;
		}
		
		public static function groupBy($key='') {
		   
		   if(!empty($key)){
			    self::$groupByStr .= ' GROUP BY '. $key;
			}
			
			return self::getResults() ;
		}
		
		public static function limit($start=0,$end=10) {
		   
		   if(!empty($end)){
			    self::$limitStr .= ' LIMIT '.$start.' , '.$end;
			}else
				self::$limitStr .= ' LIMIT '.$end;
			return self::getResults() ;
		}
		
	  public static function where($key='',$op='',$val='') {
		  
		  
		     
		   if(!empty($val)){
			    self::$whereStr .= ' AND ';
			   if(is_numeric($val)) 
				  self::$whereStr .= $key.' '.$op.' '.$val.' ';
				else
				  self::$whereStr .= $key.' '.$op.' "'.$val.'" ';
		   }
		   
		   if(empty($val) && !empty($op) && !empty($key)){
			    self::$whereStr .= ' AND ';
			    
			    if(is_numeric($op)) 
				self::$whereStr .= $key.' = '.$op.' ';
				else
				self::$whereStr .= $key.' = "'.$op.'" ';
		  }
		   
		    return self::getResults() ;
		   
			
	  }
	  
	  public static function whereOr($key='',$op='',$val='') {
		  
		  
		     
		   if(!empty($val)){
			    self::$whereStr .= ' OR ';
			   if(is_numeric($val)) 
				  self::$whereStr .= $key.' '.$op.' '.$val.' ';
				else
				  self::$whereStr .= $key.' '.$op.' "'.$val.'" ';
		   }
		   
		   if(empty($val) && !empty($op) && !empty($key)){
			    self::$whereStr .= ' OR ';
			    
			    if(is_numeric($op)) 
				self::$whereStr .= $key.' = '.$op.' ';
				else
				self::$whereStr .= $key.' = "'.$op.'" ';
		  }
		   
		     return self::getResults() ;
		   
			
	  }
	  
	   public static function getResults(){
		  
		  try{  
			    if(empty(self::$selectStr))
				  self::$whereStr = 'SELECT * FROM '.self::$tbl.' WHERE ' .self::getPrimeryKey(self::$tbl) ;
				else
				  self::$whereStr = self::$selectStr.' WHERE ' .self::getPrimeryKey(self::$tbl) ;
				
				self::$whereStr .= ' '.self::$groupByStr;
				self::$whereStr .= ' '.self::$orderByStr; 
				self::$whereStr .= ' '.self::$limitStr;
			     
			     if(self::$debugConfig)
			     self::dx(self::$whereStr);
			    
				$stmt = self::$conn->prepare(self::$whereStr); 
				$stmt->execute();

				// set the resulting array to associative
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
				return $result;
			}
			catch(PDOException $e)
			{ 
				echo self::$whereStr . "<br>" . $e->getMessage();
			}
	  }
	 
	  public static function paginate($length = 10, $start=0, $current_page=1 ){
		$data = self::getResults() ;
		$result = array();
		
		if(!empty($data)){
			$total_items = count($data);
			
			if($total_items > $length)
				$total_pages = $total_items / $length ;
			else
				$total_pages = 1;
			
			$previous_link = '';
			if($total_items > $length ){
				$next_link = 'page='.($current_page+1);
				if($current_page > 1)
					$previous_link = 'page='.($current_page-1);
		    }else{
				$next_link = '';
				$previous_link = '';
			}
			
			$result['pagination']['total_items'] = $total_items ;
			$result['pagination']['total_pages'] = $total_pages;
			$result['pagination']['current_page'] = $current_page;
			$result['pagination']['length'] = $length;
			$result['pagination']['previous_link'] = $previous_link;
			$result['pagination']['next_link'] = $next_link;
			$result['pagination']['start'] = $start;
			$result['items'] = $data;
		}
		
        return $result;
        
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
			 if(self::$debugConfig)
					self::dx($sql);
					
			 self::$conn->exec($sql); 
			 return self::$conn->lastInsertId();
		}
		catch(PDOException $e)
		{ 
			echo $sql . "<br>" . $e->getMessage();
		}
     

	 }
	 
	 public static function exec($sql) {
		
		 
		try{ 
			 return self::$conn->exec($sql); 
		}
		catch(PDOException $e)
		{ 
			echo $sql . "<br>" . $e->getMessage();
		}
		/**/
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
		 $pid = self::getPrimeryKey($tbl);
		 $sql = 'DELETE FROM '.$tbl.' WHERE '.$pid.'='.$id;
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
		 
		 if($type == 3){
			    
		 }
		 
		   return $sql;
	 }
	 
	 
}
