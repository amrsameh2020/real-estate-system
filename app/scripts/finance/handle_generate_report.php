<?php
// File: app/scripts/finance/handle_generate_report.php

/**
 * ===================================================================
 * SCRIPT: GENERATE FINANCIAL REPORT
 * ===================================================================
 * This script handles the generation of financial reports based on
 * user-selected criteria.
 *
 * ---
 *
 * 1. **Authentication Check:** Ensures the user is logged in and has the
 * 'Accountant' or 'SystemAdmin' role.
 * 2. **CSRF Validation:** Verifies the CSRF token to prevent cross-site
 * request forgery attacks.
 * 3. **Input Validation:** Checks that the report type and date range
 * are valid.
 * 4. **Data Fetching:** Retrieves the relevant financial data from the
 * database (invoices, expenses).
 * 5. **Report Generation:** Generates the report content (this is a
 * placeholder and can be expanded to create PDFs, CSVs, etc.).
 * 6. **Redirection/Display:** Redirects back with the report data or an
 * error message.
 */

// --- Step 1: Authentication Check ---
Auth::requireRole(['SystemAdmin', 'Accountant']);

// --- Step 2: CSRF Validation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Correctly call the verifyCSRF method
    if (!isset($_POST['csrf_token']) || !Security::verifyCSRF($_POST['csrf_token'])) {
        Session::flash('error', 'خطأ في التحقق من CSRF. حاول مرة أخرى.');
        redirect('?url=finance/reports');
    }

    // --- Step 3: Input Validation ---
    $report_type = $_POST['report_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (empty($report_type) || empty($start_date) || empty($end_date)) {
        Session::flash('error', 'يرجى تحديد نوع التقرير وتاريخ البدء والانتهاء.');
        redirect('?url=finance/reports');
    }

    // --- Step 4: Data Fetching (Example) ---
    // In a real application, you would query the database based on the criteria.
    // For this example, we'll just flash the data back.
    
    // --- Step 5: Report Generation ---
    $report_data = [
        'type' => htmlspecialchars($report_type),
        'start' => htmlspecialchars($start_date),
        'end' => htmlspecialchars($end_date)
    ];

    // --- Step 6: Redirection/Display ---
    Session::flash('success', 'تم إنشاء التقرير بنجاح.');
    Session::flash('report_data', $report_data);
    redirect('?url=finance/reports');

} else {
    // If accessed directly via GET, redirect
    redirect('?url=finance/reports');
}