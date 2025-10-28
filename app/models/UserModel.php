<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fillable = ['username', 'password', 'role', 'full_name', 'email', 'name', 'email_verified_at'];

    public function find_by_username($username)
    {
        return $this->filter(['username' => $username])->get();
    }

    public function create_user($data)
    {
        return $this->insert($data);
    }

    public function findUserByEmail($email)
    {
        return $this->filter(['email' => $email])->get();
    }

    public function register($data)
    {
        if (empty($data['password'])) {
            $insert_data = [
                'full_name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'email_verified_at' => $data['email_verified_at']
            ];
        } else {
            $insert_data = [
                'full_name' => $data['name'] ?? ($data['full_name'] ?? ($data['username'] ?? 'User')),
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role']
            ];
            if (isset($data['username'])) {
                $insert_data['username'] = $data['username'];
            }
        }

        $last_insert_id = $this->insert($insert_data);

        return $last_insert_id ? true : false;
    }
}