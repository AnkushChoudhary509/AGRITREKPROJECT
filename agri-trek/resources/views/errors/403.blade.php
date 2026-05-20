<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Access Denied | Agri-Trek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#1b5e20,#43a047); min-height:100vh;
               display:flex;align-items:center;justify-content:center; }
        .error-card { background:#fff;border-radius:20px;padding:50px;text-align:center;max-width:450px; }
    </style>
</head>
<body>
<div class="error-card shadow-lg">
    <i class="bi bi-shield-lock-fill text-danger" style="font-size:72px;"></i>
    <h1 class="fw-bold mt-3">403</h1>
    <h4 class="text-muted">Access Denied</h4>
    <p class="text-muted">You do not have permission to access this page. Admin privileges are required.</p>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
        <i class="bi bi-arrow-left me-1"></i>Go Back
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-success">
        <i class="bi bi-house me-1"></i>Dashboard
    </a>
</div>
</body>
</html>
