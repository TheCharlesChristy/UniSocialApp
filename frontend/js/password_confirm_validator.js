// Password confirmation validation
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const errorElement = document.getElementById('confirmPassword-error');

    if (confirmPassword && password !== confirmPassword) {
        errorElement.textContent = 'Passwords do not match.';
        this.classList.add('invalid');
        this.classList.remove('valid');
    } else if (confirmPassword) {
        errorElement.textContent = '';
        this.classList.add('valid');
        this.classList.remove('invalid');
    }
});