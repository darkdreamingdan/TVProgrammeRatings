# Programme Ratings
This was a project I developed to demonstrate basic backend and frontend web development for a company as part of their assessment process.  It allows a user to rate their favourite TV Shows and Movies, then go view a Scoreboard of how users have rated these programmes.  It also allows Administrators to add to the database TV Shows and Movies, either by uploading in XML format or by adding manually via TMDB.  Please note, this requires a [ TMDB API key](https://www.themoviedb.org/documentation/api).

![Programme Ratings page](https://i.imgur.com/9B9DfK3.png)
[![Scoreboard page](https://i.imgur.com/gKECrMGm.png%20Scoreboard) ](https://i.imgur.com/gKECrMG.png)[![Admin Panel page](https://i.imgur.com/34RkmYAm.png)](https://i.imgur.com/34RkmYA.png)

This website was developed in an Apache Environment and tested with Chrome 46 and Edge 20, with the following requirements:

PHP 5.4.45
MySQL 5.5.46

Any environment with a typical PHP5 and MySQL5 configuration should be compatible.  This has been tested on a "UniServer Zero XI" portable all-in-one web server package on a Windows machine.

# INSTRUCTIONS
1) Unzip the contents of the "public_html" folder in the .zip to your public_html or www directory of your web server
2) Create a new MySQL database with the following server parameters:

 - Address: localhost 
 - User: root 
 - Password: pass 
 - Database 
 - Name: main
 - Port: 3306 
 - NOTE:  These settings can be modified if required on Line 2 of inc/database.php.

3) Depending on OS, read-write-execute permissions are required to the img/ folder (typically chmod 744). 
4) Navigate to the Upload page, and upload one (or all) of the sample XMLs provided - sample.xml, sample_marvel.xml, sample_craigbond.xml

Requires a the movie database API key to function correctly. Please sign up at https://www.themoviedb.org/documentation/api , and add your API key to the following lines

 - public_html/upload.php - Line 6
 - public_html/inc/ajax.php - Line 71

# NOTES
Please note the following logic/features:

* An assumption has been made that customers view and rate content on the "Rate our content" and "Content Leaderboard" pages.  The web page and backend design has been developed with this in mind.
* Administration functionality is completed on the "Upload" page, user authentication was not in the scope of the project
* XML Scheme can be seen in the sample XMLs.  The 'type' attribute and 'name' element are mandatory for each programme.
* User ratings are processed per-IP and are cumulative.
* New programmes can be added by hand from the "Manually add programme" section of the Upload page.
* The leaderboard sorts by number of points, then alphabetically
* Image assets for only two programmes have been provided for proof-of-concept.  The rest are fetched automatically.
* The "libraries" folder contains assets not developed by myself
* Everything else is written by me for this project.  Some utility functions sourced (and modified) from the internet are credited in comments