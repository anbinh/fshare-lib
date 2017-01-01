# fshare-lib
This is a PHP library for manipulating downloads at fshare.vn - the biggest file sharing service in Vietnam. The idea of this project is to create a library for developers to work with downloads at fshare.vn. So that we don't need a desktop to download files from fshare.vn, what we need is any headless computer with PHP supported.

Fshare.vn doesn't officially provide any APIs so this tool only relies on parsing HTML. We need to keep this up to date with UI changes there.

# Installation
`composer require ndthuan/fshare-lib` to include this library in your project. No idea what composer is? Check this out: https://getcomposer.org/.

# Usage
Please check functional tests at https://github.com/ndthuan/fshare-lib/tree/master/tests/functional for an idea on how to use it.

# Guzzle Configs
You might want to read more about https://github.com/guzzle/guzzle to adjust its configurations per your needs, eg. change default HTTP user-agent, configure a cookie file...
