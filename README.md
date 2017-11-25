# Posts'n'Pages for Contao 4

[![Version](https://img.shields.io/packagist/v/agoat/contao-postsnpages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-postsnpages)
[![License](https://img.shields.io/packagist/l/agoat/contao-postsnpages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-postsnpages)
[![Downloads](https://img.shields.io/packagist/dt/agoat/contao-postsnpages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-postsnpages) 

## About
An alternative structure to manage content in contao - similar to Wordpress but with a more comprehensive approach. It's based on the idea that a website basically contains 3 types of content blocks:

#### Post content
Content that refers to an article or topic (which is typical for blogs), which can be called under a separate url, but which are also listed in an overview within a page (usually as a teaser).

#### Page content
Content that refers to a single page and only available on this page (like team pages, contact pages).

#### Static content
Content that are integrated as modules in the page layout and which can be seen on several or all pages (like footers with copyright).


## Notice
If installed into an existing project, all page articles will disappear (they still exists in the database). A automatic migration of   the page articles to the new page containers will be implemented in a future version.

## Install
### Contao manager
Search for the package and install it
```bash
agoat/contao-postsnpages
```

### Managed edition
Add the package
```bash
# Using the composer
composer require agoat/contao-postsnpages
```
Registration and configuration is done by the manager-plugin automatically.

### Standard edition
Add the package
```bash
# Using the composer
composer require agoat/contao-postsnpages
```
Register the bundle in the AppKernel
```php
# app/AppKernel.php
class AppKernel
{
    // ...
    public function registerBundles()
    {
        $bundles = [
            // ...
            // after Contao\CoreBundle\ContaoCoreBundle
            new Agoat\PostsnPagesBundle\AgoatPostsnPagesBundle(),
        ];
    }
}
```
