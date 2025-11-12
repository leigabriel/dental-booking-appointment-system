<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class AppointmentModel extends Model
{
  protected $table = 'appointments';
  protected $primary_key = 'id';
  protected $fillable = ['user_id', 'doctor_id', 'service_id', 'appointment_date', 'time_slot', 'status', 'decline_message', 'payment_method', 'payment_status', 'payment_reference'];

  public function __construct()
  {
    parent::__construct();
  }
  
  // Checks if a specific doctor's slot is already booked and not cancelled.
  public function is_slot_booked($doctor_id, $date, $time)
  {
    $this->db->table($this->table)
      ->where('doctor_id', $doctor_id)
      ->where('appointment_date', $date)
      ->where('time_slot', $time)
      ->not_in('status', ['cancelled'])
      ->get_all();

    return $this->db->row_count() > 0;
  }

  /**
   * Cancel an appointment (mark status as 'cancelled').
   * If $user_id is provided, ensure the appointment belongs to that user.
   *
   * @param int $appointment_id
   * @param int|null $user_id
   * @return bool
   */
  public function cancel($appointment_id, $user_id = null)
  {
    $appointment = $this->find($appointment_id);
    if (!$appointment) {
      return false;
    }

    if ($user_id !== null && $appointment['user_id'] != $user_id) {
      // Do not allow cancelling others' appointments
      return false;
    }

    return (bool) $this->update($appointment_id, ['status' => 'cancelled']);
  }

  /**
   * Count non-cancelled/non-declined bookings for a specific date.
   * These are the active bookings that should count against the daily cap.
   *
   * @param string $date YYYY-MM-DD
   * @return int
   */
  public function count_active_by_date($date)
  {
    $stmt = $this->db->raw(
      "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE appointment_date = ? AND status NOT IN ('cancelled','declined')",
      [$date]
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cnt' => 0];
    return (int) $row['cnt'];
  }

  /**
   * Get all booked time slots for a specific doctor and date
   * Returns an array of time slots that are already booked (not cancelled)
   *
   * @param int $doctor_id
   * @param string $date YYYY-MM-DD
   * @return array Array of time slot strings (e.g., ['08:00:00', '09:00:00'])
   */
  public function get_booked_time_slots($doctor_id, $date)
  {
    $stmt = $this->db->raw(
      "SELECT time_slot FROM {$this->table} WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('cancelled', 'declined')",
      [$doctor_id, $date]
    );
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Extract just the time_slot values into a simple array
    return array_map(function($row) {
      return $row['time_slot'];
    }, $results);
  }

  /**
   * Clear all appointment history for a user (except confirmed appointments)
   * Deletes all appointments with status: pending, cancelled, declined
   *
   * @param int $user_id
   * @return bool
   */
  public function clear_history($user_id)
  {
    try {
      $stmt = $this->db->raw(
        "DELETE FROM {$this->table} WHERE user_id = ? AND status IN ('pending', 'cancelled', 'declined')",
        [$user_id]
      );
      return true;
    } catch (Exception $e) {
      return false;
    }
  }
}