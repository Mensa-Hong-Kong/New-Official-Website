### Set-up Environment

Please read the documents folder for Windows or Mac setup instructions.

### Pull the project

### Gen .env

Please read documents folder for ansible

### Install Library

```shell
composer install
npm install
```

### Set-up Local Server

```shell
php artisan serve
```

```shell
npm run dev
```

### Add Super Administrator For Local Actual Test

```shell
php artisan db:seed --class=SuperAdministratorSeeder
```

### As Known Issues

1. nav nested node hyperlink not work

### Database

Please read database/README.md for database setup and details.

### Coding Suggestion

1. SOLID：https://en.wikipedia.org/wiki/SOLID
2. Design Pattern: https://ithelp.ithome.com.tw/articles/10201706

以上只是建議，不是強行要求，比如說Prince Wong傾向：
1. 單一責任原則在function多過class
2. fat controller skinny model，除非多過一個地方會用到同一功能
