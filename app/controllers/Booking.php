<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Booking extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Ensure user is logged in to book appointments
        if (!$this->session->userdata('is_logged_in')) {
            $this->session->set_flashdata('error_message', 'You must be logged in to schedule an appointment.');
            redirect('login');
        }

        $this->call->model(['DoctorModel', 'ServiceModel', 'AppointmentModel']);
        $this->call->library('Form_validation');
        $this->call->helper(['url', 'language']);
    }

    // Show appointment booking form
    public function index()
    {
        $data['doctors'] = $this->DoctorModel->all();
        $data['services'] = $this->ServiceModel->all();

        $this->call->view('booking/appointment_form', $data);
    }

    // Submit appointment booking
    public function submit()
    {
        if (!$this->io->post()) {
            redirect('book');
        }

        $user_id = $this->session->userdata('user_id');
        $post = $this->io->post();

        // Validation
        $this->form_validation
            ->name('doctor_id|Doctor')->required()->numeric()
            ->name('service_id|Service')->required()->numeric()
            ->name('appointment_date|Date')->required()->custom_pattern('^\d{4}-\d{2}-\d{2}$', 'Invalid date format (YYYY-MM-DD)')
            ->name('time_slot|Time')->required();

        if ($this->form_validation->run()) {

            // Check Availability
            if ($this->AppointmentModel->is_slot_booked($post['doctor_id'], $post['appointment_date'], $post['time_slot'])) {
                $this->session->set_flashdata('error_message', 'This time slot is already booked. Please choose another time.');
                redirect('book');
            }

            // Enforce daily booking cap (max 5 active bookings per day)
            try {
                $active_count = $this->AppointmentModel->count_active_by_date($post['appointment_date']);
            } catch (Exception $e) {
                $active_count = 5; // fail-safe to block when counting fails
            }
            if ($active_count >= 5) {
                $this->session->set_flashdata('error_message', 'This date has reached the daily booking limit (5 appointments). Please select another date.');
                redirect('book');
            }

            // Insert Appointment
            $booking_data = [
                'user_id' => $user_id,
                'doctor_id' => $post['doctor_id'],
                'service_id' => $post['service_id'],
                'appointment_date' => $post['appointment_date'],
                'time_slot' => $post['time_slot'],
                'status' => 'pending'
            ];

            $appointment_id = $this->AppointmentModel->insert($booking_data);

            if ($appointment_id) {
                $this->session->set_flashdata('success_message', 'Appointment scheduled successfully! Awaiting confirmation.');
            } else {
                $this->session->set_flashdata('error_message', 'Failed to schedule appointment. Please try again.');
            }
            redirect('book');
        } else {

            // Validation Failed
            $data['doctors'] = $this->DoctorModel->all();
            $data['services'] = $this->ServiceModel->all();
            $data['errors'] = $this->form_validation->get_errors();

            global $data;
            $data = array_merge($data, $post);

            $this->call->view('booking/appointment_form', $data);
        }
    }

    // Get booked time slots for a specific doctor and date (AJAX endpoint)
    public function get_booked_slots()
    {
        // Only allow AJAX requests
        if (!$this->io->is_ajax()) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            return;
        }

        $doctor_id = $this->io->get('doctor_id');
        $date = $this->io->get('date');

        if (!$doctor_id || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            return;
        }

        // Get all booked time slots for this doctor and date
        $booked_slots = $this->AppointmentModel->get_booked_time_slots($doctor_id, $date);
        
        // Get total active bookings for this date (for daily limit check)
        $daily_count = $this->AppointmentModel->count_active_by_date($date);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'booked_slots' => $booked_slots,
            'daily_count' => $daily_count,
            'daily_limit' => 5,
            'doctor_id' => $doctor_id,
            'date' => $date
        ]);
    }
}