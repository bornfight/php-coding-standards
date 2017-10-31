# List of common mistakes that people make/to check out for

## Use ViewModels when passing more than three parameters to the view.

Prefer ViewModels whenever possible, but especially when passing more than three variables to the view. Tracking all these variables in the view can become a maintenance issue. Using ViewModels makes your code cleaner, less fragile, less error-prone, and easy to maintain.

Bad:
```php
//SomeController.php
public function actionIndex() {
    $foo = Foo::find()->whereSomeCondition()->all();
    $bar = Bar::find()->whereSomeCondition()->all();
    $baz = Baz::find()->whereSomeCondition()->all();
    return $this->render('index', [
      'foo' => $foo,
      'bar' => $bar,
      'baz' => $baz
    ];
}
```

Good:
```php
//SomeController.php
public function actionIndex() {
    $someViewModel = new SomeViewModel();
    return $this->render('index', [
        'model' => $someViewModel
    ]
}

//SomeViewModel.php
class SomeViewModel extends yii\base\Model {
    /* @var Foo[] */
    private $fooModels;
    /* @var Bar[] */
    private $barModels;
    /* @var Baz[] */
    private $bazModel;

    public function init() {
        $this->fooModels = Foo::find()->whereSomeCondition()->all();
        $this->barModels = Bar::find()->whereSomeCondition()->all();
        $this->bazModels = Baz::find()->whereSomeCondition()->all();   
    }

    public function getFooModels() {
        return $this->fooModel;
    }

    public function getBarModels() {
        return $this->barModels;
    }

    public function getBazModels() {
        return $this->bazModels;
    }
}
```
