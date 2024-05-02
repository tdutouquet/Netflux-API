# Netflux (back/api) documentation

## Setup

To launch the SYmfony server, run the following command:

```
symfony server:start
```

## Fixtures

This project contains fixtures, which allow you to kickstart the database for several entities:
* Users ;
* Movies ;
* Categories ;

To inject fixtures into the database :

```
symfony console make:migration
```
```
symfony console d:m:m
```
```
symfony console d:f:l
```

## Admin access

Once the fixtures have been loaded, an administrator account is created with the following login and password:
* Login : `admin@localhost.com`
* Mot de passe `admin`

## API endpoints

### Base entry point

http://localhost:8000/api/

### Authentication

* Register (public) - POST: http://localhost:8000/api/register
* Login (public) - POST: http://localhost:8000/api/login

### Movies

* Retrieve all movies (public) - GET: http://localhost:8000/api/movies
* Retrieve a specific movie (public) - GET: http://localhost:8000/api/movies/id
* Add a movie (admin) - POST: http://localhost:8000/api/movies
* Edit a movie (admin) - PUT: http://localhost:8000/api/movies/id
* Delete a movie (admin) - DELETE: http://localhost:8000/api/movies/id

### Categories

* Retrieve all categories (public) - GET: http://localhost:8000/api/categories
* Retrieve a specific category (public) - GET: http://localhost:8000/api/categories/id
* Add a category (admin) - POST: http://localhost:8000/api/categories
* Edit a category (admin) - PUT: http://localhost:8000/api/categories/id
* Delete a category (admin) - DELETE: http://localhost:8000/api/categories/id
