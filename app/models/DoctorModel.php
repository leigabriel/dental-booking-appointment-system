<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class DoctorModel extends Model
{
    protected $table = 'doctors';
    protected $primary_key = 'id';
    protected $fillable = ['user_id', 'name', 'specialty', 'email', 'is_available'];

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

    // Get all available doctors
    public function getAvailable()
    {
        if (!isset($this->db)) {
            $this->call->database();
        }

        $sql = "SELECT * FROM doctors WHERE is_available = 1 ORDER BY name ASC";
        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Toggle doctor availability
    public function toggleAvailability($doctor_id)
    {
        if (!isset($this->db)) {
            $this->call->database();
        }

        // Get current status using raw SQL
        $sql = "SELECT id, is_available FROM doctors WHERE id = ? LIMIT 1";
        $stmt = $this->db->raw($sql, [$doctor_id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doctor) {
            return false;
        }
        
        $new_status = $doctor['is_available'] ? 0 : 1;
        
        // Update using raw SQL
        $update_sql = "UPDATE doctors SET is_available = ? WHERE id = ?";
        $update_stmt = $this->db->raw($update_sql, [$new_status, $doctor_id]);
        
        return $update_stmt->rowCount() > 0;
    }

    // Set doctor availability
    public function setAvailability($doctor_id, $is_available)
    {
        return $this->update(['is_available' => $is_available ? 1 : 0], $doctor_id);
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
