<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Site extends Model
{
    const TEST = 'test';

    const DEMO = 'demo';
    
    const PROD = 'production';

    public static function mapConnection($site)
    {
    	$maps = [
    		self::TEST => 'pgsql_test',
    		self::DEMO => 'pgsql_demo',
    		self::PROD => 'pgsql_prod',
    	];

    	if (!in_array($site, array_keys($maps))) {
    		return $maps[self::TEST];
    	}

    	return $maps[$site];
    }

    public static function query($site = '')
    {
    	$model = new static;
    	$model->connection = self::mapConnection($site);
    	return $model->newQuery();
    }
}