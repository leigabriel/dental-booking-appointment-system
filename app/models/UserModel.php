<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fillable = ['username', 'password', 'role', 'full_name', 'email', 'email_verified_at'];

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

    // Register user with conditional username generation
    public function register($data)
    {
        if (empty($data['password'])) {
            $emailParts = explode('@', $data['email']);
            $baseUsername = preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0]);
            $username = $baseUsername . '_' . substr(uniqid(), -4);
            $username = substr($username, 0, 50);

            $insert_data = [
                'full_name' => $data['name'],
                'email' => $data['email'],
                'username' => $username,
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
            } else {
                $emailParts = explode('@', $data['email']);
                $baseUsername = preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0]);
                $insert_data['username'] = substr($baseUsername . '_' . substr(uniqid(), -4), 0, 50);
            }
        }

        $last_insert_id = $this->insert($insert_data);

        return $last_insert_id ? true : false;
    }
}