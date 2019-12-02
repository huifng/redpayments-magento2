# Magento-2
The payment extension for Magento 2, support WeChat Pay and Alipay

Magento® 2 use the Composer to manage the module package and the library. Composer is a dependency manager for PHP. Composer declare the libraries your project depends on and it will manage (install/update) them for you.

# Check Composer Status
Check if your server has composer installed by running the following command:
```shell
composer –v
```
If your server doesn’t have the composer install, you can easily install it. 
[https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

# Install using Composer
Step-by-step to install the Magento® 2 extension by Composer:

Run the ssh console.
Locate your Root
Install the Magento® 2 extension
Cache and Deploy
Run your SSH Console to connect to your Magento® 2 store

Locate the root of your Magento® 2 store.

Enter the command line in your Root and wait as composer will download the extension for you:
```shell
composer require redpayments/magento2
```
When it’s finished you can activate the extension, clean the caches and deploy the content in your Magento® environment using the following command line;
```shell
php bin/magento module:enable Redpayments_Magento2
php bin/magento setup:upgrade
php bin/magento cache:clean
```
If Magento® is running in production mode, deploy the static content:
```shell
php bin/magento setup:static-content:deploy
```
After the installation. Go to your Magento® admin portal, to 'Stores' > 'Configuration’ > 'Sales’ > 'Payment Methods’ > 'Redpayments’ and enable it.

# About Redpayments
Redpayments is a leading Australian cross-border payment platform, dedicated to providing simple, secure and fast payment solutions that users can process via mobile devices for any scenario.

You can send us email at admin@redpayments.com.au to get more information.

## FEATURES
- Expand your reach and attract more Chinese customers and marketing capabilities. Consumers pay in RMB and merchants can settle in australia dollars.
- Get the transaction insights you need anytime you want and anywhere you want. 
- Accepting Alipay and WeChat Pay means higher approval rates and tightened security. 
- Improve the customer experience, payments are completed within three seconds.
- The exclusive same day settlement improve your cash flow. 
- Lower processing rate than all the international credit card rates. No setup fee, no management fee. 
- We provide various payment integration options to suit your business process.

Alipay and WeChat Pay are the biggest payment platforms in China. Redpayments has established strategic partnerships with them, using China's advanced payment technology to target Australia market. We are an adaptable, multi-platform payment solution with a comprehensive management system catering to the needs of sales persons and merchants. We service industries ranging from restaurants, supermarkets, travel, events and international students with more room for growth.