<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

// forgott
global $_db;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Online Payment-Page</title>
    <link rel="stylesheet" href="PaymentStyles.css">
</head>

<body>

    <?php include_once 'fetchBillItems.php'; ?>

    <div class="container">

        <form action="#">

            <div class="row">

                <!-- <div class="col">
                    <h3 class="title">
                        Billing Address
                    </h3>

                    <div class="inputBox">
                        <label for="name">
                            Full Name:
                        </label>
                        <input type="text" id="name"
                            placeholder="Enter your full name" name="fullname"
                            required>
                    </div>

                    <div class="inputBox">
                        <label for="email">
                            Email:
                        </label>
                        <input type="text" id="email"
                            placeholder="Enter email address"
                            name="email"
                            required>
                    </div>

                    <div class="inputBox">
                        <label for="address">
                            Address:
                        </label>
                        <input type="text" id="address"
                            placeholder="Enter address"
                            name="address"
                            required>
                    </div>

                    <div class="inputBox">
                        <label for="city">
                            City:
                        </label>
                        <input type="text" id="city"
                            placeholder="Enter city"
                            name="city"
                            required>
                    </div>

                    <div class="flex">

                        <div class="inputBox">
                            <label for="state">
                                State:
                            </label>
                            <input type="text" id="state"
                                placeholder="Enter state"
                                required>
                        </div>

                        <div class="inputBox">
                            <label for="zip">
                                Zip Code:
                            </label>
                            <input type="number" id="zip"
                                placeholder="123 456"
                                required>
                        </div>

                    </div>

                </div> !-->
                <div class="col">
                    <h3 class="title">Payment</h3>

                    <div class="inputBox">
                        <label for="name">
                            Card Accepted:
                        </label>
                        <div class="bankLogoContainer">
                            <img src="bankLogos/maybankLogo.png"
                                alt="credit/debit card image">
                            <img src="bankLogos/publicbankLogo.png"
                                alt="credit/debit card image">
                            <img src="bankLogos/hongleongbankLogo.png"
                                alt="credit/debit card image">
                        </div>
                    </div>

                    <div class="inputBox">
                        <label for="">Bank Name:</label>
                        <select name="" id="">
                            <option value="" disabled selected>Choose bank name</option>
                            <option value="PUBLIC_BANK">Public Bank</option>
                            <option value="MAY_BANK">May Bank</option>
                            <option value="HONGLEONG_BANK">Hong Leong Bank</option>
                        </select>
                    </div>

                    <div class="inputBox">
                        <label for="cardNum">
                            Credit Card Number:
                        </label>
                        <input type="text" id="cardNum"
                            placeholder="1111-2222-3333-4444"
                            maxlength="19" required>
                    </div>

                    <div class="inputBox">
                        <label for="">Exp Month:</label>
                        <select name="" id="">
                            <option value="">Choose month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>


                    <div class="flex">
                        <div class="inputBox">
                            <label for="">Exp Year:</label>
                            <select name="" id="">
                                <option value="">Choose Year</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>

                        <div class="inputBox">
                            <label for="cvv">CVV</label>
                            <input type="number" id="cvv"
                                placeholder="1234" required>
                        </div>
                    </div>

                </div>

            </div>

            <a href='paymentDone.php'>Proceed to checkout</a>

            <a href="../cartSide/CartPage.php">Back to cart</a>
                </form>

    </div>

</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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
</script>

</html>