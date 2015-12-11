# fk-scale-mysql
Scaling up the column type for fields used in foreign keys in MySQL can be a daunting activity, now this will be no more!

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c/big.png)](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c)

In order to use this package following steps have to be followed:
- Make sure you have installed Composer (https://getcomposer.org/doc/00-intro.md) as the used libraries are not distributed by default in the package on GitHub;
- Run "composer update" command from the root folder of this package (same place where "composer.json" is placed;
- Either have access to MySQL to add a new user (default one stored in the configuration (GRANT SELECT ON *.* TO "web_fk_scale"@"127.0.0.1" IDENTIFIED BY PASSWORD "*929090A686FB866B5F4B8A89D53E78FAE36D9FDD"; FLUSH PRIVILEGES;) or ammend the configuration file (ConfigurationMySQL.php) to match a user that has at least SELECT access to target database and all its dependences (if not all dependencies are known, just allow the user to have read-only access to everything);

Final remark: This packages only analyze (not a single modification is performed) and only provides sequence of query to make neccesary scaling (up or down) of Foreign Keys in MySQL databases starting from a combination of a Database/Table/Column!

(https://github.com/danielgp/fk-scale-mysql)
 