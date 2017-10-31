# List of common mistakes that people make/to check out for

## Use ViewModels when passing more than three parameters to the view.

Prefer ViewModels whenever possible, but especially when passing more than three variables to the view. Tracking all these variables in the view can become a maintenance issue. Using ViewModels makes your code cleaner, less fragile, less error-prone, and easy to maintain.

Bad:
```php
//SomeController.php
public function actionIndex()
{
    $foo = Foo::find()->whereSomeCondition()->all();
    $bar = Bar::find()->whereSomeCondition()->all();
    $baz = Baz::find()->whereSomeCondition()->all();

    return $this->render('index', [
        'foo' => $foo,
        'bar' => $bar,
        'baz' => $baz,
    ]);
}
```

Good:
```php
//SomeController.php
public function actionIndex()
{
    $someViewModel = new SomeViewModel();

    return $this->render('index', [
        'model' => $someViewModel,
    ]);
}

//SomeViewModel.php
class SomeViewModel extends yii\base\Model
{
    /* @var Foo[] */
    private $fooModels;
    /* @var Bar[] */
    private $barModels;
    /* @var Baz[] */
    private $bazModels;

    public function init()
    {
        $this->fooModels = Foo::find()->whereSomeCondition()->all();
        $this->barModels = Bar::find()->whereSomeCondition()->all();
        $this->bazModels = Baz::find()->whereSomeCondition()->all();
    }

    public function getFooModels()
    {
        return $this->fooModels;
    }

    public function getBarModels()
    {
        return $this->barModels;
    }

    public function getBazModels()
    {
        return $this->bazModels;
    }
}

```

## Request/Response objects should only be used in the controller

This rule is being discussed on [Yii2 GitHub](https://github.com/yiisoft/yii2/issues/13922). Currently, request and response objects are globals and can be accessed from anywhere. But this is a violation of encapsulation, only controllers should handle these things. Models should never read from these global objects, their values should always be passed from the controller to the model.

Bad:
```php
//SomeController.php
public function actionIndex()
{
    $foo = new FooModel();
    $foo->loadParams();
    $foo->doStuff();
}

//SomeModel.php
class FooModel extends yii\db\ActiveRecord
{
    public function loadParams()
    {
        $this->load(Yii::$app->request->get());
    }
}
```

Good:
```php
//SomeController.php
public function actionIndex()
{
    $foo = new FooModel();
    $foo->load(Yii::$app->request->get());
    $foo->doStuff();
}
```

## Avoid direct access to $_GET, $_POST, $_REQUEST, $_SERVER, $_SESSION, $_COOKIE
Yii2 has a wrapper for almost every array named on the list. It is clearer to read, less error prone and easier to test.

Bad:
```php
//SomeController.php
public function actionIndex()
{
    $foo = $_GET['foo'];
    $bar = isset($_POST['bar']) ?? 'defaultValue';
    $baz = $_REQUEST['baz'];

    $ip = $_SERVER['REMOTE_ADDR'];
    $cookies = $_COOKIE;
    $sessions = $_SESSION;
}
```

Good:
```php
//SomeController.php
public function actionIndex()
{
    $foo = Yii::$app->request->get('foo');
    $bar = Yii::$app->request->post('bar', 'defaultValue');
    $baz = Yii::$app->request->get('baz', Yii::$app->request->post('baz')); //Is there a better way?

    $ip = Yii::$app->request->getUserIP();
    $c1 = Yii::$app->request->cookies->get('c1');
    $s1 = Yii::$app->session->get('s1');

}
```

## Use ::class instead of writing the FQN in a string
Writing the class as a string makes it tricky when refactoring the class name. You need to rely on your IDE detecting the class name in a string and replacing it accordingly. When using `::class` this makes it much clearer to the IDE when refactoring that this name should also be changed. Also this way you cannot have typos when writing the class FQN.

Bad:
```php
//SomeController.php
public function actionIndex()
{
    $dataProvider = new ArrayDataProvider([
        'modelClass' => '\app\models\FooModel'
    ]);
}
```

Good:
```php
//SomeController.php
public function actionIndex()
{
    $dataProvider = new ArrayDataProvider([
        'modelClass' => \app\models\FooModel::class
    ]);
}

```

You may use either the FQN `\app\models\FooModel`, or import the namespace with `use` declaration and just referencing `FooModel`. Sometimes it is recommended to put the FQN when the package name is important. 

For example:
```php
$dataProvider = new ArrayDataProvider([
    'modelClass' => ActiveRecord::class
]);
```
Is this ActiveRecord `\yii\db\ActiveRecord` or `\yii\mongodb\ActiveRecord`? It is hard to tell until we inspect the `use` declarations. If we know for confidence that we never use `\yii\mongodb\ActiveRecord` we can omit the FQN
