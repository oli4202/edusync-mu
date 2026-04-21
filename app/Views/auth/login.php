<?php
/**
 * Login page - auth/login.php
 * $error - error message if login failed
 * $flash - flash message from session
 */
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>EduSync</h1>
        <p class="auth-subtitle">Student Portal — Metropolitan University Sylhet</p>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($flash) && $flash): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/login" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p class="auth-link">
            Don't have an account? <a href="/signup">Create one</a>
        </p>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0a0e1a 0%, #111827 100%);
}

.auth-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 12px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.auth-card h1 {
    font-size: 32px;
    margin-bottom: 8px;
    color: #22d3ee;
}

.auth-subtitle {
    color: #64748b;
    margin-bottom: 30px;
    font-size: 14px;
}

.auth-form {
    margin: 30px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #e2e8f0;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 10px 12px;
    background: #0f172a;
    border: 1px solid #1e2d45;
    border-radius: 6px;
    color: #e2e8f0;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #22d3ee;
    background: #0a0e1a;
}

.btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: #22d3ee;
    color: #0a0e1a;
}

.btn-primary:hover {
    background: #06b6d4;
}

.auth-link {
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.auth-link a {
    color: #22d3ee;
    text-decoration: none;
}

.auth-link a:hover {
    text-decoration: underline;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-error {
    background: rgba(244, 63, 94, 0.1);
    color: #f87171;
    border: 1px solid #f87171;
}

.alert-success {
    background: rgba(52, 211, 153, 0.1);
    color: #34d399;
    border: 1px solid #34d399;
}
</style>
