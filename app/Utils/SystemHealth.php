<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Utils;

use App\Libraries\MultiDB;
use Illuminate\Support\Facades\DB;

/**
 * Class SystemHealth.
 */
class SystemHealth
{

	private static $extensions = [
		'mysqli',
		'gd',
		'curl',
		'zip',
		'gmp'
	];

    private static $php_version = 7.3;


	/**
	 * Check loaded extensions / PHP version / DB Connections
	 *
	 * @return     array  Result set of checks
	 */
	public static function check() : array
	{
		$system_health = TRUE;

		if (in_array(FALSE, self::extensions())) 
		{
			$system_health = FALSE;
		}
		elseif (phpversion() < self::$php_version)
		{
			$system_health = FALSE;
		}


		return [
			'system_health' => $system_health,
			'extensions' => self::extensions(),
			'php_version' => phpversion(),
			'min_php_version' => self::$php_version,
			'dbs' => self::dbCheck(),
		];

	}

	private static function extensions() :array
	{

		$loaded_extensions = [];

		foreach(self::$extensions as $extension)
		{

			$loaded_extensions[] = [$extension => extension_loaded($extension)];

		}

		return $loaded_extensions;

	}

	private static function dbCheck() :array
	{

		$result = [];

        if (! config('ninja.db.multi_db_enabled'))
        {
            $pdo = DB::connection()->getPdo();

            if($pdo)
            	$result[] = [ DB::connection()->getDatabaseName() => TRUE ];
            else
            	$result[] = [ config('database.connections.' . config('database.default') . '.database') => FALSE ];

        }
        else
        {

            foreach (MultiDB::$dbs as $db)
            {
                MultiDB::setDB($db);

				$pdo = DB::connection()->getPdo();
                            
                if($pdo)
	            	$result[] = [ DB::connection()->getDatabaseName() => TRUE ];
	            else
	            	$result[] = [ config('database.connections.' . config('database.default') . '.database') => FALSE ];
            }

            return $result;
        }

	}

}
