<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class ProfileLogs extends Site
{
	public function realname()
	{
		return $this->hasOne('App\Models\User', 'id', 'uid')->select('id', 'realname');
	}
}