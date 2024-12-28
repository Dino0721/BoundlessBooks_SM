<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

global $_db;

unset($_SESSION['discount_code_used']);
unset($_SESSION['discount_percentage']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment-Page</title>
    <link rel="stylesheet" href="PaymentStyles.css">
</head>

<body>

    <?php include_once 'fetchBillItems.php'; ?>

    <form id="discountCodeForm" method="POST">
        <div class="flex" style="height:50px;margin-bottom:10px;">
            <label for="discountCode">Discount Code</label>
            <input type="text" id="discountCode" name="discount_code" placeholder="Insert discount code">
            <small id="discountError" style="color: red; display: none; font-size: 10px;">Invalid discount code</small>
        </div>

        <input type="submit" value="Use discount Code" class="submit-discount-code">

        <div class="container">
    </form>

    <form id="paymentForm" action="paymentDone.php" method="POST">

        <div class="row">
            <div class="col">
                <h3 class="title">Payment</h3>

                <div class="inputBox">
                    <label for="name">Card Accepted:</label>
                    <div class="bankLogoContainer">
                        <img src="bankLogos/maybankLogo.png" alt="credit/debit card image">
                        <img src="bankLogos/publicbankLogo.png" alt="credit/debit card image">
                        <img src="bankLogos/hongleongbankLogo.png" alt="credit/debit card image">
                    </div>
                </div>

                <div class="inputBox">
                    <label for="">Bank Name:</label>
                    <select name="bank_name" id="bank_name">
                        <option value="" disabled selected>Choose bank name</option>
                        <option value="PUBLIC_BANK">Public Bank</option>
                        <option value="MAY_BANK">May Bank</option>
                        <option value="HONGLEONG_BANK">Hong Leong Bank</option>
                    </select>
                </div>

                <div class="inputBox">
                    <label for="cardNum">Credit Card Number:</label>
                    <input type="text" id="cardNum" name="card_number" placeholder="1111-2222-3333-4444" maxlength="19" required>
                </div>

                <div class="inputBox">
                    <label for="">Exp Month:</label>
                    <select name="exp_month" id="exp_month">
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
                        <select name="exp_year" id="exp_year">
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
                        <input type="number" id="cvv" name="cvv" placeholder="1234" required>
                    </div>
                </div>

            </div>
        </div>

        <input type="submit" value="Proceed to checkout" style="margin:10px; padding:8px;">
        <a href="../cartSide/CartPage.php">Back to cart</a>
    </form>

    </div>

</body>


<script src="../js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="paymentJs.js"></script>

</html>