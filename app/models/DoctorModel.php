<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class DoctorModel extends Model
{
    protected $table = 'doctors';
    protected $primary_key = 'id';
    protected $fillable = ['user_id', 'name', 'specialty', 'email'];

    public function __construct()
    {
        parent::__construct();
        $this->call->database();
    }

    // Find doctor by user_id
    public function findByUserId($user_id)
    {
        return $this->filter(['user_id' => $user_id])->get();
    }

    // Get all doctors with their linked user accounts
    public function getAllWithUsers()
    {
        if (!isset($this->db)) {
            $this->call->database();
        }

        $sql = "SELECT d.*, u.username, u.full_name as user_full_name, u.role
                FROM doctors d
                LEFT JOIN users u ON u.id = d.user_id
                ORDER BY d.id DESC";
        
        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Link doctor to user account
    public function linkToUser($doctor_id, $user_id)
    {
        return $this->update(['user_id' => $user_id], $doctor_id);
    }

    // Unlink doctor from user account
    public function unlinkFromUser($doctor_id)
    {
        return $this->update(['user_id' => NULL], $doctor_id);
    }

    // Create doctor and optionally link to user
    public function createDoctor($data)
    {
        $doctor_data = [
            'name' => $data['name'],
            'specialty' => $data['specialty'],
            'email' => $data['email']
        ];

        if (isset($data['user_id'])) {
            $doctor_data['user_id'] = $data['user_id'];
        }

        return $this->insert($doctor_data);
    }
}
