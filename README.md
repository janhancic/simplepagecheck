# SimplePageCheck

## What is it?
A simple PHP class that you can use to check web pages if they contain certain strings.

## Why?
I often build a "debug" sub-page on my projects that list various things about the state of the web page (memcache keys, memory usage, ...). And it got boring to check the page every now and then by hand to see if everything is OK. So I've built this class that does the work for me (I just put it into a cron job). One of my usage examples is: I output some contents of a memcache key on my debug script, if one string is not present something is wrong, and this is where this class comes in.

## How to use it?
See "example.php" for usage. It's really simple.