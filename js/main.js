// ============================================================================
// General Functions
// ============================================================================

$(() => {

    // autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find('.input:first').focus();

    // reset
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });
});


// document.addEventListener("DOMContentLoaded", function () {
//     // Toggle visibility of sign-up form
//     document.getElementById("sign-up-btn").addEventListener("click", function () {
//         document.getElementById("login-form").style.display = "none"; // Hide login form
//         document.getElementById("sign-up-form").style.display = "block"; // Show sign-up form
//     });

//     // Optional: Add a back-to-login button functionality for better UX
//     const backToLoginButton = document.createElement("button");
//     backToLoginButton.textContent = "Back to Login";
//     backToLoginButton.style.marginTop = "10px";
//     backToLoginButton.style.padding = "10px 20px";
//     backToLoginButton.style.background = "#f44336";
//     backToLoginButton.style.color = "white";
//     backToLoginButton.style.border = "none";
//     backToLoginButton.style.cursor = "pointer";

//     // Append the back button to the sign-up form
//     document.getElementById("sign-up-form").appendChild(backToLoginButton);

//     // Add event listener for back-to-login button
//     backToLoginButton.addEventListener("click", function () {
//         document.getElementById("sign-up-form").style.display = "none"; // Hide sign-up form
//         document.getElementById("login-form").style.display = "block"; // Show login form
//     });
// });


// document.getElementById('reset-btn').addEventListener('click', function() {
//     const form = document.getElementById('login-form');
    
//     form.querySelectorAll('input').forEach(input => input.value = '');
    
//     const errorMessages = form.querySelectorAll('.error');
//     errorMessages.forEach(error => error.textContent = '');
// });


// ============================================================================
// Page Load (jQuery)
// ============================================================================
