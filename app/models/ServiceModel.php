<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primary_key = 'id';
    protected $fillable = ['name', 'price', 'duration_mins'];

    public function __construct()
    {
        parent::__construct();
        $this->call->database();
    }
}
