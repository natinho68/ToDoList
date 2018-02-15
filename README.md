ToDoList [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8feade4b-1726-4966-a0ef-c0e3303920ea/mini.png)](https://insight.sensiolabs.com/projects/8feade4b-1726-4966-a0ef-c0e3303920ea)
========

Improve an existing Symfony project

* Anomaly corrections
* Implementation of new features
* Implementation of automated tests
* Create the technical documentation
* Create a quality audit of the code & performance of the application

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

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

## Built With

* [Composer](https://getcomposer.org/) - Used for dependency manager
* [Doctrine](https://github.com/doctrine/doctrine2) - Used for Object Relational Mapper
* [Twig](https://twig.sensiolabs.org/) - Used for template engine
* [Bootstrap](https://getbootstrap.com/) - Used for design and responsive
* [PHPUnit](https://phpunit.de/) - Used for unit and functional tests

## Authors

[**Nathan MEYER**](https://github.com/natinho68)

See also [ismail1432](https://github.com/ismail1432) on whom I can rely on a lot on this project.
