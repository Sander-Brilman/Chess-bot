# Chess bot

#### To play [click here](http://schaken.sanderbrilman.nl/versions/en/)

I made this project for fun wondering if i could make a chess bot from scratch not knowing a damn thing about how chess bots work.<br>

To view the code and see how it works the `assets/php/functions.php` file is the place to be. Almost all chess bot code is written & divided in functions.

## What i used

This is a self made chess bot from scratch. I did not use any chess libraries or follow tutorials to make this. 
<br>
The languages i used are:
- PHP (for backend & move processing)
- Jquery (for live updating)
- HTML & CSS (for building the website itself)

I used my self made [php website template](https://github.com/Sander-Brilman/php-website-template) to take care of the basics of the websites


For the icons i used FontAwsome

## How to setup.

To run this project use a webserver or a localhost like XAMMP.<br>
Once you have done that you need to change the `config.php` file.

inside the `config.php` file there is a variable called `$site_folder` on line 2. Set this value to the folder you setup the project in.<br>

For example if you have a localhost setup and the url is `localhost/chess-bot-website/` set the variable to `/chess-bot-website/`.<br>
```php
$site_folder = '/chess-bot-website/';
```


## Short history

I stared with a simple (and very slow) vanilla javascript bot in a basic html page. With every new version it got better and faster until it grew to this current version.<br>

This latest version is the first to process the moves on the backend and live update the changes.<br>




All the previous verison use pure vanilla js only. To view the code you can use the dev tools.
<br>

You can view the previous versions [here](http://schaken.sanderbrilman.nl/index.php?version-select=true) (dutch only sorry)

## **Code design**

I designed the bot in a special way where its easy to adjust the way the bot rates states of the board. This can be found in the `board_score` function.