# Footup MVC PHP Framework

Un mini framework MVC PHP qui comporte :

* CLI support for generating class
* Translation support
* Config using PHP File or .env (Rename env to .env)
* Gestion de Requête (Request)
* Validator (Form Validation - Not validating UploadedFile)
* Pagination (Pagination and Pagination View)
* Gestion de Reponse (Response)
* Session
* Email
* Routing
* Controller AND Middleware
* Model RelationShips ($hasOne, $hasMany, $belongsTo, $belongsToMany)
* Model QueryBuilder
* Model Events CallBacks
* Fichiers (Upload File)
* Extensible (You can integrate any library you want and you can add (news folders and class) in the App Directory in condition you use psr4)

## ReadMe should be updated -- (Soon i'll create the docs website for FootUp PHP MVC Framework)
------------------

## Directories tree
```
.
├── app
│   ├── Config
│   │   ├── Autoload.php
│   │   ├── Config.php
│   │   ├── Constants.php
│   │   ├── Email.php
│   │   ├── Paginator.php
│   │   ├── Form.php
│   │   └── Routes.php
│   ├── Controller
│   │   ├── BaseController.php
│   │   └── Home.php
│   ├── Functions.php
│   ├── Lang
│   ├── Libs
│   ├── Middle
│   │   └── Maintenance.php
│   ├── Model
│   │   └── Contact.php
│   └── View
│       └── accueil.php
├── core
│   ├── Boot.php
│   ├── Cli
│   │   ├── CLI.php
│   │   ├── Colors.php
│   │   ├── Console.php
│   │   ├── Exception.php
│   │   ├── Generator.php
│   │   ├── Options.php
│   │   ├── TableFormatter.php
│   │   └── Tpl
│   │       ├── Controller.tpl
│   │       ├── Middle.tpl
│   │       ├── Model.tpl
│   │       └── View.tpl
│   ├── Config
│   │   ├── Autoload.php
│   │   ├── Config.php
│   │   ├── DotEnv
│   │   │   ├── DotEnv.php
│   │   │   └── Exception
│   │   │       ├── Exception.php
│   │   │       └── InvalidPathException.php
│   │   ├── Email.php
│   │   └── Mime.php
│   ├── Controller.php
│   ├── Files
│   │   ├── File.php
│   │   └── FileSystem.php
│   ├── Paginator
│   │   ├── AbstractPaginator.php
│   │   ├── Page.php
│   │   ├── PaginatorException.php
│   │   ├── Paginator.php
│   │   ├── Views
│   │   │   └── default.php
│   │   └── Paginator.php
│   ├── Utils
│   │   ├── Arrays
│   │   │   ├── Arr.php
│   │   │   ├── Arrayable.php
│   │   │   ├── ArrDots.php
│   │   │   ├── Collection.php
│   │   │   └── Dots.php
│   │   ├── Validator
│   │   │   ├── Validate.php
│   │   │   └── Validator.php
│   │   └── Str.php
│   ├── Footup.php
│   ├── Functions.php
│   ├── Html
│   │   ├── Form.php
│   │   └── Html.php
│   ├── Http
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Session.php
│   ├── I18n
│   │   ├── Exceptions
│   │   │   └── I18nException.php
│   │   ├── TimeDifference.php
│   │   └── Time.php
│   ├── Lang
│   │   ├── fr
│   │   │   ├── core.json
│   │   │   ├── date.json
│   │   │   ├── db.json
│   │   │   ├── email.json
│   │   │   ├── file.json
│   │   │   ├── http.json
│   │   │   └── view.json
│   │   └── Lang.php
│   ├── Model.php
│   ├── Orm
│   │   └── BaseModel.php
│   └── Routing
│       ├── Middle.php
│       ├── Route.php
│       └── Router.php
├── public
│   ├── assets
│   │   ├── css
│   │   │   └── style.css
│   │   └── js
│   │       └── script.js
│   ├── error
│   │   ├── 404.html
│   │   └── 500.html
│   ├── index.php
│   └── uploads
├── env
├── footup
├── LICENSE
└── README.md
```

## Description

Ce framework utilise les namespaces pour auto-loading des class.
Pour interagir avec une base de données, le framework **Footup** utilise l'extension **PDO**.

Vous êtes libre et maître de votre code, soyez à l'aise

-- retrouvez toutes les configurations dans le dossier **app/Config** --

Ce framework a tout ce qu'il faut pour vous aider à developper vite votre application.

**#TO-DO:** Une documentation complète doit être rédigée si possible

## Exemple d'Utilisation du CLI

```bash
nuka@hacker_pc:~$ php footup 
```

## Exemple de Model

```php
<?php

namespace App\Model;
use Footup\Model;

class Contact extends Model{
  // If not defined, the name of this class is used
  protected $table = 'contact';
  /**
   * PrimaryKey
   *
   * @var string
   */
  protected $primaryKey = 'idcont';

  protected $beforeInsert         = [];
  protected $beforeFind           = [];
  protected $beforeDelete         = [];
  protected $beforeUpdate         = [];
  protected $afterInsert          = [];
  protected $afterFind            = [];
  protected $afterDelete          = [];
  protected $afterUpdate          = [];
}

// Using the model Contact
.....
  use App\Model\Contact;
  ....
  // Retrouve tout | retrieve all
  $contacts = Contact::all();
  ------------ others methods --------------
  $c = new Contact();
  $contacts = $c->get();
  foreach($contacts as $contact)
    echo $contact->email;
  
  # you can also use 
  $contact->setEmail("fuck@you.yh");

  // Generating form ******
  $contact->getForm();

  var_dump($c->firstByEmail('faus@fizz.io'));
  ..........................
  
```

## _Globals Functions | Fonctions glabales_

> **request($index = null, $arg = null)**

```php
  /**
   * Une fonction pour exposer l'objet Request
   *
   * @param mixed $index
   * @param mixed $arg
   * @return Footup\Http\Request|mixed
   */
  request($index = null, $arg = null)
```

> **calledController($withNamespace = true)**

```php
  /**
   * Retrouve le controlleur en cours d'utilisation
   *
   * @param boolean $withNamespace
   * @return string
   */
  calledController($withNamespace = true)
```

> **calledMethod()**

```php
  /**
   * Retrouve la méthode couremment utilisée
   *
   * method of the current called controller
   * 
   * @return string
   */
  calledMethod()
```

> And many mores others globals functions

## Uploading file | Téleverser un fichier

```php
  #eg. file field = image
  # @uses one below
  request('image') or request()->image or request()->file('image')

  # remember that request is available directly in the controller so :
  $this->request->image

  # et pour enregistrer le fichier | and for saving :
  request('image')->save(?string $destinationName = null, bool $replace = true) or request()->image->save(?string $destinationName = null, bool $replace = true) or request()->file('image')->save(?string $destinationName = null, bool $replace = true)

  # remember that request is available directly in the controller so :
  $this->request->image
```

## Getting Auto Genereted Form | Avoir un formulaire auto généré

```php
  $contact = new ContactModel();
  $contact->getForm("#", [], true /* true to print */ )
  # or use
  echo $contact->getForm();
```

## QueryBuilder Methods | Méthode de Model QueryBuilder

### **_# model->from($table, $reset = true): $this_**

> Utilise **$reset = false** si vous ne souhaiter pas recommencer une requête.
Utilise **$table** pour changer de table (cette méthode vous l'utiliserez rarement)
<!-- -->

### **_# model->join($table, $fields, $type = 'INNER', $operator = '='): $this_**

> * $type doit être un dans ['INNER', 'LEFT OUTER', 'RIGHT OUTER', 'FULL OUTER']
> * $fields string|array ex: "user.id = article.id_user" | ["user.id" => "article.id_user"]
<!-- -->

### **_# model->leftJoin($table, $fields, $operator = '='): $this_**

> * $fields string|array ex: "user.id = article.id_user" | ["user.id" => "article.id_user"]
<!-- -->

### **_# model->rightJoin($table, $fields, $operator = '='): $this_**

> * $fields string|array ex: "user.id = article.id_user" | ["user.id" => "article.id_user"]
<!-- -->

### **_# model->where($key, $val = null, $operator = null, $link = ' AND ', $escape = true): $this_**

> * $key string|array ex: "id = 1" | ["id" => 1]
> * $link string where $link is AND | OR | IS
> * $fields string|array ex: "arnauld" | [2, 3, 5] for $operator IN | NOT IN
<!-- -->

### **_# model->whereOr($key, $val = null, $operator = null, $link = ' AND ', $escape = true): $this_**

>
  ```php
    /**
      * @param string|array $key
      * @param null $operator
      * @param null $val
      * @return $this
      */
    public function whereOr($key, $val = null, $operator = null, $escape = true)
  ```

### **_# model->whereIn($key, array $val, $escape = true) | whereOrIn($key, array $val, $escape = true): $this_**

>
  ```php
    /**
     * @param $key
     * @param array $val
     * @return $this
     */
    public function whereIn($key, array $val, $escape = true)
  ```

### **_# model->whereRaw($str) | whereOrRaw($str): $this_**

>
  ```php
    /**
     * @param string $str
     * @return $this
     */
    public function whereRaw($str)
  ```

### **_# model->whereNotIn($key, array $val, $escape = true) | whereOrNotIn($key, array $val, $escape = true): $this_**

>
  ```php
    /**
     * @param $key
     * @param array $val
     * @return $this
     */
    public function whereNotIn($key, array $val, $escape = true)
  ```

### **_# model->whereNotNull($key) | whereOrNotNull($key): $this_**

>
  ```php
    /**
     * @param string $key
     * @return $this
     */
    public function whereNotNull($key)
  ```

### **_# model->whereNull($key) | whereOrNull($key): $this_**

>
  ```php
    /**
     * @param string $key
     * @return $this
     */
    public function whereNull($key)
  ```

### **_# model->asc($field): $this_**

>
  ```php
    /**
     * Adds an ascending sort for a field.
     *
     * @param string $field Field name
     * @return object Self reference
     */
    public function asc($field)
  ```

### **_# model->desc($field): $this_**

>
  ```php
    /**
     * Adds an descending sort for a field.
     *
     * @param string $field Field name
     * @return object Self reference
     */
    public function desc($field)
  ```

### **_# model->orderBy($field, $direction = 'ASC'): $this_**

>
  ```php
    /**
     * Adds fields to order by.
     *
     * @param string $field Field name
     * @param string $direction Sort direction
     * @return object Self reference
     */
    public function orderBy($field, $direction = 'ASC')
  ```

### **_# model->insert($data): bool_**

>
  ```php
    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @return bool
     */
    public function insert(array $data = [])
  ```

### **_# model->update($data): bool_**

Cette méthode doit être utilisée après une clause where

>
  ```php
    /**
     * Builds an update query.
     *
     * @param array $data Array of keys and values, or string literal
     * @return bool
     */
    public function update($data)
  ```
  
### **_# model->delete($where): bool_**

>
  ```php
    /**
     * Builds a delete query.
     *
     * @param string|int|array $where Where conditions
     * @return bool
     */
    public function delete($where = null)
  ```
  
### **_# model->sql($raw_sql): $this_**

Si **$raw_sql** est null, la méthode retourne une chaine de caractères, retourne **$this** sinon.

>
  ```php
    /**
     * Gets or sets the SQL statement.
     *
     * @param string|array SQL statement
     * @return self|string SQL statement
     */
    public function sql($sql = null)
  ```

### **_# model->save($objet = null, $db_fields = null): bool_**

* _$object_: the object to get data on default: $this
* _$db_array_: database fields ex: ['email', 'username']

>
  ```php
    /**
     * Saves an object to the database.
     *
     * @param object $objet Class instance
     * @param array $db_fields Select database fields to save (insert or update)
     * @return boolean
     */
    public function save($objet = null, array $db_fields = null)
  ```

### **_# model->remove($objet = null): bool_**

* _$object_: the object to get primaryKey data on default: $this

>
  ```php
    /**
     * Removes an object from the database.
     *
     * @param object
     * @return boolean
     */
    public function remove($objet = null)
  ```
  
> **NOTE:** Si tu as déjà utilisé un framework comme **CodeIgniter 4** tu maitriseras vite
> Autres méthodes groupBy, having, limit, offset, distinct, between, select, last, find, min, count, max, avg, one, first et des **méthodes dynamiques** comme firstBy{Field}, lastBy{Field}, findBy{Field} où {Field} est un attribut de la table.
<!-- -->

```php

/**
 * Gets the database connection instance PDO.
 *
 * @return object Database connection
 */
public function getDb()
```

```php
  /**
   * Executes a sql statement.
   *
   * @return object Query results object
   * @throws Exception When database is not defined
   */
  public function execute(array $params = [])
```

```php
  /**
   * Perform a query
   *
   * @param string $select
   * @param array|string $where
   * @param int $limit
   * @param int $offset
   * @return array - of object class
   */
  public function get($select = "*", $where = null, $limit = null, $offset = null)
```

```php
  # Autres Méthodes
  ==================

  /**
   * Get the table name for this ER class.
   * 
   * @access public
   * @return string
   */
  getTable ()

  /**
   * Get the primaryKey
   * 
   * @access public
   * @return string
   */ 
  getPrimaryKey()

  /**
   * Get model property fields by data table.
   *
   * @access public
   * @return array of available columns
   */
  getFields()

  /**
   * Create new data row.
   *
   * @access public
   * @param array $properties
   * @return object Model instance
   * @return bool
   */
  create(Array $properties)

  /**
   * Find one model in the database.
   * or create if not exists.
   *
   * @access public
   * @param array $properties
   * @return object Model instance
   * @return array|bool if error occured
   */
  findOrCreate(Array $properties = null)

  /**
   * Find all model in the database.
   *
   * @access public
   * @param mixed $where
   * @return array|object
   */
  public static function all($where = null)
```

## Exemple de création de Controller

```php
<?php

namespace App\Controller;
use App\Model\Contact;

class Home extends BaseController{

  public function index(){
    // Retrouve la méthod utilisée | HTTP Verb
    if($this->request->method() === 'post'){

      // retrouve un fichier | retrouve uploaded file
      $image = $this->request->file('image');
      // save
      $image->save();
      // get the name of the moved file 
      echo $image->name;
    }

    // Using model Contact
    // all() est la seule méthode statique | all() is the only static method
    $contacts = Contact::all();
    $contacts = (new Contact())->get());
    
    // Afficher la vue | display the vue
    return $this->view("accueil", [
        "titre" => "Accueil"
    ]);
  }

}
  
```

## **Credits Libraries**
**Paginator** [iranianpep/paginator](https://github/iranianpep/paginator)
**Form Validator** [pdscopes/php-form-validator](https://github.com/pdscopes/php-form-validator)
**PHP Arrays** [pdscopes/php-arrays](https://github.com/pdscopes/php-arrays)

## **License**

### __BSD 3-Clause License__

Copyright (c) 2021, Faustfizz <youssoufmbae2@gmail.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its
   contributors may be used to endorse or promote products derived from
   this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
