<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Reports extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('AppointmentModel');
        $this->call->model('UserModel');
        $this->call->model('DoctorModel');
        $this->call->model('ServiceModel');
        $this->call->library('Form_validation');
        $this->call->helper('url');
        $this->call->helper('language');

        // Ensure only admin can access reports
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error_message', 'Admin privileges required.');
            redirect('login');
        }
    }

    // Main reports dashboard
    public function index()
    {
        $LAVA = lava_instance();
        if (!isset($LAVA->db)) {
            $this->call->database();
        }

        $admin_user_id = $this->session->userdata('user_id');
        $admin_details = $this->UserModel->find($admin_user_id);

        // Get date range from request or default to current month
        $start_date = isset($_GET['start_date']) ? $this->io->get('start_date') : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $this->io->get('end_date') : date('Y-m-t');

        // Appointments Report Data
        $appointments_data = $this->getAppointmentsReport($start_date, $end_date);
        
        // Revenue Report Data
        $revenue_data = $this->getRevenueReport($start_date, $end_date);
        
        // Doctors Performance Data
        $doctors_data = $this->getDoctorsPerformance($start_date, $end_date);
        
        // Services Report Data
        $services_data = $this->getServicesReport($start_date, $end_date);
        
        // Payment Methods Report
        $payment_methods_data = $this->getPaymentMethodsReport($start_date, $end_date);

        // Status Distribution
        $status_distribution = $this->getStatusDistribution($start_date, $end_date);

        $data = [
            'admin_details' => $admin_details,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'appointments_data' => $appointments_data,
            'revenue_data' => $revenue_data,
            'doctors_data' => $doctors_data,
            'services_data' => $services_data,
            'payment_methods_data' => $payment_methods_data,
            'status_distribution' => $status_distribution,
        ];

        $this->call->view('admin/reports', $data);
    }

    // Export Appointments Report
    public function export_appointments()
    {
        $format = isset($_GET['format']) ? $this->io->get('format') : 'csv';
        $start_date = isset($_GET['start_date']) ? $this->io->get('start_date') : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $this->io->get('end_date') : date('Y-m-t');

        $data = $this->getDetailedAppointmentsData($start_date, $end_date);

        if ($format === 'csv') {
            $this->exportCSV($data, 'appointments_report', [
                'ID', 'Patient Name', 'Email', 'Doctor', 'Service', 'Date', 'Time', 
                'Status', 'Payment Method', 'Payment Status', 'Amount', 'Created At'
            ]);
        } else if ($format === 'pdf') {
            $this->exportAppointmentsPDF($data, $start_date, $end_date);
        }
    }

    // Export Revenue Report
    public function export_revenue()
    {
        $format = isset($_GET['format']) ? $this->io->get('format') : 'csv';
        $start_date = isset($_GET['start_date']) ? $this->io->get('start_date') : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $this->io->get('end_date') : date('Y-m-t');

        $data = $this->getRevenueDetailedData($start_date, $end_date);

        if ($format === 'csv') {
            $this->exportCSV($data, 'revenue_report', [
                'Date', 'Total Revenue', 'Paid Amount', 'Pending Amount', 
                'Number of Appointments', 'Average Transaction'
            ]);
        } else if ($format === 'pdf') {
            $this->exportRevenuePDF($data, $start_date, $end_date);
        }
    }

    // Export Doctors Performance Report
    public function export_doctors()
    {
        $format = isset($_GET['format']) ? $this->io->get('format') : 'csv';
        $start_date = isset($_GET['start_date']) ? $this->io->get('start_date') : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $this->io->get('end_date') : date('Y-m-t');

        $data = $this->getDoctorsPerformance($start_date, $end_date);

        if ($format === 'csv') {
            $this->exportCSV($data, 'doctors_performance_report', [
                'Doctor Name', 'Specialty', 'Total Appointments', 'Confirmed', 
                'Pending', 'Cancelled', 'Revenue Generated'
            ]);
        } else if ($format === 'pdf') {
            $this->exportDoctorsPDF($data, $start_date, $end_date);
        }
    }

    // Export Services Report
    public function export_services()
    {
        $format = isset($_GET['format']) ? $this->io->get('format') : 'csv';
        $start_date = isset($_GET['start_date']) ? $this->io->get('start_date') : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $this->io->get('end_date') : date('Y-m-t');

        $data = $this->getServicesReport($start_date, $end_date);

        if ($format === 'csv') {
            $this->exportCSV($data, 'services_report', [
                'Service Name', 'Base Price', 'Times Booked', 
                'Total Revenue', 'Average Duration'
            ]);
        } else if ($format === 'pdf') {
            $this->exportServicesPDF($data, $start_date, $end_date);
        }
    }

    // Private helper methods

    private function getAppointmentsReport($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
                FROM appointments 
                WHERE appointment_date BETWEEN ? AND ?";
        
        $result = $LAVA->db->raw($sql, [$start_date, $end_date])->fetch(PDO::FETCH_ASSOC);
        
        // Ensure all keys exist with default values
        return [
            'total' => $result['total'] ?? 0,
            'confirmed' => $result['confirmed'] ?? 0,
            'pending' => $result['pending'] ?? 0,
            'cancelled' => $result['cancelled'] ?? 0,
            'declined' => $result['declined'] ?? 0
        ];
    }

    private function getRevenueReport($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    COALESCE(SUM(s.price), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN a.payment_status = 'paid' THEN s.price ELSE 0 END), 0) as paid_revenue,
                    COALESCE(SUM(CASE WHEN a.payment_status = 'pending' THEN s.price ELSE 0 END), 0) as pending_revenue,
                    COALESCE(SUM(CASE WHEN a.payment_status = 'unpaid' THEN s.price ELSE 0 END), 0) as unpaid_revenue,
                    COUNT(*) as total_appointments,
                    COALESCE(AVG(s.price), 0) as average_transaction
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                WHERE a.appointment_date BETWEEN ? AND ?
                AND a.status != 'cancelled' AND a.status != 'declined'";
        
        $result = $LAVA->db->raw($sql, [$start_date, $end_date])->fetch(PDO::FETCH_ASSOC);
        
        // Ensure all keys exist with default values
        return [
            'total_revenue' => floatval($result['total_revenue'] ?? 0),
            'paid_revenue' => floatval($result['paid_revenue'] ?? 0),
            'pending_revenue' => floatval($result['pending_revenue'] ?? 0),
            'unpaid_revenue' => floatval($result['unpaid_revenue'] ?? 0),
            'total_appointments' => intval($result['total_appointments'] ?? 0),
            'average_transaction' => floatval($result['average_transaction'] ?? 0)
        ];
    }

    private function getDoctorsPerformance($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    d.id,
                    d.name as doctor_name,
                    d.specialty,
                    COUNT(a.id) as total_appointments,
                    SUM(CASE WHEN a.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN a.payment_status = 'paid' THEN s.price ELSE 0 END) as revenue_generated
                FROM doctors d
                LEFT JOIN appointments a ON d.id = a.doctor_id 
                    AND a.appointment_date BETWEEN ? AND ?
                LEFT JOIN services s ON a.service_id = s.id
                GROUP BY d.id, d.name, d.specialty
                ORDER BY total_appointments DESC";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getServicesReport($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    s.id,
                    s.name as service_name,
                    s.price as base_price,
                    s.duration_mins,
                    COUNT(a.id) as times_booked,
                    SUM(CASE WHEN a.payment_status = 'paid' THEN s.price ELSE 0 END) as total_revenue
                FROM services s
                LEFT JOIN appointments a ON s.id = a.service_id 
                    AND a.appointment_date BETWEEN ? AND ?
                    AND a.status != 'cancelled' AND a.status != 'declined'
                GROUP BY s.id, s.name, s.price, s.duration_mins
                ORDER BY times_booked DESC";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getPaymentMethodsReport($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    a.payment_method,
                    COUNT(*) as count,
                    SUM(s.price) as total_amount
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                WHERE a.appointment_date BETWEEN ? AND ?
                AND a.payment_status = 'paid'
                GROUP BY a.payment_method";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getStatusDistribution($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM appointments
                WHERE appointment_date BETWEEN ? AND ?
                GROUP BY status";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getDetailedAppointmentsData($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    a.id,
                    u.full_name as patient_name,
                    u.email,
                    d.name as doctor_name,
                    s.name as service_name,
                    a.appointment_date,
                    a.time_slot,
                    a.status,
                    a.payment_method,
                    a.payment_status,
                    s.price as amount,
                    a.created_at
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN services s ON a.service_id = s.id
                WHERE a.appointment_date BETWEEN ? AND ?
                ORDER BY a.appointment_date DESC, a.time_slot DESC";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getRevenueDetailedData($start_date, $end_date)
    {
        $LAVA = lava_instance();
        
        $sql = "SELECT 
                    DATE(a.appointment_date) as date,
                    SUM(s.price) as total_revenue,
                    SUM(CASE WHEN a.payment_status = 'paid' THEN s.price ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN a.payment_status = 'pending' THEN s.price ELSE 0 END) as pending_amount,
                    COUNT(*) as appointment_count,
                    AVG(s.price) as average_transaction
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                WHERE a.appointment_date BETWEEN ? AND ?
                AND a.status != 'cancelled' AND a.status != 'declined'
                GROUP BY DATE(a.appointment_date)
                ORDER BY date DESC";
        
        return $LAVA->db->raw($sql, [$start_date, $end_date])->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Generic CSV Export
    private function exportCSV($data, $filename, $headers)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '_' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for proper Excel encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
    }

    // PDF Export for Appointments
    private function exportAppointmentsPDF($data, $start_date, $end_date)
    {
        require_once(APPPATH . '../vendor/autoload.php');
        
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('DentalCare System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Appointments Report');
        $pdf->SetSubject('Appointments Report');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, 'DentalCare Appointments Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generated: ' . date('M d, Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(15, 7, 'ID', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Patient', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Doctor', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Service', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Date', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Time', 1, 0, 'C', 1);
        $pdf->Cell(23, 7, 'Status', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Payment', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Amount', 1, 1, 'C', 1);
        
        // Table data
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        
        $fill = false;
        foreach ($data as $row) {
            $pdf->SetFillColor(249, 250, 251);
            
            $pdf->Cell(15, 6, $row['id'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, substr($row['patient_name'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, substr($row['doctor_name'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, substr($row['service_name'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, date('M d, Y', strtotime($row['appointment_date'])), 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, date('h:i A', strtotime($row['time_slot'])), 1, 0, 'C', $fill);
            $pdf->Cell(23, 6, ucfirst($row['status']), 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, ucfirst($row['payment_status']), 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, '₱' . number_format($row['amount'], 2), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }
        
        // Output PDF
        $pdf->Output('appointments_report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    // PDF Export for Revenue
    private function exportRevenuePDF($data, $start_date, $end_date)
    {
        require_once(APPPATH . '../vendor/autoload.php');
        
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('DentalCare System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Revenue Report');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'DentalCare Revenue Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generated: ' . date('M d, Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Calculate totals
        $grand_total = 0;
        $grand_paid = 0;
        $grand_pending = 0;
        $total_appointments = 0;
        
        foreach ($data as $row) {
            $grand_total += $row['total_revenue'];
            $grand_paid += $row['paid_amount'];
            $grand_pending += $row['pending_amount'];
            $total_appointments += $row['appointment_count'];
        }
        
        // Summary section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Summary', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 8, 'Total Revenue', 1, 0, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 8, '₱' . number_format($grand_total, 2), 1, 1, 'R');
        
        $pdf->SetFillColor(34, 197, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 8, 'Paid Amount', 1, 0, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 8, '₱' . number_format($grand_paid, 2), 1, 1, 'R');
        
        $pdf->SetFillColor(234, 179, 8);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 8, 'Pending Amount', 1, 0, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 8, '₱' . number_format($grand_pending, 2), 1, 1, 'R');
        
        $pdf->SetFillColor(156, 163, 175);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 8, 'Total Appointments', 1, 0, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 8, $total_appointments, 1, 1, 'R');
        
        $pdf->Ln(10);
        
        // Daily breakdown table
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Daily Breakdown', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(35, 7, 'Date', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Total Revenue', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Paid', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Pending', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Appointments', 1, 1, 'C', 1);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        $fill = false;
        foreach ($data as $row) {
            $pdf->SetFillColor(249, 250, 251);
            
            $pdf->Cell(35, 6, date('M d, Y', strtotime($row['date'])), 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, '₱' . number_format($row['total_revenue'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(35, 6, '₱' . number_format($row['paid_amount'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(35, 6, '₱' . number_format($row['pending_amount'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(40, 6, $row['appointment_count'], 1, 1, 'C', $fill);
            
            $fill = !$fill;
        }
        
        $pdf->Output('revenue_report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    // PDF Export for Doctors
    private function exportDoctorsPDF($data, $start_date, $end_date)
    {
        require_once(APPPATH . '../vendor/autoload.php');
        
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('DentalCare System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Doctors Performance Report');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'DentalCare Doctors Performance Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generated: ' . date('M d, Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(60, 7, 'Doctor Name', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Specialty', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Total Appts', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Confirmed', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Pending', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Cancelled', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Revenue', 1, 1, 'C', 1);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        $fill = false;
        foreach ($data as $row) {
            $pdf->SetFillColor(249, 250, 251);
            
            $pdf->Cell(60, 6, substr($row['doctor_name'], 0, 35), 1, 0, 'L', $fill);
            $pdf->Cell(50, 6, substr($row['specialty'], 0, 30), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $row['total_appointments'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $row['confirmed'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $row['pending'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $row['cancelled'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, '₱' . number_format($row['revenue_generated'], 2), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }
        
        $pdf->Output('doctors_performance_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    // PDF Export for Services
    private function exportServicesPDF($data, $start_date, $end_date)
    {
        require_once(APPPATH . '../vendor/autoload.php');
        
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('DentalCare System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Services Report');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'DentalCare Services Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generated: ' . date('M d, Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(70, 7, 'Service Name', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Base Price', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Times Booked', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Total Revenue', 1, 1, 'C', 1);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        $fill = false;
        $total_bookings = 0;
        $total_revenue = 0;
        
        foreach ($data as $row) {
            $pdf->SetFillColor(249, 250, 251);
            
            $pdf->Cell(70, 6, substr($row['service_name'], 0, 40), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, '₱' . number_format($row['base_price'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(30, 6, $row['times_booked'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, '₱' . number_format($row['total_revenue'], 2), 1, 1, 'R', $fill);
            
            $total_bookings += $row['times_booked'];
            $total_revenue += $row['total_revenue'];
            $fill = !$fill;
        }
        
        // Totals row
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(100, 7, 'TOTALS', 1, 0, 'R', 1);
        $pdf->Cell(30, 7, $total_bookings, 1, 0, 'C', 1);
        $pdf->Cell(40, 7, '₱' . number_format($total_revenue, 2), 1, 1, 'R', 1);
        
        $pdf->Output('services_report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
}
