# XORM

## Installation
You can install this package via Composer by running this command in your terminal in the root of your project:
```bash
composer require xorm/xorm:dev-master

use X as X; // Load class with composer  at the top of the file 
// or 
require '/X.php'; // Load direct without composer at the top of the file 

```
Composer detail:

http://rapidsol.blogspot.com/2015/03/download-composerphar.html


# SETUP DATABASE
```bash 
X::setup( 'mysql:host=localhost;dbname=mydb',  'username' ,  'password'  );
```

# SETUP TABLE FOR ADD / UPDATE / DELETE
```bash
X::manage('users'); 
//$dbc['id'] = 1;
$dbc['title'] = 'test title';
X::save($dbc); //for save and update
```
# REMOVE RECORD BY ID. 
```bash
//It will find you primery coulumn auto and delete record. It does't matter primery column is id or bid.
X::delete('users' ,12);
```

# GET ALL BY CUSTOM QUERY
```bash
$sql = 'SELECT xdata FROM `users` WHERE 1 ';
$rec = X::getAll( $sql );
```

# GET RECORD BY ID. 
```bash
//It will find you primery coulumn auto and show record
$rec = X::load('users' ,12 );
X::debug($rec);
```

 # PAGINATION
```bash
//paginate($length = 20, $current_page=5 )
$data = X::paginate(20,5);
 ```
 
# EMPTY TABLE
```bash
 X::emptyX('users');
```
# REMOVE TABLE
```bash
 X::drop('users');
 ```
 
 # EXPORT CSV 
```bash
X::download_send_headers("data_CSV_export_" . date("Y-m-d") . ".csv");
echo X::array2csv($data);
 ```
 
  #  xml to Array 
```bash
$dataArray = X::xmltoArray($xmlstr);
 
 ```
 
 
# FULL DETAIL
```bash
// setup($constr, $user, $pass, $debugConfig=0) $debugConfig 1 will show all queries before result
X::setup( 'mysql:host=localhost;dbname=st_mysite', 'root', 'm' ,1);

/*
CREATE TABLE IF NOT EXISTS `AppVersion` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ClassName` enum('AppVersion') DEFAULT 'AppVersion',
  `LastEdited` datetime DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Version` varchar(12) DEFAULT NULL,
  `Mandatory` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Link` varchar(255) DEFAULT NULL,
  `Platform` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ClassName` (`ClassName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=470 ;

--
-- Dumping data for table `AppVersion`
--

INSERT INTO `AppVersion` (`ID`, `ClassName`, `LastEdited`, `Created`, `Version`, `Mandatory`, `Link`, `Platform`) VALUES
(3, 'AppVersion', '2016-01-20 19:47:09', '2016-01-20 19:47:09', '1.0.1', 1, 'http://apple.com', 'ios'),
(4, 'AppVersion', '2016-01-20 19:47:09', '2016-01-20 19:47:09', '1.0.1', 1, 'http://google.com', 'android'),
(7, 'AppVersion', NULL, NULL, '1.0.2', 1, 'http://apple.com', 'ios'),
(8, 'AppVersion', NULL, NULL, '1.0.6', 0, 'http://apple.com', 'ios'),
(9, 'AppVersion', NULL, NULL, '1.0.10', 1, 'http://apple.com', 'ios');
*/

X::manage( 'AppVersion');

$data = X::select('Mandatory, Platform'); // PUT FIELD STATMENT LIKE FIEL1 AS F, FIELD2 AS B, FIELD3 

$data = X::where('Version','like','1.0.2'); //WHERE WORK WITH AND OPERATOR

$data = X::where('Platform','=','ios');

$data = X::groupBy('Mandatory');

$data = X::limit(1,5);

$data = X::where('Mandatory','=',0);

$data = X::orderBy('Mandatory','DESC');

//paginate($length = 10, $current_page=1 )

$data = X::paginate(20,5); 

//$data = X::whereOr('Mandatory','=',0); //It IS FOR OR CONDITION

//$data = X::where(1); //IT WILL RETURN WHOLE TABLE DATA
```

//$data = X::where(); //IT WILL RETURN WHOLE TABLE DATA

//
//https://packagist.org/packages/xorm/xorm

