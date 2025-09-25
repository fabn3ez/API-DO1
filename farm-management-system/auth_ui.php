<?php // public/auth_ui.php ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Farm System — Auth</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f4f6f9; }
    .card { border-radius:12px; box-shadow:0 6px 20px rgba(22,28,45,0.08); }
    .form-section { display:none; }
    .form-section.active { display:block; }
    pre#output { max-height:180px; overflow:auto; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card p-4">
          <h3 class="text-center mb-3">Farm Management — Auth</h3>
          <ul class="nav nav-tabs mb-3" id="tabs">
            <li class="nav-item"><button class="nav-link active" data-target="#login">Login</button></li>
            <li class="nav-item"><button class="nav-link" data-target="#register">Register</button></li>
            <li class="nav-item"><button class="nav-link" data-target="#verify">Verify 2FA</button></li>
            <li class="nav-item"><button class="nav-link" data-target="#enable">Enable 2FA</button></li>
          </ul>

          <div id="login" class="form-section active">
            <form id="form-login">
              <div class="mb-2"><label>Username</label><input name="username" class="form-control" required></div>
              <div class="mb-2"><label>Password</label><input name="password" type="password" class="form-control" required></div>
              <button class="btn btn-primary w-100">Login</button>
            </form>
          </div>

          <div id="register" class="form-section">
            <form id="form-register">
              <div class="mb-2"><label>Username</label><input name="username" class="form-control" required></div>
              <div class="mb-2"><label>Email</label><input name="email" type="email" class="form-control" required></div>
              <div class="mb-2"><label>Password</label><input name="password" type="password" class="form-control" required></div>
              <div class="mb-2"><label>Phone (optional)</label><input name="phone" class="form-control"></div>
              <button class="btn btn-success w-100">Register</button>
            </form>
          </div>

          <div id="verify" class="form-section">
            <form id="form-verify">
              <div class="mb-2"><label>Temp Session ID</label><input name="temp_session_id" class="form-control" required></div>
              <div class="mb-2"><label>2FA Code</label><input name="token" class="form-control" required></div>
              <button class="btn btn-warning w-100">Verify</button>
            </form>
          </div>

          <div id="enable" class="form-section">
            <form id="form-enable">
              <div class="mb-2"><label>User ID</label><input name="user_id" type="number" class="form-control" required></div>
              <div class="mb-2"><label>Method</label>
                <select name="method" class="form-control">
                  <option value="email">Email</option>
                  <option value="sms">SMS</option>
                </select>
              </div>
              <div class="mb-2"><label>Phone (if SMS)</label><input name="phone" class="form-control"></div>
              <button class="btn btn-info w-100">Enable 2FA</button>
            </form>
          </div>

          <hr>
          <div>
            <label class="form-label">Response (debug)</label>
            <pre id="output" class="bg-light p-2 rounded small"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.querySelectorAll('#tabs button').forEach(b=>{
  b.addEventListener('click', ()=> {
    document.querySelectorAll('#tabs button').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('.form-section').forEach(s=>s.classList.remove('active'));
    b.classList.add('active');
    document.querySelector(b.dataset.target).classList.add('active');
  });
});

// helper
const out = v => document.getElementById('output').textContent = JSON.stringify(v, null, 2);

document.getElementById('form-register').addEventListener('submit', async e=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  try { const res = await axios.post('/register', data); out(res.data); } catch (err) { out(err.response?.data || err.message); }
});

document.getElementById('form-login').addEventListener('submit', async e=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  try { const res = await axios.post('/login', data); out(res.data); } catch (err) { out(err.response?.data || err.message); }
});

document.getElementById('form-verify').addEventListener('submit', async e=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  try { const res = await axios.post('/verify-2fa', data); out(res.data); } catch (err) { out(err.response?.data || err.message); }
});

document.getElementById('form-enable').addEventListener('submit', async e=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  try { const res = await axios.post('/enable-2fa', data); out(res.data); } catch (err) { out(err.response?.data || err.message); }
});
</script>
</body>
</html>
