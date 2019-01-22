# **EntaNet Behat**
This package is designed specifically for EntaNet's Software QA department to aid with automation of features.

# **The Package**
Ensure Behat is initialized within your project before installing.

You will find an "API_Context" class within the /src directory.
This extends Imbo's Behat API Extension 
_(https://github.com/imbo/behat-api-extension)_
and uses Guzzle as the client handler. 

Our class requires one change out of the box, which is the "$requestPath"
variable in the constructor. Here, replace the placeholder with your base API url.

The class is autoloaded, so just extend the class in your FeatureContext class.

There is a directory within /src called "examples" which will show you all the capabilities of the "API_Context" class.
As the package evolves, so will the examples - So keep them in mind when there is a version update!

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





