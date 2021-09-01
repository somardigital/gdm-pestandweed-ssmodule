# SilverStripe Pests & Weeds system integration

Enables the display of Pests and Weeds from https://pw.gurudigital.nz/ 

Using a PestHub Page

## Introduction

You must have an account on https://pw.gurudigital.nz/
Contact info@gurudigital.nz to arrange access
After login navigate to - Settings & Users - Web API
to get Organisation ID and API Key 

## Installation

composer require gurudigital/pesthub

## Configuration:
```yml
gurudigital\pesthub\PestHub:
  organisationid: [Your Organisation ID]
  apikey: [Your API Key]
```

## Pest summary data

Get summary data for all pests and weeds 
E.g. for adding urls to search index or sitemap

```php
$url = "[URL of Pest Hub Page]";
$pestHub = new PestHub();
return $pestHub->getPestContent($url);
```

## Errors

Incorrect configuration - Check Configuration for correct path, Organisation ID and apikey

Invalid API Key - Check Configuration for correct Organisation ID and apikey