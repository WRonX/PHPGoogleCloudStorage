## PHP Google Cloud Storage

This piece of poorly written code may help you with performing basic operations on Google Cloud Storage files. Or may not. I don't know, I'm a plumber, not a fortune-teller.

#### License:

> Copyright © 2016 github.com/WRonX  
This work is free. You can redistribute it and/or modify it under the terms of the Do What The Fuck You Want To Public License, Version 2, as published by Sam Hocevar. See http://www.wtfpl.net/ for more details.

#### Features:

* downloading file from bucket
* uploading file to bucket
* check if file exists in bucket under specified location
* delete file from bucket
* ...
* PROFIT

#### Installation and configuration:

`php composer.phar require wronx/php-google-cloud-storage "dev-master"`


#### Usage

Method names should be self-explanatory, so just a few words about the rest:

###### Caching Storage Objects

You can safely use it if you are sure nothing comes in your way.  
Say you are checking if file exists (that loads Storage Object to cache), then you want to download it. But meanwhile someone deleted it. If you want to avoid such situations (if there's a possibility it can happen), disable caching.

###### Authorization file

This one should be downloadable from GCloud. It looks like this:

```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "your private key ID",
  "private_key": "-----BEGIN PRIVATE KEY-----\n Your private key goes here \n-----END PRIVATE KEY-----\n",
  "client_email": "something@probably-your-project-id.iam.gserviceaccount.com",
  "client_id": "clientID",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://accounts.google.com/o/oauth2/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/something@probably-your-project-id.iam.gserviceaccount.com"
}
```


#### Summary
Oh, come on, I spent enough time writing readme already...
