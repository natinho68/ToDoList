ToDoList [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8feade4b-1726-4966-a0ef-c0e3303920ea/mini.png)](https://insight.sensiolabs.com/projects/8feade4b-1726-4966-a0ef-c0e3303920ea) [![Build Status](https://scrutinizer-ci.com/g/natinho68/ToDoList/badges/build.png?b=master)](https://scrutinizer-ci.com/g/natinho68/ToDoList/build-status/master) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/3b350108ddda487ea2aafa0e46abaa4f)](https://www.codacy.com/app/natinho68/ToDoList?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=natinho68/ToDoList&amp;utm_campaign=Badge_Grade)
========

Improve an existing Symfony project

* Anomaly corrections
* Implementation of new features
* Implementation of automated tests
* Create the technical documentation
* Create a quality audit of the code & performance of the application

[More details of the project](https://openclassrooms.com/projects/ameliorer-un-projet-existant-1)

## Getting Started


### Prerequisites

* Local server environment or live server
* PHP v7.0
* MySQL v5.0 or higher


### Installing

* [Clone or download the repository](https://github.com/natinho68/ToDoList.git) and put files into your environment


* Install all the project dependencies with ``` composer install ```

* Modify the database parameters if you need to in **app/config/parameters.yml**

```php
parameters:
    database_host: your_host
    database_port: your_port
    database_name: your_database_name
    database_user: your_database_username
    database_password: your_database_password
```
* Install the database structure and datas with ``` php bin/console app:load-datas ```
* Enjoy


## Testing

* Install the test database with ``` php bin/console doctrine:database:create --env=test ```
* Run the automatic tests with ``` vendor/bin/phpunit ```
* If you want to see the HTML code coverage go in **web/test-coverage**


## How to contribute

1. Clone the project on github, on master branch
2. Create a branch and make your contributions 
3. Write your tests and **make sure they pass !**
4. Create a pull request
5. After checking, the pull request will be merged on the project



## Built With

* [Composer](https://getcomposer.org/) - Used for dependency manager
* [Doctrine](https://github.com/doctrine/doctrine2) - Used for Object Relational Mapper
* [Twig](https://twig.sensiolabs.org/) - Used for template engine
* [Bootstrap](https://getbootstrap.com/) - Used for design and responsive
* [PHPUnit](https://phpunit.de/) - Used for unit and functional tests
* [Blackfire](https://blackfire.io/) - Used as code performance tool
* [SensioLabsInsight](https://insight.sensiolabs.com/) - Used as Symfony code quality tool
* [Codacy](https://app.codacy.com/) - Used as PHP code quality tool
* [Scrutinizer](https://scrutinizer-ci.com) - Used as PHP code quality tool

## Authors

[**Nathan MEYER**](https://github.com/natinho68)

See also [ismail1432](https://github.com/ismail1432) on whom I can rely on a lot on this project.
