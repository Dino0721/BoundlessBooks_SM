let cardNumInput =
    document.querySelector('#cardNum')

cardNumInput.addEventListener('keyup', () => {
    let cNumber = cardNumInput.value
    cNumber = cNumber.replace(/\s/g, "")

    if (Number(cNumber)) {
        cNumber = cNumber.match(/.{1,4}/g)
        cNumber = cNumber.join(" ")
        cardNumInput.value = cNumber
    }
})

$(document).ready(function () {
    const originalTotalText = $("#totalPrice").text(); 
    const originalTotal = parseFloat(originalTotalText.replace(/[^0-9.]/g, ""));

    let discountedTotal = originalTotal;

    $("#discountCodeForm").on("submit", function (e) {
        e.preventDefault(); // Prevent form submission

        const discountCode = $("#discountCode").val().trim();
        $("#discountError").hide(); // Hide error message initially

        if (discountCode === "") {
            alert("Please enter a discount code.");
            return;
        }

        // Validate discount code via AJAX
        $.ajax({
            url: "handleDiscountCode.php",
            type: "POST",
            data: {
                discount_code: discountCode
            },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.valid) {
                    const discountPercentage = parseFloat(data.discount_percentage);
                    discountedTotal = originalTotal - (originalTotal * (discountPercentage / 100));

                    // Use .html() to allow HTML rendering
                    $("#totalPrice").html(`Total: <del>RM${originalTotal.toFixed(2)}</del> RM${discountedTotal.toFixed(2)}`);

                    alert(`Discount applied: ${discountPercentage}%`);
                } else {
                    $("#discountError").show();
                    $("#discountError").text("Invalid discount code.");
                }
            },
            error: function () {
                alert("An error occurred while validating the discount code.");
            }
        });
    });
});
