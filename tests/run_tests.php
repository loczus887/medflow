#!/usr/bin/env php
<?php

/**
 * Test Runner - Runs all unit tests
 */

require_once __DIR__ . '/bootstrap.php';

echo "\n";
echo "MedFlow Test Suite Runner\n";
echo "\n";

$testFiles = [
    __DIR__ . '/unit/ValidationServiceTest.php',
    __DIR__ . '/unit/UserTest.php',
    __DIR__ . '/unit/AppointmentTest.php'
];

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($testFiles as $testFile) {
    if (!file_exists($testFile)) {
        echo "Test file not found: $testFile\n";
        continue;
    }
    
    require_once $testFile;
    
    $className = basename($testFile, '.php');
    
    if (!class_exists($className)) {
        echo "Test class not found: $className\n";
        continue;
    }
    
    $test = new $className();
    
    if (!method_exists($test, 'runAllTests')) {
        echo "runAllTests method not found in: $className\n";
        continue;
    }
    
    $totalTests++;
    
    $success = $test->runAllTests();
    
    if ($success) {
        $passedTests++;
    } else {
        $failedTests++;
    }
}

echo "\n";

echo "Test Summary:\n";
echo "Total test suites: " . str_pad($totalTests, 19) . "\n";
echo "Passed:            " . str_pad($passedTests, 19) . "\n";
echo "Failed:            " . str_pad($failedTests, 19) . "\n";
echo "\n";

if ($failedTests > 0) {
    echo "Some tests failed!\n\n";
    exit(1);
} else {
    echo "All tests passed successfully!\n\n";
    exit(0);
}