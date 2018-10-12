## How to install:
- Copy the `packages` folder to root of laravel project.
- Add this code to your composer.js `"CaffeineInteractive\\Remzy\\": "packages/caffeineinteractive/remzy/src/"` autoload > inside psr-4.
```
"autoload": {
    ...
    "psr-4": {
        ...
        "CaffeineInteractive\\Remzy\\": "packages/caffeineinteractive/remzy/src/"
    }
},
```
- Add the provider `CaffeineInteractive\Remzy\CIRemzyServiceProvider::class` to providers array of config > app.php file

```
providers' => [
...
CaffeineInteractive\Remzy\CIRemzyServiceProvider::class,
]
```