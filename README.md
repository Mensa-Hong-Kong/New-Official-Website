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

### Payment cards for Stripe UAT
https://docs.stripe.com/testing


### A.I. Helper

This installed laravel prompts mcp and boost

Please read laravel official documents:

- Laravel Prompts: https://laravel.com/docs/11.x/prompts
- Laravel MCP: https://laravel.com/docs/11.x/mcp#main-content
- Laravel Boost: https://laravel.com/docs/11.x/installation#installing-laravel-boost


### Todo

#### stage 1 permission system, nav, custom web page and admission test

- move the project checklist to github project or jira
- add edit candidate and edit candidate result permission and update admin admission test candidate permission checking
- update candidate store method to support select product and contact stripe
- add stripe checkout web hock handle
- change quota validity months to inside product and order table
- add details, candidate and result module under admission test and change permission from admission test module to details, candidate and result
- Add admin exchange and refund for admin admission test orders
- add reschedule charges create product
- add reschedule charges create product price
- add reschedule charges to reschedule function, user show and candidate create and store middleware 
- Add other costs table and add relation to admission tests for venue rental and nsp
- Add admin store proctor and candidates function to admin user show
- Add proctor and candidate admission test list to admin user show
- Add admin store proctor and candidates to admin user show
- Add transportation_cost column to admission_tests_has_proctor table and add claim function to user profile
- add transportation cost paid button to admin admission test show page
- add transportation cost received button to user profile
- admin user add admission test orders list and create order button
- add from and to column to model_has_team_roles table and overwrite roles relationship method on user model (parent roles where pivot now between from and to columns)
- add team_statuses table and update team found end and back end to support team_statuses
- Add assign roles function to admin user show
- Admin team show page add team member list and relation role
- add notification_templates table, add seeder and add admin notification templates index 
- add admin notification templates edit and update
- change notifications to use templates
- change qr code to from quickchart.io gen image link to use twilio assets
- add user delete function

#### stage 2 third party iq test result

- Add admin third party iq test accept list store
- Add admin third party iq test accept list index
- Add admin third party iq test accept list update name
- Add admin third party iq test accept list update active status
- Add admin third party iq test request products create
- Add admin third party iq test request products index
- Add admin third party iq test request products edit
- Add admin third party iq test request products price store
- Add admin third party iq test request products price update
- Add admin third party iq test request orders create
- Add admin third party iq test request orders index
- Add admin third party iq test request orders show
- Add admin third party iq test request orders update payment status
- Add admin third party iq test request orders details create when succeeded
- Add admin third party iq test request orders details update
- Add admin third party iq test request orders result
- Add stripe payment gateway for third party iq test result request on user show
- Add admin exchange and refund for third party iq test request orders

#### stage 3 membership

- add admin nation mensa index
- add admin nation mensa store
- add admin nation mensa update
- add admin nation mensa update status
- add can_join_admission_test column to passport_types table, update admin edit and update user logic for passport type and update admission test schedule logic
- add transfer member register page
- add admin user in progress transfer member filter
- Add membership products create
- Add membership products index
- Add membership products edit
- Add membership products price store
- Add membership products price update
- Add membership orders create (require user address)
- Add membership orders index
- Add membership orders show
- Add membership orders update payment status
- Add stripe payment gateway for inactive member and add show all membership orders on user show
- Add admin exchange and refund for membership orders
- Add subscription headless web hock
- Add transfer records and create function and show on admin user show
- Add transfer records and transfer in request on user show
- add user public option on user show
- Add member index
- Add member show
- Add upload image to profile when user is member
- Add remove image to profile when user is member
- Add edit forwarding email function
- Add admin forwarding email changing index and approve or reject function

#### stage 4 news

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

#### stage 5 event

- Add admin events create
- Add admin events index
- Add admin events show
- Add admin events update
- Add admin events cancel
- Add admin event costs list and store
- Add admin event costs list and update
- Add admin event products store
- Add admin event products update
- Add admin event order create
- Add admin event order index
- Add admin event order show
- Add admin event order update payment status
- Add event index
- Add event create by stripe payment gateway when with payment
- Add event show
- Add admin exchange and refund for event orders

#### stage 6 online shop

- Add admin shop products create
- Add admin shop products index
- Add admin shop products show
- Add admin shop products update
- Add admin shop product options store
- Add admin shop product options update
- Add admin shop product options price store
- Add admin shop orders create
- Add admin shop orders index
- Add admin shop order store status
- Add admin shop other costs list and store
- Add admin shop other costs list and update
- Add shop index
- Add shop create
- Add shop cart index
- Add shop cart item update
- Add shop cart item delete
- Add admin shop order create by stripe payment gateway
- Add shop order list to user profile
- add shop order show page
- Add admin exchange and refund for shop orders

#### stage 7 analytics (coming soon)

#### stage 8 contest (coming soon)

#### stage 9 CalDAV (coming soon)

#### stage 10 project management (coming soon)

#### unimportant

- takeout Strip Library from app folder to make a standalone composer package on standalone repo. 
