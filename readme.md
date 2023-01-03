...Working Progress...

## Installation
Download project with command line :
```
git clone https://github.com/sebcamacho/ReservationAPI.git
```
1) After download write command line to install dependencies :
```
composer install
```
2) Change DATABASE_URL line in .env file to connect your database.

3) Create database with command line :
```
symfony console doctrine:database:create
```
4) Update database with :
```
symfony console doctrine:schema:update --force
```
5)Add fixtures with :
```
symfony console doctrine:fixtures:load 
```
6) Test it with insomnia or postman.

7) Thank you.


