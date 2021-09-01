# SilverStripe Pests & Weeds system integration

Enables the display of Pests and Weeds from https://pw.gurudigital.nz/ 

## Installation:

1. Add repositories section to composer.json
```json
    "repositories": [
        {
			"type": "vcs",
        	"url": "git@github.com:guru-digital/gdm-pestandweed-ssmodule.git"
		}
    ],
```
2. Add to require section of composer.json
```json
    "require": {
        "php": "^7.1 || ^8",
        "silverstripe/recipe-plugin": "^1.2",
        "silverstripe/recipe-cms": "4.8.0@stable",
        ... etc ...
        "gdmedia/pestsandweeds": "1.x-dev"
    },
```
## Configuration:
```yml
gdmedia\pestsandweeds\PestHub:
  organisationid: [Your Organisation ID]
  apikey: [Your API Key]
```
