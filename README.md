# Project 7 : BileMo (Application developer - PHP / Symfony - OpenClassrooms)

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7454bdb351e2486c8f2512fd4e00417d)](https://www.codacy.com/gh/ashk74/P7_bilemo/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ashk74/P7_bilemo&amp;utm_campaign=Badge_Grade)

## Context
-   Create a web service exposing an API for BileMo.
-   BileMo is a company offering a wide selection of high-end cell phones
-   Sell exclusively in B2B
-   Provide access to the catalog via an API to all platforms that want it

## Technical Requirements

### Framework & Bundles
-   Framework : Symfony 5.4.7
-   willdurand/hateoas-bundle : Generate hypermedia links to create a self-discovering API
-   lexik/jwt-authentication-bundle : Create stateless authentication with a token
-    nelmio/api-doc-bundle : Create API documentation with annotations
### Web Server
-   PHP 7.2.5 or higher
-   PHP extensions : Ctype, iconv, JSON, PCRE, Session, SimpleXML and Tokenizer
-   SQL DBMS
-   Versions used in this project
    -   Apache 2.4.46
    -   MySQL 5.7.34
    -   PHP 8.1.4

### Composer
-   [How to install Composer ?](https://getcomposer.org/download/)

## Installation
### 1.  Download or clone the project
-   Download zip files or clone the project repository with github - [GitHub documentation](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/cloning-a-repository)

### 2.  Edit .env file
```yaml
# SQL DBMS
DATABASE_URL="mysql://username:password@host:port/dbname"
```

### 3.  Set your PHP version
-   List and select PHP version (minimum 7.2.5)
```bash
symfony local:php:list
```
-   Set your PHP version
```
echo 8.1.4 > .php-version
```

### 4.  Install packages needed
#### Run your terminal at root project
```bash
composer install
```

### 5.  Create database and tables
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6.  Load fixtures with pictures and avatar
```bash
php bin/console doctrine:fixtures:load
```

### 7. Generate SSL keys for JWT Token
```bash
php bin/console lexik:jwt:generate-keypair
```

### 8. Test
-   Go to [https://127.0.0.1:8000/api/doc](https://127.0.0.1:8000/api/doc) and follow the instructions

### Great ! You are ready to use BileMo API :)
