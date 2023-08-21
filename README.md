# README

[![Release](https://img.shields.io/badge/release-1.9-a2cbe9.svg)](https://git.dotclear.watch/JcDenis/alias/releases)
![Date](https://img.shields.io/badge/date-2023.08.07-c44d58.svg)]
[![Dotclear](https://img.shields.io/badge/dotclear-v2.27-137bbb.svg)](https://fr.dotclear.org/download)
[![Dotaddict](https://img.shields.io/badge/dotaddict-official-9ac123.svg)](https://plugins.dotaddict.org/dc2/details/alias)
[![License](https://img.shields.io/badge/license-GPL--2.0-ececec.svg)](https://git.dotclear.watch/JcDenis/alias/blob/master/LICENSE)

## ABOUT

_alias_ is a plugin for the open-source web publishing software called [Dotclear](https://www.dotclear.org).

> Create aliases of your blog's URLs.

## REQUIREMENTS

* Dotclear 2.27
* PHP 7.4+
* Dotclear admin permission for management

## USAGE

First install _alias_, manualy from a zip package or from 
Dotaddict repository. (See Dotclear's documentation to know how do this)

You can manage your aliases from menu ''Alias'' on admin dashboard sidebar.

## RULES

* Only blog URLs can be redirected.
* It can redirect a specific URL "plop" to another one "post/2023/04/24/my-post".
* It can redirect all URLs that content "plop" using alias "/plop/" by replacing it by destination "post" into requesting URL. (Even if it's not at the begining of the URL!)
* It can not redirect an alias to another alias. 
* It has priority on all ohters URLs handlers, so if you create an alias of an existing page, the destination from plugin _alias_ will be used.

Keep in mind, plugin _alias_ loads all registered aliases to test them on each page load, 
so more there are aliases, more page load is slow.

## LINKS

* [License](https://git.dotclear.watch/JcDenis/alias/blob/master/LICENSE)
* [Packages & details](https://git.dotclear.watch/JcDenis/alias/releases) (or on [Dotaddict](https://plugins.dotaddict.org/dc2/details/alias))
* [Sources & contributions](https://git.dotclear.watch/JcDenis/alias) (or on [GitHub](https://github.com/JcDenis/alias))
* [Issues & security](https://git.dotclear.watch/JcDenis/alias/issues) (or on [GitHub](https://github.com/JcDenis/alias/issues))

## CONTRIBUTORS

* Olivier Meunier (author)
* Franck-paul
* Jean-Christian Denis

You are welcome to contribute to this code.
