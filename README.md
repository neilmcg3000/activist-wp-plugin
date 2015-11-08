Activist.js - The Wordpress Plugin
==================================

A Wordpress plugin for adding [Activist.js](https://activistjs.com) to your
site.

Installation follows the same method as any plugin, just get this folder into
the ```wp_content/plugins``` folder of your blog.  The version in this git
repository will be the most up to date, but updates of the actual activist.js
script will occur even if the wordpress plugin is an earlier version.

Configuration
-------------

The main Configuration choice you'll need to make when using Activist.js is what
parts of your site you want make available to users when they cannot access your
blog directly. By default, activist saves only a list of error messages to let
visitors know why your site is unavailable.

Three modes of operation are supported:

* A single error page is available to users.
* A manually chosen set of sensitive content is available to users.
* The full blog is available to users.
