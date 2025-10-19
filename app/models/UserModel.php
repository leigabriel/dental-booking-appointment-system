<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';

    // UPDATED: Allow new fields for mass assignment
    protected $fillable = ['username', 'password', 'role', 'full_name', 'email'];

    public function __construct()
    {
        // ... (omitted code) ...
        $this->call->database();
    }

    // Retrieves a single user row by username
    public function find_by_username($username)
    {
        return $this->filter(['username' => $username])->get();
    }

    // Creates a new user record
    public function create_user($data)
    {
        return $this->insert($data);
    }
}