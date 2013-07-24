DerpyCMS
=========

A derped CMS built on Slim, now with 2% more muffins and 39% less fat!

Goals for this project
------

The primary goal is to have a RESTful Content Management Framework (RCMF, yay for catchy acronyms).

To achieve this, we use Slim as the base framework, which already does RESTful routing and has a support for rendering views. We expand on the wonderful routing system by adding every page in the CMF as an individual route, allowing to have a separate page for GET and POST requests with ease.

Full and partial content caching will be implemented Soon™, allowing to move load as much as possible from the database to the HTTP server.

As well as having page caching, parts of pages or "Blobs" can be loaded either from database or from the file system, allowing you to implement a partial caching scheme with static header/footer and dynamic content (hopefully...)

About the developer
------
I am a 22 year old student, studying at Metropolia University of Applied Sciences for a Bachelor's Degree in Information Technology. This project is mainly done as a hobby and arose from the need to have a flexible yet lightweight CMS with extendability beyond what other CMS-projects have out there.

My experience in PHP is mainly self-taught, which might show as some stupid design decisions and being overly perfectionist in things that aren't really that important.

This project is also my first touch using Composer and the finished version will support Composer to the fullest extent possible (hopefully).

Derp!
------
![derpy]

[derpy]: https://static.derpy.me/img/derpy.png "Derpy!"
