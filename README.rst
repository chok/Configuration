Configuration
=============

Configuration is a generic class for any kind of configuration

Installation
============

via Composer
------------

The recommended way to install Configuration is through `Composer <http://getcomposer.org>`_.

1. Add ``harchibald/configuration`` as a dependency in your project's ``composer.json`` file:

```yaml
        {
            "require": {
                "harchibald/configuration": "*"
            }
        }
```

Consider tightening your dependencies to a known version when deploying mission critical applications (e.g. ``2.7.*``).

2. Download and install Composer:

```bash
$ curl -s http://getcomposer.org/installer | php
```

3. Install your dependencies:

```bash
$ php composer.phar install
```

4. Require Composer's autoloader

Composer also prepares an autoload file that's capable of autoloading all of the classes in any of the libraries that it downloads. To use it, just add the following line to your code's bootstrap process:

```php
require 'vendor/autoload.php';
```

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).

Use
===

$parameters = array(
  'foo' => array(
    'bar' => array(
      'baz' => 'Yeah !'
     )
  ),
  'bar' => array(
    'foo' => array(
      'bar',
      'baz'
    )
  ),
  'baz' => 'foo'
);

$configuration = new Configuration($parameters);

$configuration->prefix('foo/bar');
$configuration->set('bar/baz/baz', 'test');
$configuration->get('baz'); // Yeah !

$configuration->addToPrefix('baz');
$configuration->all(); // Yeah !
              
$configuration->removeFromPrefix('bar/baz');
$configuration->get('bar');

$configuration->addToPrefix('bar');
$configuration->get('baz');

$configuration->resetPrefix();

$configuration->get('bar/foo');

Methods
-------

  public function __construct(array $parameters = array())
  
  public function box($box)  
  public function endBox($all = false)
  public function inBoxMode()
  
  public function getPrefix()
  public function prefix($prefix)
  public function resetPrefix()
  public function addToPrefix($path)
  public function removeFromPrefix($path)
  
  public function has($path)
  public function set($path, $value)
  public function get($path, $default = null)
  public function merge(array $parameters, $path = null)
  public function clear($path)
  public function all()
  public function remove($path)
