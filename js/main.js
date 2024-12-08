// ============================================================================
// General Functions
// ============================================================================

document.getElementById('sign-up-btn').addEventListener('click', function() {
    const signUpForm = document.getElementById('sign-up-form');
    signUpForm.style.display = 'block'; // Show the form
    setTimeout(function() {
        signUpForm.classList.add('show'); // Trigger the slide-in animation
    }, 10); // Adding a slight delay to allow display change
});

document.getElementById('reset-btn').addEventListener('click', function() {
    const form = document.getElementById('login-form');
    
    form.querySelectorAll('input').forEach(input => input.value = '');
    
    const errorMessages = form.querySelectorAll('.error');
    errorMessages.forEach(error => error.textContent = '');
});


// ============================================================================
// Page Load (jQuery)
// ============================================================================
