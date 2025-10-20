<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class AppointmentModel extends Model
{
  protected $table = 'appointments';
  protected $primary_key = 'id';
  protected $fillable = ['user_id', 'doctor_id', 'service_id', 'appointment_date', 'time_slot', 'status'];

  public function __construct()
  {
    parent::__construct();
    // The database connection is loaded by the parent Model and autoload.
  }

  /**
   * Checks if a specific doctor's slot is already booked and not cancelled.
   *
   * @param int $doctor_id
   * @param string $date
   * @param string $time
   * @return bool
   */
  public function is_slot_booked($doctor_id, $date, $time)
  {
    // Ensures the query runs and counts correctly using framework's chaining
    $this->db->table($this->table)
      ->where('doctor_id', $doctor_id)
      ->where('appointment_date', $date)
      ->where('time_slot', $time)
      ->not_in('status', ['cancelled'])
      ->get_all(); // Forces execution for row_count()

    return $this->db->row_count() > 0;
  }
}
