<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fillable = ['username', 'password', 'role', 'full_name', 'email'];

    public function __construct()
    {
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

    /**
     * Finds a user by their email address.
     *
     * @param string $email The user's email
     * @return object|false The user object or false if not found
     */
    public function findUserByEmail($email)
    {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->single();

        return ($this->db->rowCount() > 0) ? $row : false;
    }

    /**
     * Registers a new user.
     * This method now handles BOTH standard and Google registration.
     *
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    public function register($data)
    {
        // Check if this is a Google registration (no password)
        if (empty($data['password'])) {
            // Google Sign-Up: Insert without a password
            $this->db->query('INSERT INTO users (name, email, role, email_verified_at) VALUES (:name, :email, :role, :verified_at)');
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':role', $data['role']);
            $this->db->bind(':verified_at', $data['email_verified_at']);
        } else {
            // Standard Email/Password Registration
            $this->db->query('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT)); // Hash password
            $this->db->bind(':role', $data['role']);
        }

        // Execute the query
        return $this->db->execute();
    }
}