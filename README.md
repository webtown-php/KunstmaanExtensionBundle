# WebtownKunstmaanExtensionBundle

This bundle add new feature to KunstmaanCMSBundle:

- Auto cache purge + cache purge page (Settings --> Cache purge)
- JMSTranslateBundle integration (with a fork!)
- Custom page parts: Gallery, InsertPage, Slider
- Make searchable entity from custom entity

## Install

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require webtown/kunstmaan-extension-bundle 
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

``` php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Webtown\KunstmaanExtensionBundle\WebtownKunstmaanExtensionBundle(),
        );

        // ...
    }

    // ...
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

