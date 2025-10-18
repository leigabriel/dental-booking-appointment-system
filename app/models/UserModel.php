<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';

    // Only allow safe fields for mass assignment (registration)
    protected $fillable = ['username', 'password', 'role'];

    public function __construct()
    {
        parent::__construct();

        // CRITICAL FIX: Explicitly load the database instance immediately
        // after the parent Model construct to ensure $this->db property is set.
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
