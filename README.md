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

### Coding Suggestion

1. SOLID：https://en.wikipedia.org/wiki/SOLID
2. Design Pattern: https://ithelp.ithome.com.tw/articles/10201706

以上只是建議，不是強行要求，比如說Prince Wong傾向：
1. 單一責任原則在function多過class
2. fat controller skinny model，除非多過一個地方會用到同一功能

### TOdo

#### stage 1 todo

- Admission test add is free field
- Add stripe payment gateway to candidate store when user have no unused quota
- Add admin refund for admin admission test orders
- add reschedule charges create product
- add reschedule charges create product price
- add reschedule charges
- Add other costs table and add relation to admission tests
- Add admin store proctor and candidates function to admin user show
- Add proctor and candidate admission test list to admin user show
- Add transportation_cost column to admission_tests_has_proctor table and add claim function to user profile
- add transportation cost paid button to admin admission test show page
- add transportation cost received button to user profile
- Add admin store proctor and candidates to admin user show
- admin user add admission test orders list and create order button
- Add assign roles function to admin user show
- Admin team show add show team members and relation role 
- Add third party iq test accept list store
- Add third party iq test accept list index
- Add third party iq test accept list update name
- Add third party iq test accept list update active status
- Add third party iq test request products create
- Add third party iq test request products index
- Add third party iq test request products edit
- Add third party iq test request products price store
- Add third party iq test request products price update
- Add third party iq test request orders create
- Add third party iq test request orders index
- Add third party iq test request orders show
- Add third party iq test request orders update payment status
- Add stripe payment gateway for third party iq test request on user show
- Add admin refund for third party iq test request orders
- Add transfer in and out store and show on admin user show
- Add transfer in request on user show
- Add membership products create
- Add membership products index
- Add membership products edit
- Add membership products price store
- Add membership products price update
- Add membership orders create
- Add membership orders index
- Add membership orders show
- Add membership orders update payment status
- Add stripe payment gateway for inactive member and add show all membership orders on user show
- Add admin refund for membership orders
- Add subscription headless web hock
- add user public option on user show
- Add member index
- Add member show
- Add edit forwarding email function

#### stage 2 todo

- subscriptable channel store
- subscriptable channel index
- subscriptable channel update
- subscriptable channel delete
- Add subscript channel to user show
- Add admin news create
- Add admin news index
- Add admin news edit
- Add admin news delete
- Add new index
- Add new show

#### stage 3 todo

- Add admin event create
- Add admin event index
- Add admin event show
- Add admin event update
- Add admin event cancel
- Add admin event products store
- Add admin event products update
- Add admin event order create and list on admin event show
- Add admin event order update payment status
- Add admin refund for event orders
- Add stripe payment gateway for event orders
- Add event index
- Add event create
- Add event show
