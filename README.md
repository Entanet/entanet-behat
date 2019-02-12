# **EntaNet Behat**
This package is designed specifically for EntaNet's Software QA department to aid with automation of features.

### Installing

A step by step series of examples that tell you how to get a development env running

```
composer require entanet/entanet-behat
php artisan vendor:publish --provider="Entanet\Behat\BehatServiceProvider"
```

Publishing vendor files will create a behat.yml with suites initialized
and a .env.behat with a sample URL and an in memory database.

After running this, go to your _config/app.php_ file and 
add this line to the "Providers" array:
```
'PubSub' => Superbalist\PubSub\Adapters\LocalPubSubAdapter::class,
```

Finally, go to your _AppServiceProvider.php_ and make a binding
between the PubSub interface and the LocalAdapter:

```
  public function register()
    {
        $this->app->bind('Superbalist\PubSub\PubSubAdapterInterface', 'Superbalist\PubSub\Adapters\LocalPubSubAdapter');
    }
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

# **_Notice_**
At present, this is for API testing through Behat. A UI, Kafka and Database context class will be implemented into this package in the future.





