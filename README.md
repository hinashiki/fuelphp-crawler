fuelphp-crawler
===============
## How to install
### From oil
get original fuel/app/config/package.php
```
wget -O "fuel/app/config/package.php" 'https://gist.githubusercontent.com/hinashiki/6d89170f681ad5b1877f/raw/90f4a6b4f885b5ebb1cecb2ccbace5ac61a4a30f/fuel__app__config__package.php'
```
or make it as above.
```
return array(
	"sources" => array(
		"github.com/hinashiki"
	),
);
```
after setting it, exec oil command, and update composer
```
php oil package install fuelphp-crawler
php composer.phar -d=fuel/package/fuelphp-crawler update
```

### From composer
edit composer.json and add above lines.
```
{
	"repositories": [
		{
			"type": "package",
			"package": {
				"name": "hinashiki/fuelphp-crawler",
				"type": "fuel-package",
				"version": "dev-master",
				"dist": {
					"url": "https://github.com/hinashiki/fuelphp-crawler/archive/master.zip",
					"type": "zip"
				},
				"source": {
					"url": "https://github.com/hinashiki/fuelphp-crawler",
					"type": "git",
					"reference": "master"
				}
			}
		}
	],
	"require": {
		"hinashiki/fuelphp-crawler": "dev-master"
	}
}
```
after edit json, exec update composer.
```
php composer.phar update
php composer.phar -d=fuel/package/fuelphp-crawler update
```
## How to use
write it later...
