<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class DoctorModel extends Model
{
    protected $table = 'doctors';
    protected $primary_key = 'id';
    protected $fillable = ['name', 'specialty', 'email'];

    public function __construct()
    {
        parent::__construct();
        $this->call->database();
    }
}