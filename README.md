# MedelseUtmCookieBundle

The **MedelseUtmCookieBundle** is a Symfony Bundle to save utm parameters from url into cookie when exists. Than cookie (utm) can be used later without parsing google or any other cookies.


Features include:

- Compatible Symfony version 3 & 4


## Installation

### Through Composer:

Install the bundle:

```
$ composer require medelse/utm-cookie-bundle
```

### Register the bundle in app/AppKernel.php (Symfony V3):

``` php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new Medelse\UtmCookieBundle\MedelseUtmCookieBundle(),
    );
}
```

### Register the bundle in app/AppKernel.php (Symfony V4):

``` php
// config/bundles.php

return [
    // ...
    Medelse\UtmCookieBundle\MedelseUtmCookieBundle::class => ['all' => true],
];
```

### Parameters :

``` yml
medelse_utm_cookie:
    name: 'utm' #The Name of cookie (default value "utm")
    lifetime: 604800 #The lifetime of cookie in seconds (default 604800 => 7 days)
    path: '/' #The path on the server in which the cookie will be available on (default '/')
    domain: '' #The (sub)domain that the cookie is available to (default '' so use current domain)
    overwrite: true|false #If overwrite all utm values when even one is set in get (default true)
    secure: true|false #Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client (default false)
    httponly: true|false #When TRUE the cookie will be made accessible only through the HTTP protocol (default false)
    auto_init: true|false #If true, run init and create cookie automatically. If false you have to call init manually (default true)
```

## Usage

### Public service

The service name available is `medelse_utm_cookie.utm_cookie`

### Basic Usage

``` php
$this->get('medelse_utm_cookie.utm_cookie')->init(); // just init - read utm params and cookie and save new values. (optionnal if auto_init config is TRUE or automatically called when call get() method)
$this->get('medelse_utm_cookie.utm_cookie')->get(); // get all utm cookies as array
$this->get('medelse_utm_cookie.utm_cookie')->get('utm_source'); // get utm_source
$this->get('medelse_utm_cookie.utm_cookie')->get('source'); // get utm_source
```



## License

This bundle is under the MIT license.