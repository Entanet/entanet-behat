# **Entanet Behat**
This package is designed specifically for Entanet's Software QA department to aid with automation of features.

### Installing

Require the entanet behat package
```
composer require entanet/entanet-behat --dev
```

Add the following to the config/app.php providers array
```
Superbalist\LaravelPubSub\PubSubServiceProvider::class
```

Publish the behat environment and YAML file
```
php artisan vendor:publish --provider="Entanet\Behat\BehatServiceProvider"
```

# **The Package**

Aimed to aid with testing, the package includes API, Database, UI, Kafka and 
Laravel Context files to test with that can be used out of the box. 

While we use in memory databases for DatabaseContext and mockery for Kafka, our API testing class 
will instance an application by leveraging Illuminate's Kernel classes so no real
call outs are ever made, and this allows us to use clean Laravel Feature Test style
assertions.

# **_Advantages_**
This package leverages already established packages with the aim
of making your "*.feature" files **clean**.

Using Behat's TableNode _(http://behat.org/en/latest/user_guide/writing_scenarios.html)_
We can pass values through to endpoints, and make assertions on the responses we are returned.

The table can be converted either to a JSON string or a regular array, and both html body and html header values can be included in the same tables.

By doing these conversions and keeping to Behat's native style,
our "*.feature" files are readable by anyone involved in the project without touching the code.






