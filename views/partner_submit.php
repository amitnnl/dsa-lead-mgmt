<?php
/**
 * Partner Submit Lead View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-plus-circle"></i> Submit a New Lead</h2>
    <p>Provide the customer details below to initiate the loan process.</p>
</div>

<div class="card" style="max-width:800px; margin:0 auto">
    <div class="card-body">
        <form method="POST" action="index.php?page=partner&action=submit_lead">
            <?= Security::csrfField() ?>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Customer Full Name</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" placeholder="10-digit mobile number" required pattern="[0-9]{10}">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Loan Type</label>
                    <select name="loan_type" class="form-select" required>
                        <option value="">Select Loan Type...</option>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="Business Loan">Business Loan</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="LAP">Loan Against Property</option>
                        <option value="Car Loan">Car Loan</option>
                        <option value="Education Loan">Education Loan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Requested Loan Amount (₹)</label>
                    <input type="number" name="loan_amount" class="form-control" placeholder="e.g. 500000" required>
                </div>
            </div>

            <div style="margin-top:20px; padding:16px; background:rgba(34,110,84,0.05); border-radius:8px; border:1px solid rgba(34,110,84,0.1)">
                <p style="font-size:12px; color:#226e54; margin:0">
                    <i class="fas fa-shield-alt"></i> Your customer data is safe with us. Our team will contact the customer within 24 business hours to collect documents.
                </p>
            </div>

            <div style="margin-top:24px">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Submit Lead Now
                </button>
            </div>
        </form>
    </div>
</div>
