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

    public function index()
    {
        $data['doctors'] = $this->DoctorModel->all();
        $data['services'] = $this->ServiceModel->all();

        $this->call->view('booking/appointment_form', $data);
    }

    public function submit()
    {
        if (!$this->io->post()) {
            redirect('book');
        }

        $user_id = $this->session->userdata('user_id');
        $post = $this->io->post();

        // 1. Validation
        $this->form_validation
            ->name('doctor_id|Doctor')->required()->numeric()
            ->name('service_id|Service')->required()->numeric()
            ->name('appointment_date|Date')->required()->custom_pattern('^\d{4}-\d{2}-\d{2}$', 'Invalid date format (YYYY-MM-DD)')
            ->name('time_slot|Time')->required();

        if ($this->form_validation->run()) {

            // 2. Check Availability
            if ($this->AppointmentModel->is_slot_booked($post['doctor_id'], $post['appointment_date'], $post['time_slot'])) {
                $this->session->set_flashdata('error_message', 'The selected time slot is already taken. Please choose another.');
                redirect('book');
            }

            // 3. Insert Appointment
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
            // 4. Validation Failed
            $data['doctors'] = $this->DoctorModel->all();
            $data['services'] = $this->ServiceModel->all();
            $data['errors'] = $this->form_validation->get_errors();

            global $data;
            $data = array_merge($data, $post);

            $this->call->view('booking/appointment_form', $data);
        }
    }
}