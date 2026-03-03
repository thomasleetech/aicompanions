<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Calculator - Companion Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0f0f0f;--bg2:#1a1a1a;--bg3:#252525;--text:#fff;--text2:#888;--accent:#10b981;--red:#ef4444;--border:#333}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);padding:24px;min-height:100vh}
        .container{max-width:1200px;margin:0 auto}
        h1{font-size:28px;margin-bottom:8px}
        .subtitle{color:var(--text2);margin-bottom:32px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:24px}
        .card h2{font-size:16px;color:var(--text2);margin-bottom:20px;text-transform:uppercase;letter-spacing:1px}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:13px;color:var(--text2);margin-bottom:6px}
        .form-group input{width:100%;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px}
        .form-group input:focus{outline:none;border-color:var(--accent)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .scenarios{display:flex;gap:8px;margin-bottom:24px}
        .scenario{padding:10px 20px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--text2);cursor:pointer;font-size:13px}
        .scenario:hover,.scenario.active{background:var(--accent);color:#000;border-color:var(--accent)}
        .result{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)}
        .result:last-child{border-bottom:none}
        .result-label{color:var(--text2)}
        .result-value{font-weight:600;font-family:monospace;font-size:15px}
        .result-value.green{color:var(--accent)}
        .result-value.red{color:var(--red)}
        .highlight{background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(16,185,129,0.05));border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:32px;text-align:center;margin-top:24px}
        .highlight-value{font-size:48px;font-weight:700;color:var(--accent)}
        .highlight-label{color:var(--text2);margin-top:4px}
        .metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:20px}
        .metric{background:var(--bg);border-radius:8px;padding:16px;text-align:center}
        .metric-value{font-size:20px;font-weight:600}
        .metric-label{font-size:11px;color:var(--text2);margin-top:4px}
        .projection{margin-top:24px}
        .projection-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
        .projection-item{background:var(--bg);border-radius:8px;padding:16px;text-align:center}
        .back-link{color:var(--accent);text-decoration:none;display:inline-block;margin-bottom:24px}
        @media(max-width:768px){.grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.metrics,.projection-row{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" class="back-link">← Back to Admin</a>
        <h1>Revenue & Profit Calculator</h1>
        <p class="subtitle">Model potential earnings based on user growth and pricing</p>
        
        <div class="scenarios">
            <button class="scenario active" onclick="loadScenario('conservative',this)">Conservative</button>
            <button class="scenario" onclick="loadScenario('moderate',this)">Moderate</button>
            <button class="scenario" onclick="loadScenario('optimistic',this)">Optimistic</button>
        </div>
        
        <div class="grid">
            <div>
                <div class="card">
                    <h2>User Metrics</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monthly Active Users</label>
                            <input type="number" id="mau" value="1000" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>Monthly Growth %</label>
                            <input type="number" id="growth" value="15" oninput="calculate()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Free → Paid %</label>
                            <input type="number" id="conversion" value="5" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>Monthly Churn %</label>
                            <input type="number" id="churn" value="8" oninput="calculate()">
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top:24px">
                    <h2>Pricing</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monthly Sub ($)</label>
                            <input type="number" id="monthlyPrice" value="29" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>Hourly Rate ($)</label>
                            <input type="number" id="hourlyPrice" value="25" oninput="calculate()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Avg Upgrade ($)</label>
                            <input type="number" id="avgUpgrade" value="15" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>% Buy Upgrades</label>
                            <input type="number" id="upgradeRate" value="30" oninput="calculate()">
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top:24px">
                    <h2>Costs</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>API Cost/User ($)</label>
                            <input type="number" id="apiCost" value="2.50" step="0.10" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>Server/Mo ($)</label>
                            <input type="number" id="serverCost" value="200" oninput="calculate()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Payment Fee %</label>
                            <input type="number" id="paymentFee" value="2.9" step="0.1" oninput="calculate()">
                        </div>
                        <div class="form-group">
                            <label>Other Costs ($)</label>
                            <input type="number" id="otherCosts" value="100" oninput="calculate()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="card">
                    <h2>Monthly Revenue</h2>
                    <div class="result"><span class="result-label">Paying Users</span><span class="result-value" id="payingUsers">50</span></div>
                    <div class="result"><span class="result-label">Subscription Revenue</span><span class="result-value green" id="subRev">$1,450</span></div>
                    <div class="result"><span class="result-label">Upgrade Revenue</span><span class="result-value green" id="upgradeRev">$225</span></div>
                    <div class="result"><span class="result-label">Total Revenue</span><span class="result-value green" id="totalRev">$1,675</span></div>
                </div>
                
                <div class="card" style="margin-top:24px">
                    <h2>Monthly Costs</h2>
                    <div class="result"><span class="result-label">API Costs</span><span class="result-value red" id="apiCostTotal">-$125</span></div>
                    <div class="result"><span class="result-label">Payment Processing</span><span class="result-value red" id="paymentTotal">-$49</span></div>
                    <div class="result"><span class="result-label">Server & Other</span><span class="result-value red" id="otherTotal">-$300</span></div>
                    <div class="result"><span class="result-label">Total Costs</span><span class="result-value red" id="totalCosts">-$474</span></div>
                </div>
                
                <div class="highlight">
                    <div class="highlight-value" id="profit">$1,201</div>
                    <div class="highlight-label">Monthly Net Profit (<span id="margin">72%</span> margin)</div>
                </div>
                
                <div class="metrics">
                    <div class="metric"><div class="metric-value" id="arpu">$33.50</div><div class="metric-label">ARPU</div></div>
                    <div class="metric"><div class="metric-value" id="ltv">$418</div><div class="metric-label">LTV</div></div>
                    <div class="metric"><div class="metric-value" id="cac">$139</div><div class="metric-label">Target CAC (3:1)</div></div>
                </div>
            </div>
        </div>
        
        <div class="card projection">
            <h2>12-Month Projection</h2>
            <div class="projection-row">
                <div class="projection-item"><div class="metric-value" id="m3">-</div><div class="metric-label">3 Mo Users</div></div>
                <div class="projection-item"><div class="metric-value" id="m6">-</div><div class="metric-label">6 Mo Users</div></div>
                <div class="projection-item"><div class="metric-value" id="m12">-</div><div class="metric-label">12 Mo Users</div></div>
                <div class="projection-item"><div class="metric-value" id="yearProfit" style="color:var(--accent)">-</div><div class="metric-label">Year 1 Profit</div></div>
            </div>
        </div>
    </div>
    
    <script>
    const scenarios = {
        conservative: {mau:500,growth:10,conversion:3,churn:12,apiCost:3},
        moderate: {mau:1000,growth:15,conversion:5,churn:8,apiCost:2.5},
        optimistic: {mau:2500,growth:25,conversion:8,churn:5,apiCost:2}
    };
    
    function loadScenario(name,btn) {
        document.querySelectorAll('.scenario').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const s = scenarios[name];
        document.getElementById('mau').value = s.mau;
        document.getElementById('growth').value = s.growth;
        document.getElementById('conversion').value = s.conversion;
        document.getElementById('churn').value = s.churn;
        document.getElementById('apiCost').value = s.apiCost;
        calculate();
    }
    
    function calculate() {
        const mau = +document.getElementById('mau').value || 0;
        const growth = +document.getElementById('growth').value / 100;
        const conversion = +document.getElementById('conversion').value / 100;
        const churn = +document.getElementById('churn').value / 100;
        const monthlyPrice = +document.getElementById('monthlyPrice').value || 0;
        const avgUpgrade = +document.getElementById('avgUpgrade').value || 0;
        const upgradeRate = +document.getElementById('upgradeRate').value / 100;
        const apiCost = +document.getElementById('apiCost').value || 0;
        const serverCost = +document.getElementById('serverCost').value || 0;
        const paymentFee = +document.getElementById('paymentFee').value / 100;
        const otherCosts = +document.getElementById('otherCosts').value || 0;
        
        const paying = Math.round(mau * conversion);
        const subRev = paying * monthlyPrice;
        const upgradeRev = paying * upgradeRate * avgUpgrade;
        const totalRev = subRev + upgradeRev;
        
        const apiTotal = paying * apiCost;
        const paymentTotal = totalRev * paymentFee;
        const otherTotal = serverCost + otherCosts;
        const totalCosts = apiTotal + paymentTotal + otherTotal;
        
        const profit = totalRev - totalCosts;
        const margin = totalRev > 0 ? (profit / totalRev * 100) : 0;
        
        const arpu = paying > 0 ? totalRev / paying : 0;
        const avgLife = churn > 0 ? 1 / churn : 12;
        const ltv = arpu * avgLife;
        const cac = ltv / 3;
        
        document.getElementById('payingUsers').textContent = paying.toLocaleString();
        document.getElementById('subRev').textContent = '$' + subRev.toLocaleString();
        document.getElementById('upgradeRev').textContent = '$' + upgradeRev.toLocaleString();
        document.getElementById('totalRev').textContent = '$' + totalRev.toLocaleString();
        
        document.getElementById('apiCostTotal').textContent = '-$' + Math.round(apiTotal).toLocaleString();
        document.getElementById('paymentTotal').textContent = '-$' + Math.round(paymentTotal).toLocaleString();
        document.getElementById('otherTotal').textContent = '-$' + otherTotal.toLocaleString();
        document.getElementById('totalCosts').textContent = '-$' + Math.round(totalCosts).toLocaleString();
        
        document.getElementById('profit').textContent = '$' + Math.round(profit).toLocaleString();
        document.getElementById('margin').textContent = margin.toFixed(0) + '%';
        
        document.getElementById('arpu').textContent = '$' + arpu.toFixed(2);
        document.getElementById('ltv').textContent = '$' + Math.round(ltv);
        document.getElementById('cac').textContent = '$' + Math.round(cac);
        
        // 12-month projection
        let users = mau, yearProfit = 0;
        for (let m = 1; m <= 12; m++) {
            users = users * (1 + growth) * (1 - churn);
            const mPaying = users * conversion;
            const mRev = mPaying * monthlyPrice + mPaying * upgradeRate * avgUpgrade;
            const mCost = mPaying * apiCost + mRev * paymentFee + serverCost + otherCosts;
            yearProfit += mRev - mCost;
            if (m === 3) document.getElementById('m3').textContent = Math.round(users).toLocaleString();
            if (m === 6) document.getElementById('m6').textContent = Math.round(users).toLocaleString();
            if (m === 12) document.getElementById('m12').textContent = Math.round(users).toLocaleString();
        }
        document.getElementById('yearProfit').textContent = '$' + Math.round(yearProfit).toLocaleString();
    }
    
    calculate();
    </script>
</body>
</html>
