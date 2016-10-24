# Tattler-OctoberCMS

**Description:**
This code allows you to send async notifications to users with web-socket. This is version for Laravel-based OctoberCMS.

-------
Adding new js handlers:

    window.tattler.addHandler('mySuperHandler', 'global', function(data){ console.log(data); })

Then from php run `tattler()->message('mySuperHandler', ['anything'=>['else'], [1,2,3]])->say();`

-------
**Installation**

 Install and run Tattler backend: https://github.com/grohman/tattler
 
 Then `git clone https://github.com/grohman/tattler-octobertcms.git plugins/grohman/tattler`
    or
    `git submodule init && git submodule add https://github.com/grohman/tattler-octobertcms plugins/grohman/tattler`

`php artisan october:up`
   
`composer update`

`cp vendor/oktopost/tattler-php/js/tattler.min.js plugins/grohman/tattler/assets/js`

Then go to CMS settings and setup Tattler







