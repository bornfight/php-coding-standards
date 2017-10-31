# Browser sync
How to do certain stuff in yii with browser sync.


## Sync assets over 192.xxx.xxx.xxx IP
Set asset manager base path as following to allow debugging on mobiles.
Create a sym link in web folder to allow access to static.

Since browserSync is starting this on local machine and exposing it over wifi, http://www.static.loc is not available.

```
<?php
return [
	'components' => [
		'assetManager' => [
			'baseUrl' => '@web/staticFolder/assets',
		]
	]
];
```