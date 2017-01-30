# X
X ORM

require __DIR__.'/X.php';

X::setup( 'mysql:host='.$dbc['host'].';dbname='.$dbc['database'],  $dbc['username'] ,  $dbc['password']  );

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
