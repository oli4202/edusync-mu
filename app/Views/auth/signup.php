<?php
/**
 * Signup page - auth/signup.php
 * $error - error message if signup failed
 * $name, $email - form values for repopulation
 */
?>
<div class="auth-container">
    <div class="auth-card">
        <h1>EduSync</h1>
        <p class="auth-subtitle">Join the Student Portal</p>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/signup" class="auth-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="student_id">Student ID (optional)</label>
                <input type="text" id="student_id" name="student_id" placeholder="e.g., MU123456">
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <p class="auth-link">
            Already have an account? <a href="/login">Sign in</a>
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
