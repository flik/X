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

// setup($constr, $user, $pass, $debugConfig=0) $debugConfig 1 will show all queries before result

X::setup( 'mysql:host=localhost;dbname=st_mysite', 'root', 'm' ,1);


X::manage( 'AppVersion');

$data = X::select('Mandatory, Platform'); // PUT FIELD STATMENT LIKE FIEL1 AS F, FIELD2 AS B, FIELD3 

$data = X::where('Version','like','1.0.2'); //WHERE WORK WITH AND OPERATOR

$data = X::where('Platform','=','ios');

$data = X::groupBy('Mandatory');

$data = X::limit(1,5);

$data = X::where('Mandatory','=',0);

$data = X::orderBy('Mandatory','DESC');


//paginate($length = 10, $start=0, $current_page=1 )

$data = X::paginate(20,5); 


//$data = X::whereOr('Mandatory','=',0); //It IS FOR OR CONDITION

 
//$data = X::where(1); //IT WILL RETURN WHOLE TABLE DATA

//$data = X::where(); //IT WILL RETURN WHOLE TABLE DATA

