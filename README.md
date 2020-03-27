# aliabc-php
**Application Language Integration ABC** - php library

Packet helps create site translations with high performance.<br>
When you use DataBase as translation source (example Mysql), for translate all phrases packet make only one request.
In html instead translate you get translate id, which you put to you response code. On the page rendering end, ALIAbc replace all their translation ids for the real translations. 

## Installation

```bash
$ composer require ali-translator/translation
```

## Quick start
First of all, you need choose type of translation source, which you will be used.
From the box, in this packet you may use:
* MysqlSource
* CsvSource

For simplification first time using, we created `QuickStartALIAbFactory`, which creates for you instance of `ALIAbc` with general configuration.<br>
`ALIAbc` is facade Class with access for the most popular methods.<br>
<br>
Exist two base types for using this packet:
* with html auto translation. In this cast, you may put to buffer full html text, and ALIAb search and translate all phrases
    * With MySql source 
    ```php
    $aliAbc = (new \ALI\Translation\Helpers\QuickStart\ALIAbFactory())->createALIByHtmlBufferMysqlSource((new PDO('mysql:dbname=test;host=mysql', 'root', 'root')),'en','ua');
    ```
    * With CSV source 
    ```php
    $aliAbc = (new \ALI\Translation\Helpers\QuickStart\ALIAbFactory())->createALIByHtmlBufferCsvSource('/path/to/writable/directory/for/translation','en','ua'));
    ```
* manually adding text for translation in html
    * With MySql source 
    ```php
    $aliAbc = (new \ALI\Translation\Helpers\QuickStart\ALIAbFactory())->createALIByMysqlSource((new PDO('mysql:dbname=test;host=mysql', 'root', 'root')),'en','ua');
    ```
    * With CSV source 
    ```php
    $aliAbc = (new \ALI\Translation\Helpers\QuickStart\ALIAbFactory())->createALIByCsvSource('/path/to/writable/directory/for/translation','en','ua'))
    ```


## Basic Usage

```php
/** @var \ALI\Translation\ALIAbc $aliAbc */
$aliAbc->saveTranslate('Hello', 'Привіт');

// Dirrect translation
echo $aliAbc->translate('Hello');
var_dump($aliAbc->translateAll(['Hello']));

// Translate in html, using buffer, for translation at end, by one request for Source
$html =  '<div>' . $aliAbc->addToBuffer('Hello') . '</div>';
echo $html; // '<div>#ali-buffer-layer-content_0#</div>'
echo $aliAbc->translateBuffer($html); // '<div>Привіт</div>'

// If you choose type with auto html translation, you may put full html code for tanslate
$html =  $aliAbc->addToBuffer('<div>Hello</div>');
echo $aliAbc->translateBuffer($html); // '<div>Привіт</div>'
```
Also you may discover object `$aliAbc->getBufferCaptcher()` for additional methods

#### Templates

Also you may translate templates with parameters:

```php
/** @var ALI\Translation\ALIAbc $aliAbc */
echo $aliAbc->translate('Hello {objectName}!', [
    'objectName' => 'sun',
]);

$content = '<div>'. $aliAbc->addToBuffer('Hello {objectName}!', [
    'objectName' => 'sun',
]) .'</div>';
echo $aliAbc->translateBuffer($content);

```

### Tests
In packet exist docker-compose file, with environment for testing.
```bash
docker-compose up -d
docker-compose exec php bash
composer install
./vendor/bin/phpunit
``` 
