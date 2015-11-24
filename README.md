# fk-scale-mysql
Scaling up the column type for fields used in foreign keys in MySQL can be a daunting activity, now this will be no more!

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c/big.png)](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c)

In order to use this package with exact MySQL user configured on a localhost, you need to run the following 2 queries:
GRANT SELECT ON *.* TO "web_fk_scale"@"127.0.0.1" IDENTIFIED BY PASSWORD "*929090A686FB866B5F4B8A89D53E78FAE36D9FDD";
FLUSH PRIVILEGES;
or you can create your own username and adjust configuration accordingly!
