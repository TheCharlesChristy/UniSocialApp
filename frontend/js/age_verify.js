// Age verification
document.getElementById('birthDate').addEventListener('change', function() {
    const birthDate = new Date(this.value);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    const errorElement = document.getElementById('birthDate-error');
    
    if (age < 18) {
        errorElement.textContent = 'You must be at least 18 years old to create an account.';
        this.classList.add('invalid');
    } else {
        errorElement.textContent = '';
        this.classList.remove('invalid');
        this.classList.add('valid');
    }
});