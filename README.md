# footup
Un mini framework MVC PHP 

# Description
Ce framework utilise les namespaces pour auto-loading des class.
Pour interagir avec une base de données, le framework vous donne le choix en l'extension MySQLi et PDO.

# Exemple d'initiation de connexion à une base de données
```php
  new ModelName(true) // initialise ModelName avec MySQLi et les config par défaut App/Config/Config.php
  new ModelName(true, null, "pdo") // initialise ModelName avec PDO et les config par défaut App/Config/Config.php
  
  new ModelName(true, ["db_name" => "database"]) // initialise ModelName avec MySQLi et les config ["db_name" => "database"] mergés avec les config par défaut App/Config/Config.php
  
```

# Exemple de création de model
```php
  <?php
    namespace App\Model;
    use Core\Model;
    
    class ModelName extends Model{
      public function __contruct(){
        parent::__construct(...func_get_args());
      }
     
     ...
    }
  
```

# Exemple de création de Controller
```php
  <?php
    namespace App\Controller;
    use Core\Controller;
    use App\Model\ModelName;

    class Home extends Controller{
        public function __construct(){
            parent::__construct();
        }
        public function index(){
            return $this->view("view_file", $data_array);
        }
        public function another_page(){
            $model = new ModelName();
            return $this->view("view_file", $data_array);
        }
        
        
        
        ...
    }
    
    
```
