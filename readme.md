# DataTables

DataTables implementation of datagird for [Nette Framework](http://nette.org/)

## Instalation

The best way to install ipub/data-tables is using  [Composer](http://getcomposer.org/):


```json
{
	"require": {
		"ipub/data-tables": "dev-master"
	}
}
```

or


```sh
$ composer require ipub/data-tables:@dev
```

After that you have to register extension in config.neon.

```neon
extensions:
	data-tables: IPub\DataTables\DI\DataTablesExtension
```