#!/bin/bash

cd /opt/php-pubsub/
composer require superbalist/php-pubsub-google-cloud
composer dump-autoload
cd /opt/php-pubsub/examples

php HTTPPublishExample.php
