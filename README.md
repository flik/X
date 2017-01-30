# X
X ORM

require '/X.php';

# SETUP DATABASE
X::setup( 'mysql:host=localhost;dbname=mydb',  $username ,  $password  );

# SETUP TABLE FOR ADD / UPDATE / DELETE
X::manage('users'); 
//$dbc['id'] = 1;
$dbc['title'] = 'test title';
X::save($dbc); //for save and update

# GET ALL 
$sql = 'SELECT xdata FROM `users` WHERE 1 ';
$rec = X::getAll( $sql );

# GET RECORD BY ID 
$rec = X::load('users' ,12 );
