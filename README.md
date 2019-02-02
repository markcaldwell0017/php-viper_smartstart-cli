# php-viper_smartstart-cli
Viper Smartstart command line interface, written in php, utilizing functions written by Brandon Fiquett.

Command line control of Viper Smartstart.
See Viper_SmartStart_Control for more information about the original code.  None of Fiquett's functions have been changed.

Execute the command line with a question mark to obtain a list of all the possible actions:
```
php Viper-cli.php <username> <password> <vehicle#> <?> 
```
Execute the command line with an action to send the action to the vehicle#:
```
php Viper-cli.php <username> <password> <vehicle#> <action> 
```
For example, the following command line will attempt to 'arm' vehicle 0:
```
php Viper-cli.php johnsmith@gmail.com password123 0 arm 

Running from CLI

Requesting Session ID...
Session ID: abcdef1234abcdef1234abcdef
Getting Vechicle List...
4 vehicle(s) available. 
Vehicle ID: 987654 
Action: arm 
Timeout Error.  Attempt: 1
Timeout Error.  Attempt: 2
Command received successfully.
```
This program will login once and then try a maximum of 5 times to execute the action, checking each time for a successful response code '0' and then terminating.

This program utilizes the website:  'colt.calamp-ts.com'   You can sign in to this website and check the status of your vehicle(s).  If you have 1 vehicle, then it is likely vehicle# '0'.  If you have more than one vehicle, then you can send an 'arm' command to each vehicle '0', '1', '2', etc. and check the website to see which vehicle#'s correspond to each 'arm' command.

Disclaimer
----------
I'm not affiliated with Viper Smartstart in any way. They don't endorse this application. They own all the rights to Viper Smartstart (and all associated trademarks). 

This software is provided as-is with no warranty whatsoever. 

Viper Smartstart and colt.calamp-ts.com could do things to block this kind of behavior if they want, hopefully they do not. Also, if you cause problems (by sending lots of trash to the Viper Smartstart or colt.calamp-ts.com servers or anything), you're on your own.

Users must exercise the same caution when controlling their vehicles using this program as they would when using the Viper application on their mobile device or when using their keyfob.
