<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class User extends Site
{
	public $timestamp = false;

    protected $table = "user";

    public function organization()
    {
    	return $this->belongsTo('App\Models\Organization', 'organization_id')->select('id', 'name');
    }
}
