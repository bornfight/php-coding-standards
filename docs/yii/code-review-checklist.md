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

## Avoid code that does nothing
If you don't need a piece of code, delete it. If it turns out you actually need it in the future, getting it back from git is [very simple](https://stackoverflow.com/questions/2007662/rollback-to-an-old-git-commit-in-a-public-repo).
Code that does nothing just adds to clutter and confusion. 
* Delete commented code
* Delete unused variables/functions

Bad:
```php
class FooObject extends \yii\base\Object
{
    private $foo;

    public function init() 
    {
        return parent::init();
    }

    public function collectInput()
    {
        $inputCollector = new InputCollector();

        return $inputCollector->collect();
    }

    public function collectInputOld()
    {
        //NO LONGER NEEDED OR USED
        $inputCollector = new OldInputCollector();
        $this->foo = 'This was once used but no longer needed';

        return $inputCollector->collect();
    }
}

```
Better:
```php

class FooObject extends \yii\base\Object
{
    public function collectInput()
    {
        $inputCollector = new InputCollector();

        return $inputCollector->collect();
    }
}
```


## Avoid comments in code. 
Martin Fowler
```
When you feel the need to write a comment, first try to refactor the code so that any comment becomes superfluous.
```

## Be careful when using `ActiveQuery::where()`

When using `where($condition)` be mindful that this overrides any existing where conditions that you might have already defined. Suggested is to use `andWhere($condition)`

Bad:
```php

//SomeController.php
public function actionIndex()
{
    $query = FooModel::find()->where(['foo' => 'bar'])->where(['active' => true]);
}

```
This will only apply the last condition and return ANY active model.

Good:
```php
//SomeController.php
public function actionIndex()
{
    $query = FooModel::find()->andWhere(['foo' => 'bar']->andWhere(['active' => true]);
}
```
This correctly applies both conditions, model must be both active and have foo attribute equal to bar.

## Use ActiveQuery whenever possible. Always ideally
Instead of writing a lot of database login right in to your model, you can extract this logic into a dedicated ActiveQuery class.
Not only does this help separate class concerns, with eases maintance - it also allows for fancy code reusing with condition chaining.

Bad:
```php
//SomeController.php

public function actionIndex()
{
    $query1 = FooModel::find()->where([
        'foo' => 'bar',
        'active' => true,
        'userId' => User::find()->where(['fk_id' => 10]),
        ['<>', 'status', 5]
    ]);
}
```

Both examples are bad. The first query is very complex and you will be in trouble if you need to include it in several places, and then change them all at once. It may seem convenient now, but you will have bugs later on

Query2 is bad because you have no idea what is going on until you inspect and read `getComplex` in it's entirety. You are wasting time reading code that you maybe do not need to understand.

Much better is:
```php
class SomeController
{
    public function actionIndex()
    {
        $query = FooModel::foo('bar')->active()->userIdIn(10)->statusIsNot(5);
    }
}

class FooObject extends \yii\db\ActiveRecord
{
    /**
     * @return FooQuery
     */
    public static function find()
    {
        return new FooQuery(get_called_class());
    }
}

class FooQuery extends \yii\db\ActiveQuery
{
    public function foo($value)
    {
        return $this->andWhere([
            'foo' => $value,
        ]);
    }

    public function active($value = true)
    {
        return $this->andWhere([
            'active' => $value,
        ]);
    }

    public function userIdIn($userId)
    {
        return $this->andWhere([
            'userId' => User::find()->where(['fk_id' => $userId]),
        ]);
    }

    public function statusIsNot($value)
    {
        return $this->andWhere(['<>', 'status', $value]);
    }
}
```
