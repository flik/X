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

# REMOVE RECORD BY ID. 
//It will find you primery coulumn auto and delete record. It does't matter primery column is id or bid.
X::delete('users' ,12);

# GET ALL 
$sql = 'SELECT xdata FROM `users` WHERE 1 ';

$rec = X::getAll( $sql );

# GET RECORD BY ID. 
//It will find you primery coulumn auto and show record
$rec = X::load('users' ,12 );
X::debug($rec);
