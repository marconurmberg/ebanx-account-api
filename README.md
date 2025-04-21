# EBANX Technical Test - Bank System

## Technologies Used

- PHP 8.4
- PHPUnit 10.5.45
- CodeIgniter 4.6.0

## About the Solution
The application developed uses the CodeIgniter 4 framework structure. Despite that, the Business Rules Layer is decoupled from the framework and was "inspired" by Clean Architecture principles.

A "persistence mechanism" was implemented through CACHING (File-based caching) to store the app state, which its feature was ready to use through CodeIgniter.
Some design patterns were used:
#### Creational Patterns
- **Factory**: decided to use factory to create the correct use case strategy, with its dependencies, to be used in the Event Context (on it's controller method).
#### Structural Patterns
- **Adapter**: used to adapt the input data structure to the application's interface (to DTO's). Also used to adapt the other way round - application's interface to output (from DTO's).
- **Facade**: to make the "persistence mechanism", a cache service interface was defined on the app's Domain. This interface is used to invert the dependency between the Business Layer and the framework. This way, implementing the interface, a service was created acting as a Facade using framework's cache service. This keeps Business Layer's required cache interface and makes possible to use framework's services without creating a coupled dependency, since an interface was defined.
#### Behavioral Patterns
- **Strategy**: used to better "organize" all types of events and make it easier to execute the Event Context, since the context's method core is the same to any event strategy.

## Project Structure
Clean Architecture "kinda" inspires the folder structure.
- **app**: contains the BusinessLayer core and the framework structure, such as Controllers and Models.
- **app/BusinessLayer:** contains the core the application.
    - **UseCases**: contains all the use cases of the application.
    - **Domain**: contains the domain of the application such as the interfaces and entities.
    - **Infra**: contains the implementation of the application's infrastructure.


### Tests
The unit tests directory was configured only to the BusinessLayer core.


## Setup

#### Install dependencies
`composer install`

Copy `env.example` to `.env`. It's ready to run locally.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

Additionally, make sure that the following extensions are enabled in your PHP:
- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

