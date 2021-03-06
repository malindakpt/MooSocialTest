<?php

/**
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 */

/**
 * This is core configuration file.
 *
 * Use it to configure core behaviour ofCake.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * In this file you set up your database connection details.
 *
 * @package       cake.config
 */
/**
 * Database configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * driver => The name of a supported driver; valid options are as follows:
 *		Database/Mysql 		- MySQL 4 & 5,
 *		Database/Sqlite		- SQLite (PHP5 only),
 *		Database/Postgres	- PostgreSQL 7 and higher,
 *		Database/Mssql		- Microsoft SQL Server 2000 and higher,
 *		Database/Oracle		- Oracle 8 and higher
 *
 * You can add custom database drivers (or override existing drivers) by adding the
 * appropriate file to app/models/datasources/database.  Drivers should be named 'MyDriver.php',
 *
 *
 * persistent => true / false
 * Determines whether or not the database should use a persistent connection
 *
 * host =>
 * the host you connect to the database. To add a socket or port number, use 'port' => #
 *
 * prefix =>
 * Uses the given prefix for all the tables in this database.  This setting can be overridden
 * on a per-table basis with the Model::$tablePrefix property.
 *
 * schema =>
 * For Postgres specifies which schema you would like to use the tables in. Postgres defaults to 'public'.
 *
 * encoding =>
 * For MySQL, Postgres specifies the character encoding to use when connecting to the
 * database. Uses database default not specified.
 *
 */

class DATABASE_CONFIG {

	public $default = array(
		'datasource' => 'Database/Moosql',
		'persistent' => false,
		//'encoding' => 'utf8',
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
	);

	public $test = array(
		'datasource' => 'Database/Moosql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'user',
		'password' => 'password',
		'database' => 'test_database_name',
		'prefix' => '',
	);
	
	public function __construct()
	{
		if ( file_exists( APP . 'Config/config.php' ) )
		{			
			require( APP . 'Config/config.php' );
			
			$this->default['host'] 	      = $CONFIG['host'];
			$this->default['login']       = $CONFIG['login'];
			$this->default['password']    = $CONFIG['password'];
			$this->default['database']    = $CONFIG['database'];
			$this->default['unix_socket'] = $CONFIG['port'];
			$this->default['prefix']      = $CONFIG['prefix'];
			if (isset($CONFIG['encoding']))
			{
				$this->default['encoding'] = $CONFIG['encoding'];
			}
			$this->default['settings'] = array('SESSION sql_mode'=>"''");
		}
	}
}
