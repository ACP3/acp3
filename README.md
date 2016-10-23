[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ACP3/cms/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/ACP3/cms/?branch=develop)
[![Code Climate](https://codeclimate.com/github/ACP3/cms/badges/gpa.svg)](https://codeclimate.com/github/ACP3/cms)
[![Coverage Status](https://coveralls.io/repos/github/ACP3/cms/badge.svg?branch=develop)](https://coveralls.io/github/ACP3/cms?branch=develop)
[![Build Status](https://travis-ci.org/ACP3/cms.svg)](https://travis-ci.org/ACP3/cms)
[![Dependency Status](https://www.versioneye.com/user/projects/57f64a469907da003a1a64d1/badge.svg?style=flat)](https://www.versioneye.com/user/projects/57f64a469907da003a1a64d1)

# ACP3 CMS
The ACP3 CMS is a highly customizable and easy to use Web Content Management System based on PHP and MySQL. 

## Features

* Based on modular components
* Low barriers for disabled people
* Automatic generation of breadcrumbs to improve usability even further
* Secure: protection against SQL-injections, salted passwords, complete input validation...
* Wordlike text input with the WYSIWYG-Editors CKEditor and TinyMCE
* Easy to customize: Layout based on html templates, which can be styled with CSS
* Search engine optimized URIs
* Access Control Lists, which allow fine grained permissions 

If you want to find out more information about the features and the requirements, just go to the official [Project-Website](http://www.acp3-cms.net).

## Installation

To install the current development version directly from github, you have to do the following steps:

Clone the repository into a new directory:
```sh
$ git clone https://github.com/ACP3/cms.git <directory>
```

Make sure that you have composer already installed.

If so, execute the following command from the projects root directory:

```sh
$ composer install
```

Make sure that you have installed node.js with npm and gulp-cli globally.

If so, execute the following command from the projects root directory, to install the necessary frontend development dependencies:
```sh
$ npm install
```
