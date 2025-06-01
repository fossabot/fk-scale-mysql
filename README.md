# fk-scale-mysql
Scaling up the column type for fields used in foreign keys in MySQL can be a daunting activity, now this will be no more!

Packagist statistics by poser.pugx.org
[![Latest Stable Version](https://poser.pugx.org/danielgp/fk-scale-mysql/v/stable)](https://packagist.org/packages/danielgp/fk-scale-mysql)
[![Total Downloads](https://poser.pugx.org/danielgp/fk-scale-mysql/downloads)](https://packagist.org/packages/danielgp/fk-scale-mysql)
[![License](https://poser.pugx.org/danielgp/fk-scale-mysql/license)](https://packagist.org/packages/danielgp/fk-scale-mysql)
[![Monthly Downloads](https://poser.pugx.org/danielgp/fk-scale-mysql/d/monthly)](https://packagist.org/packages/danielgp/fk-scale-mysql)
[![Daily Downloads](https://poser.pugx.org/danielgp/fk-scale-mysql/d/daily)](https://packagist.org/packages/danielgp/fk-scale-mysql)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fdanielgp%2Ffk-scale-mysql.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fdanielgp%2Ffk-scale-mysql?ref=badge_shield)

Code quality analysis
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c/big.png)](https://insight.sensiolabs.com/projects/c392dde2-7f81-413c-9c80-da48a3a4b89c)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/83e1087fbff94479b333fdd32b74bcc8)](https://www.codacy.com/app/danielpopiniuc/fk-scale-mysql)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/danielgp/fk-scale-mysql/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/danielgp/fk-scale-mysql/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/danielgp/fk-scale-mysql/badges/build.png?b=master)](https://scrutinizer-ci.com/g/danielgp/fk-scale-mysql/build-status/master)

In order to use this package following steps have to be followed:
- Make sure you have installed Composer (https://getcomposer.org/doc/00-intro.md) as the used libraries are not distributed by default in the package on GitHub;
- Run "composer update" command from the root folder of this package (same place where "composer.json" is placed;
- Either have access to MySQL to add a new user (default one stored in the configuration (GRANT SELECT ON *.* TO "web_fk_scale"@"127.0.0.1" IDENTIFIED BY PASSWORD "*929090A686FB866B5F4B8A89D53E78FAE36D9FDD"; FLUSH PRIVILEGES;) or amend the configuration file (ConfigurationMySQL.php) to match a user that has at least SELECT access to target database and all its dependences (if not all dependencies are known, just allow the user to have read-only access to everything);
- Should you choose to give it a try on an already tested database, you can download "world" database from MySQL official site: http://dev.mysql.com/doc/index-other.html

Final remark: This packages only analyze (not a single modification is performed) and only provides sequence of query to make necessary scaling (up or down) of Foreign Keys in MySQL databases starting from a combination of a Database/Table/Column!

(https://github.com/danielgp/fk-scale-mysql)


## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fdanielgp%2Ffk-scale-mysql.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fdanielgp%2Ffk-scale-mysql?ref=badge_large)