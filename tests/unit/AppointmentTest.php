<?php

/**
 * Unit tests for Appointment model
 */

class AppointmentTest {
    
    public function testAppointmentCreation() {
        echo "Testing appointment creation...\n";
        
        $data = [
            'id' => 1,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-30',
            'appointment_time' => '10:00:00',
            'duration' => 30,
            'type' => 'nfz',
            'status' => 'scheduled',
            'reason' => 'Regular checkup'
        ];
        
        $appointment = new Appointment($data);
        
        assert($appointment->getId() === 1, "ID should be 1");
        assert($appointment->getPatientId() === 1, "Patient ID should be 1");
        assert($appointment->getDoctorId() === 1, "Doctor ID should be 1");
        assert($appointment->getStatus() === 'scheduled', "Status should be scheduled");
        assert($appointment->getType() === 'nfz', "Type should be nfz");
        
        echo "Appointment creation test passed\n";
    }
    
    public function testAppointmentStatus() {
        echo "Testing appointment status checks...\n";
        
        $scheduledAppointment = new Appointment([
            'id' => 1,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-30',
            'appointment_time' => '10:00:00',
            'status' => 'scheduled'
        ]);
        
        $completedAppointment = new Appointment([
            'id' => 2,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-20',
            'appointment_time' => '10:00:00',
            'status' => 'completed'
        ]);
        
        $cancelledAppointment = new Appointment([
            'id' => 3,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-25',
            'appointment_time' => '10:00:00',
            'status' => 'cancelled'
        ]);
        
        assert($scheduledAppointment->isScheduled() === true, "Scheduled appointment should be scheduled");
        assert($scheduledAppointment->isCompleted() === false, "Scheduled appointment should not be completed");
        
        assert($completedAppointment->isCompleted() === true, "Completed appointment should be completed");
        assert($completedAppointment->isScheduled() === false, "Completed appointment should not be scheduled");
        
        assert($cancelledAppointment->isCancelled() === true, "Cancelled appointment should be cancelled");
        
        echo "Appointment status test passed\n";
    }
    
    public function testAppointmentType() {
        echo "Testing appointment type checks...\n";
        
        $nfzAppointment = new Appointment([
            'id' => 1,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-30',
            'appointment_time' => '10:00:00',
            'type' => 'nfz'
        ]);
        
        $privateAppointment = new Appointment([
            'id' => 2,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-30',
            'appointment_time' => '11:00:00',
            'type' => 'private'
        ]);
        
        assert($nfzAppointment->isNFZ() === true, "NFZ appointment should be NFZ");
        assert($nfzAppointment->isPrivate() === false, "NFZ appointment should not be private");
        
        assert($privateAppointment->isPrivate() === true, "Private appointment should be private");
        assert($privateAppointment->isNFZ() === false, "Private appointment should not be NFZ");
        
        echo "Appointment type test passed\n";
    }
    
    public function testGetFullDate() {
        echo "Testing getFullDate method...\n";
        
        $appointment = new Appointment([
            'id' => 1,
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => '2024-12-30',
            'appointment_time' => '10:30:00'
        ]);
        
        $fullDate = $appointment->getFullDate();
        assert($fullDate === '2024-12-30 10:30:00', "Full date should be combined");
        
        echo "getFullDate test passed\n";
    }
    
    public function runAllTests() {
        echo "\nRunning Appointment Model Tests\n\n";
        
        try {
            $this->testAppointmentCreation();
            $this->testAppointmentStatus();
            $this->testAppointmentType();
            $this->testGetFullDate();
            
            echo "\nAll Appointment model tests passed!\n\n";
            return true;
        } catch (AssertionError $e) {
            echo "\nTest failed: " . $e->getMessage() . "\n\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
            return false;
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../bootstrap.php';
    
    $test = new AppointmentTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}