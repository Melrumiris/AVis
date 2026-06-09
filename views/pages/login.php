<div class="auth-container island-card">
    <h1 class="auth-title">Login</h1>
    <form id="login_form" method="POST">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    <div class="auth-links">
        Don't have an account? <a href="/register">Sign up</a>
    </div>
</div>