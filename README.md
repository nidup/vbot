Vindinium Bot
=============

## About:

A PHP Vindinium Bot (http://vindinium.org/)

Started with git@github.com:kcampion/vindinium-starter-php.git

## Install

For path-finding we uses a cpp lib based on https://github.com/justinhj/astar-algorithm-cpp

Compile the lib :
    g++ ./lib/astar/findpath.cpp -o ./bin/findpath

## Run with:

    php app:console run:training <key> <turns> <map> [--hxprof]
