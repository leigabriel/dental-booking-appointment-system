<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class AppointmentModel extends Model
{
  protected $table = 'appointments';
  protected $primary_key = 'id';
  protected $fillable = ['user_id', 'doctor_id', 'service_id', 'appointment_date', 'time_slot', 'status', 'decline_message'];

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
}