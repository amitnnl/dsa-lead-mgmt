<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMI Calculator - Used Vehicle Loan | DSA LeadFlow</title>
    <meta name="description" content="Calculate your monthly EMI for used car loans, used bike loans, and vehicle finance. Instant results with no login required.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #6366f1; --primary-hover: #818cf8;
            --bg: #0a0a1a; --surface: #12122a; --surface-2: #1a1a3e;
            --text: #e2e8f0; --text-dim: #94a3b8; --text-muted: #64748b;
            --border: rgba(255,255,255,0.07);
            --success: #10b981; --warning: #f59e0b;
        }
        body { font-family:'Inter',system-ui,sans-serif; background:var(--bg); color:var(--text); line-height:1.6; min-height:100vh; }
        
        .hero {
            text-align:center; padding:60px 20px 40px;
            background:linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.08));
        }
        .hero h1 { font-size:clamp(28px, 5vw, 42px); font-weight:800; letter-spacing:-1px; margin-bottom:12px; }
        .hero h1 i { color:var(--warning); }
        .hero p { color:var(--text-dim); font-size:16px; max-width:600px; margin:0 auto; }
        
        .calculator-wrapper {
            max-width:900px; margin:-20px auto 60px; padding:0 20px;
            display:grid; grid-template-columns:1fr 1fr; gap:24px;
        }
        
        .calc-card {
            background:var(--surface); border:1px solid var(--border); border-radius:16px;
            padding:32px; position:relative; overflow:hidden;
        }
        .calc-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:3px;
            background:linear-gradient(90deg, var(--primary), #8b5cf6);
        }
        .calc-card h2 { font-size:16px; font-weight:700; margin-bottom:24px; display:flex; align-items:center; gap:10px; }
        .calc-card h2 i { color:var(--primary); }
        
        .field { margin-bottom:20px; }
        .field label { display:block; font-size:12px; font-weight:600; color:var(--text-dim); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
        .field input, .field select {
            width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border);
            border-radius:10px; color:var(--text); font-size:15px; font-family:inherit; outline:none;
            transition:all 0.25s ease;
        }
        .field input:focus, .field select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,0.25); }
        .field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .field small { font-size:11px; color:var(--text-muted); margin-top:4px; display:block; }
        
        .range-display { display:flex; justify-content:space-between; margin-top:4px; }
        .range-display span { font-size:11px; color:var(--text-muted); }
        input[type="range"] {
            -webkit-appearance:none; width:100%; height:6px; background:var(--surface-2);
            border-radius:3px; outline:none; margin-top:8px;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance:none; width:22px; height:22px; border-radius:50%;
            background:linear-gradient(135deg, var(--primary), #8b5cf6);
            cursor:pointer; box-shadow:0 2px 8px rgba(99,102,241,0.4);
        }
        
        /* Results */
        .result-card { display:flex; flex-direction:column; justify-content:center; }
        .emi-display {
            text-align:center; padding:32px 0; margin-bottom:24px;
            background:linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.05));
            border-radius:16px; border:1px solid rgba(99,102,241,0.15);
        }
        .emi-display .emi-label { font-size:13px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; font-weight:600; }
        .emi-display .emi-value { font-size:48px; font-weight:800; color:var(--primary-hover); margin:8px 0; letter-spacing:-2px; }
        .emi-display .emi-sub { font-size:13px; color:var(--text-muted); }
        
        .breakdown { display:flex; flex-direction:column; gap:12px; }
        .breakdown-item {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 18px; background:var(--surface-2); border-radius:10px;
        }
        .breakdown-item .bl { font-size:13px; color:var(--text-dim); display:flex; align-items:center; gap:8px; }
        .breakdown-item .bl i { font-size:14px; }
        .breakdown-item .bv { font-size:15px; font-weight:700; }
        .bv-green { color:var(--success); }
        .bv-amber { color:var(--warning); }
        .bv-purple { color:var(--primary-hover); }
        
        .apply-btn {
            display:block; width:100%; padding:16px; margin-top:24px; text-align:center;
            background:linear-gradient(135deg, var(--primary), #8b5cf6); color:#fff;
            border:none; border-radius:12px; font-size:16px; font-weight:700;
            cursor:pointer; text-decoration:none; transition:all 0.25s ease;
            box-shadow:0 4px 20px rgba(99,102,241,0.3);
        }
        .apply-btn:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(99,102,241,0.4); }
        
        .footer {
            text-align:center; padding:30px 20px; color:var(--text-muted); font-size:12px;
            border-top:1px solid var(--border);
        }
        .footer a { color:var(--primary-hover); }
        
        @media (max-width:768px) {
            .calculator-wrapper { grid-template-columns:1fr; }
            .hero { padding:40px 20px 30px; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <h1><i class="fas fa-car"></i> Vehicle Loan EMI Calculator</h1>
        <p>Calculate your monthly EMI instantly for Used Cars, Bikes & Commercial Vehicles. No login required.</p>
    </div>

    <div class="calculator-wrapper">
        <!-- Input Card -->
        <div class="calc-card">
            <h2><i class="fas fa-sliders-h"></i> Loan Parameters</h2>
            
            <div class="field">
                <label>Vehicle Type</label>
                <select id="vehicleType">
                    <option value="car">Used Car</option>
                    <option value="bike">Used Bike / Scooter</option>
                    <option value="commercial">Commercial Vehicle</option>
                </select>
            </div>
            
            <div class="field">
                <label>Loan Amount (₹)</label>
                <input type="number" id="loanAmount" value="500000" min="50000" max="5000000" step="10000">
                <input type="range" id="loanAmountRange" min="50000" max="5000000" step="10000" value="500000">
                <div class="range-display"><span>₹50K</span><span>₹50L</span></div>
            </div>
            
            <div class="field-row">
                <div class="field">
                    <label>Interest Rate (% p.a.)</label>
                    <input type="number" id="interestRate" value="12" min="7" max="24" step="0.25">
                </div>
                <div class="field">
                    <label>Tenure (Years)</label>
                    <select id="tenure">
                        <option value="1">1 Year</option>
                        <option value="2">2 Years</option>
                        <option value="3" selected>3 Years</option>
                        <option value="4">4 Years</option>
                        <option value="5">5 Years</option>
                        <option value="7">7 Years</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Down Payment (₹)</label>
                <input type="number" id="downPayment" value="0" min="0" step="5000">
                <small>Reduce your EMI by paying upfront</small>
            </div>
        </div>

        <!-- Result Card -->
        <div class="calc-card result-card">
            <div class="emi-display">
                <div class="emi-label">Your Monthly EMI</div>
                <div class="emi-value" id="emiValue">₹16,607</div>
                <div class="emi-sub" id="emiSub">for 36 months</div>
            </div>

            <div class="breakdown">
                <div class="breakdown-item">
                    <span class="bl"><i class="fas fa-wallet"></i> Principal</span>
                    <span class="bv bv-green" id="totalPrincipal">₹5,00,000</span>
                </div>
                <div class="breakdown-item">
                    <span class="bl"><i class="fas fa-percentage"></i> Total Interest</span>
                    <span class="bv bv-amber" id="totalInterest">₹97,852</span>
                </div>
                <div class="breakdown-item">
                    <span class="bl"><i class="fas fa-calculator"></i> Total Payable</span>
                    <span class="bv bv-purple" id="totalPayable">₹5,97,852</span>
                </div>
            </div>

            <a href="index.php?page=login" class="apply-btn">
                <i class="fas fa-paper-plane"></i> Apply for Vehicle Loan Now
            </a>
        </div>
    </div>

    <div class="footer">
        Powered by <a href="index.php">DSA LeadFlow</a> &mdash; Your trusted vehicle finance partner.
    </div>

    <script>
    const $ = id => document.getElementById(id);
    const fmt = n => '₹' + n.toLocaleString('en-IN');

    function calculateEMI() {
        const P = Math.max(0, parseFloat($('loanAmount').value) - parseFloat($('downPayment').value || 0));
        const annualRate = parseFloat($('interestRate').value);
        const years = parseInt($('tenure').value);
        const n = years * 12;
        const r = annualRate / 12 / 100;

        let emi, totalPayable, totalInterest;

        if (r === 0) {
            emi = P / n;
            totalPayable = P;
            totalInterest = 0;
        } else {
            emi = P * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
            totalPayable = emi * n;
            totalInterest = totalPayable - P;
        }

        $('emiValue').textContent = fmt(Math.round(emi));
        $('emiSub').textContent = `for ${n} months at ${annualRate}% p.a.`;
        $('totalPrincipal').textContent = fmt(Math.round(P));
        $('totalInterest').textContent = fmt(Math.round(totalInterest));
        $('totalPayable').textContent = fmt(Math.round(totalPayable));
    }

    // Sync range slider with input
    $('loanAmountRange').addEventListener('input', function() {
        $('loanAmount').value = this.value;
        calculateEMI();
    });
    $('loanAmount').addEventListener('input', function() {
        $('loanAmountRange').value = this.value;
        calculateEMI();
    });

    // Auto-set interest rate based on vehicle type
    $('vehicleType').addEventListener('change', function() {
        const rates = { car: 12, bike: 14, commercial: 11 };
        $('interestRate').value = rates[this.value] || 12;
        calculateEMI();
    });

    // Recalculate on any input change
    ['interestRate', 'tenure', 'downPayment'].forEach(id => {
        $(id).addEventListener('input', calculateEMI);
        $(id).addEventListener('change', calculateEMI);
    });

    // Initial calculation
    calculateEMI();
    </script>
</body>
</html>
