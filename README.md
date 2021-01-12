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
