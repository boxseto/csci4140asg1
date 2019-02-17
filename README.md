# CSCI 4140 assignment 1 -- web instagram
### Link To Heroku app: [Here](https://csci4140-asg1-seto.herokuapp.com/index.php)

## Files Introduction
* ##### PHP Files
  * __admin.php__
    * Place For admin to confirm system's initialization.
  * __editor.php__
    * Place For hosting the editor, as the "editing stage" after file upload
  * __final.php__
    * Place For users to review final image.
  * __finish.php__
    * Confirm message that marks the end of system initialization for admin.
  * __index.php__
    * Main page. Provides gallery, upload dialogue and access control header.
  * __init.php__
    * Initialization process for admin.
  * __login.php__
    * cointains a form for user-login. 
  * __logout.php__
    * cointains functions to clear all cookies and session used, for logging out purpose.
  * __webprocess.php__
    * Process page. Currently it is only for checking log in information.
* ##### Other Files
  * __composer.json__
    * cointains required modules and corresponding module to use in the app.
  * __composer.lock__
    * auto generated file by composer.
  * __.gitignore__
    * ignore composer-generated files when push
  * __README.md__
    * this file :) provide introduction to this assignment
  * __img/protected/flare.png__
    * folder storing image used in filter.

## Development Procedure
1. Create my very brief draft of the program in my own laptop, using apache and mysql
1. intergrated to heroku app
1. Found Lots of Errors to fix.
1. Found out that Heroku have good support in PostgreSQL, switch all code from mysql to psql.
1. Found out that image cannot be put in the server directory, used AWS S3 to help store image uploaded.
1. Cointinue fix bugs...
1. Find out too much difference between my own server and heroku. decided to open new branch on github.
1. Cointinue fix bugs...
1. Modification of minor changes (e.g. parameter of imagick functions, html output formats)

## Parts deserve Bonus
* No part is deserved. this assignment is very bad and rubbish code.
* If one thing deserves bonus, that will be this readme.

## Parts not Fully completed
* Due to my poor time management, I only finish the very basic requirement.
* Things like security is not implemented, so guest can actually goes initialization by appending 'init.php' in url
* it is very slow because of the implementation on imagick
* UI is extremely ugly
