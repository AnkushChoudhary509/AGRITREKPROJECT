<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Page Not Found | Agri-Trek</title>
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
    <i class="bi bi-map text-warning" style="font-size:72px;"></i>
    <h1 class="fw-bold mt-3">404</h1>
    <h4 class="text-muted">Page Not Found</h4>
    <p class="text-muted">The page you are looking for doesn't exist or has been moved.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-success">
        <i class="bi bi-house me-1"></i>Back to Dashboard
    </a>
</div>
</body>
</html>
