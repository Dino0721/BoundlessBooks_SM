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


// ============================================================================
// Page Load (jQuery)
// ============================================================================
