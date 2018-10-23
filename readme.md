# Trading bot - Dbot
PHP trading bot framework

## Requirements
Dbot requires PHP version 7.2 or greater, mysql server also required for data storage. 
This project includes automatic trading with basic strategies. All testing and coding was done on Mac OSX so this will work on other linux system but I will
never bother to do this for Windows. For Windows users you are on your own.

### Befor you start
This project needs php extension trader, in version 7.2 it is automatically included while in older versions it need's to be enabled or added.
Create one folder and in that folder you can install it by the following commands.
Before you run the migration and seeder setup your .env file.

### Set up commands
```
1. git clone https://github.com/deakzsolt/dbot.git
2. composer update
3. php artisan migrate
4. php artisan db:seed
5. echo "* * * * * `which php` `pwd`/artisan schedule:run >> /dev/null 2>&1" | /usr/bin/crontab
``` 