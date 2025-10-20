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
      $this->call->database();
    }

    public function is_slot_booked($doctor_id, $date, $time)
    {
      $this->db->table($this->table)
        ->where('doctor_id', $doctor_id)
        ->where('appointment_date', $date)
        ->where('time_slot', $time)
        ->not_in('status', ['cancelled']) // <-- FIX: Changed from where_not_in() to not_in()
        ->get();

      return $this->db->row_count() > 0;
    }
  }