<?php

namespace libs;

use Illuminate\Database\Eloquent\Model;

class Session extends Model {

	protected $connection = 'wrapper';

	protected $table = 'sessions';

	public $timestamps = false;

	protected $fillable = array('*');

}
