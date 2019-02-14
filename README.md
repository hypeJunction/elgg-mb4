Multibyte Support
=================
![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3.x-orange.svg?style=flat-square)

**BACKUP YOUR DATABASE BEFORE DOING ANYTHING WITH THIS PLUGIN**

**FOR EXPERIENCED DEVELOPERS ONLY**

## Features
 
 * Alters database table encoding to utf8mb4 to support emojis


## Usage

After enabling the plugin, run the Upgrade from Admin dashboard.

In `composer.json` of your project root, add:

```json
{
    "autoload": {
        "psr-4": {
            "Elgg\\": "mod/elgg-mb4/classes/Elgg/"
        }
    }
}
```

You can also use the autoload option to specify a path to a different version of the Database class that corresponds to your Elgg version.

## Important

 This plugin hijacks the Elgg Database class to override the hardcoded encoding - very unconventional approach, but the encoding is hardcoded in core files.
 Therefore, you must make sure the Database class in this plugin corresponds to the core Database class in your Elgg version.
 
 **If you don't know what this means and entails, don't use this plugin**