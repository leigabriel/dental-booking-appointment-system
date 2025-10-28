<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';
    // Ensure 'username' is included here
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

    public function register($data)
    {
        if (empty($data['password'])) {
            // --- GOOGLE SIGN-IN ---
            // Generate a username from the email (part before @)
            // Add a random suffix to help uniqueness, remove invalid chars
            $emailParts = explode('@', $data['email']);
            $baseUsername = preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0]); // Allow letters, numbers, _, .
            $username = $baseUsername . '_' . substr(uniqid(), -4); // Add short random suffix

            // Ensure username doesn't exceed potential database limits (adjust '50' if needed)
            $username = substr($username, 0, 50);

            // You might want to add a loop here to check if $username already exists
            // and regenerate if necessary, if your DB has a UNIQUE constraint on username.

            $insert_data = [
                'full_name' => $data['name'],
                'email' => $data['email'],
                'username' => $username, // Add the generated username
                'role' => $data['role'],
                'email_verified_at' => $data['email_verified_at']
            ];
        } else {
            // --- STANDARD REGISTRATION ---
            $insert_data = [
                'full_name' => $data['name'] ?? ($data['full_name'] ?? ($data['username'] ?? 'User')),
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role']
            ];
             if (isset($data['username'])) {
                $insert_data['username'] = $data['username'];
             } else {
                 // Handle cases where standard registration might also need a username generated
                 // Example: Use email part if username wasn't provided in the form
                 $emailParts = explode('@', $data['email']);
                 $baseUsername = preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0]);
                 $insert_data['username'] = substr($baseUsername . '_' . substr(uniqid(), -4), 0, 50);
             }
        }

        $last_insert_id = $this->insert($insert_data);

        return $last_insert_id ? true : false;
    }
}
?>