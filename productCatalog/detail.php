<?php
include '../pageFormat/base.php';

// Define the title here
$_title = 'Default Title';  // You can use a default or dynamic value

// Fetch the product details based on the `book_id` from the URL
$bookId = $_GET['book_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;

try {
    // Prepare the SQL statement
    $stmt = $_db->prepare("SELECT * FROM book_item WHERE book_id = :book_id");
    $stmt->execute([':book_id' => $bookId]);

    // Fetch the result as an associative array
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the book exists
    if (!$book) {
        die('Product not found!');
    }

    // Check if the book is already in the user's wishlist
    $wishlistStmt = $_db->prepare("SELECT COUNT(*) FROM wishlist WHERE book_id = :book_id AND user_id = :user_id");
    $wishlistStmt->execute([':book_id' => $bookId, ':user_id' => $userId]);
    $isInWishlist = $wishlistStmt->fetchColumn() > 0;

    // Set the page title dynamically
    $_title = htmlspecialchars($book['book_name']); // Ensure proper escaping

} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

include '../pageFormat/head.php';  // Include head.php after setting the title
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamically set the page title -->
    <title><?php echo $_title; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles can go here */
    </style>
</head>

<body>
    <div class="product-detail">
        <img
            src="../images/<?= htmlspecialchars(!empty($book['book_photo']) ? $book['book_photo'] : 'default.jpg') ?>"
            alt="<?= htmlspecialchars($book['book_name']) ?>"
            class="book-detail-image">
        <h1>
            <?= htmlspecialchars($book['book_name']) ?> |
            <span class="category" style="color: grey;"><?= htmlspecialchars($book['book_category']) ?></span>
        </h1>
        <p><?= htmlspecialchars($book['book_desc']) ?></p>
        <p class="price">Price: $<?= number_format($book['book_price'], 2) ?></p>

        <?php if ($book['book_status'] === 'DISABLED'): ?>
            <p class="unavailable">This book is currently unavailable.</p>
        <?php endif; ?>

        <button id="addToCartButton" class="<?= $book['book_status'] === 'DISABLED' ? 'disabled' : '' ?>">
            <?= $book['book_status'] === 'DISABLED' ? 'Unavailable' : 'Add to Cart' ?>
        </button>

        <button id="backToListingButton">Back to Listing</button>
        <!-- Wishlist button -->
        <div id="main-content" style="display: inline-block; margin-left: 10px;">
            <form action="../wishList/wishList.php" method="POST" id="wishlistForm">
                <input type="checkbox" id="checkbox" name="wishList" value="added" <?php echo $isInWishlist ? 'checked' : ''; ?>>
                <label for="checkbox">
                    <svg id="heart-svg" viewBox="467 392 58 57" xmlns="http://www.w3.org/2000/svg">
                        <g id="Group" fill="none" fill-rule="evenodd" transform="translate(467 392)">
                            <path d="M29.144 20.773c-.063-.13-4.227-8.67-11.44-2.59C7.63 28.795 28.94 43.256 29.143 43.394c.204-.138 21.513-14.6 11.44-25.213-7.214-6.08-11.377 2.46-11.44 2.59z" id="heart" fill="#AAB8C2"/>
                            <circle id="main-circ" fill="#E2264D" opacity="0" cx="29.5" cy="29.5" r="1.5"/>
                            <g id="grp7" opacity="0" transform="translate(7 6)">
                                <circle id="oval1" fill="#9CD8C3" cx="2" cy="6" r="2"/>
                                <circle id="oval2" fill="#8CE8C3" cx="5" cy="2" r="2"/>
                            </g>
                            <g id="grp6" opacity="0" transform="translate(0 28)">
                                <circle id="oval1" fill="#CC8EF5" cx="2" cy="7" r="2"/>
                                <circle id="oval2" fill="#91D2FA" cx="3" cy="2" r="2"/>
                            </g>
                            <g id="grp3" opacity="0" transform="translate(52 28)">
                                <circle id="oval2" fill="#9CD8C3" cx="2" cy="7" r="2"/>
                                <circle id="oval1" fill="#8CE8C3" cx="4" cy="2" r="2"/>
                            </g>
                            <g id="grp2" opacity="0" transform="translate(44 6)">
                                <circle id="oval2" fill="#CC8EF5" cx="5" cy="6" r="2"/>
                                <circle id="oval1" fill="#CC8EF5" cx="2" cy="2" r="2"/>
                            </g>
                            <g id="grp5" opacity="0" transform="translate(14 50)">
                                <circle id="oval1" fill="#91D2FA" cx="6" cy="5" r="2"/>
                                <circle id="oval2" fill="#91D2FA" cx="2" cy="2" r="2"/>
                            </g>
                            <g id="grp4" opacity="0" transform="translate(35 50)">
                                <circle id="oval1" fill="#F48EA7" cx="6" cy="5" r="2"/>
                                <circle id="oval2" fill="#F48EA7" cx="2" cy="2" r="2"/>
                            </g>
                            <g id="grp1" opacity="0" transform="translate(24)">
                                <circle id="oval1" fill="#9FC7FA" cx="2.5" cy="3" r="2"/>
                                <circle id="oval2" fill="#9FC7FA" cx="7.5" cy="2" r="2"/>
                            </g>
                        </g>
                    </svg>
                </label>
        </form>
</div>
    </div>

    <script>
        document.getElementById('addToCartButton').addEventListener('click', function() {
            var bookId = <?php echo $bookId; ?>; // Pass the book_id dynamically from PHP

            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../cartSide/addToCart.php?book_id=' + bookId, true);

            // Ensure this is only triggered when the request is complete
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) { // Only proceed when the request is finished
                    if (xhr.status === 200) { // Check if the request was successful
                        var response = xhr.responseText.trim(); // Trim any extra spaces or unwanted characters
                        if (response === "success") {
                            alert("Book has been added to your cart!");
                        } else {
                            alert(response); // Display error or other messages
                        }
                    } else {
                        alert("Error: Unable to add book to cart.");
                    }
                }
            };

            xhr.send();
        });

        $(document).ready(function() {
            $('#backToListingButton').click(function() {
                window.location.href = '<?= $_SERVER['HTTP_REFERER'] ?? 'index.php' ?>';
            });
        });

        document.getElementById('checkbox').addEventListener('change', function () {
            var bookId = <?php echo $bookId; ?>; // Pass the book_id dynamically from PHP
            var isChecked = this.checked; // Check if the checkbox is checked

            // Create the request to add/remove the book from the wishlist
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../wishList/wishListAction.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = xhr.responseText.trim();
                    if (response === "added" && isChecked) {
                        showPopoutMessage("Book has been added to your Wish List!");
                    } else if (response === "removed" && !isChecked) {
                        showPopoutMessage("Book has been removed from your Wish List!");
                    }
                }
            };

            xhr.send("book_id=" + bookId + "&is_checked=" + isChecked);
        });    

        function showPopoutMessage(message) {
            const popoutMessage = document.getElementById('popoutMessage');
            const popoutText = document.getElementById('popoutText');
            popoutText.textContent = message;

            // Show the popout message
            popoutMessage.style.display = 'block';

            // Fade in the popout message
            setTimeout(() => {
                popoutMessage.style.opacity = 1;
            }, 10);

            // Hide the popout message after 3 seconds
            setTimeout(() => {
                popoutMessage.style.opacity = 0;
                setTimeout(() => {
                    popoutMessage.style.display = 'none';
                }, 500); // Wait for fade-out to finish before hiding
            }, 3000); // 3 seconds display time
        }
    </script>
</body>

</html>

<div id="popoutMessage" class="popout-message" style="display: none;">
    <span id="popoutText"></span>
</div>

<style>
    .popout-message {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #28a745;
        color: white;
        padding: 15px;
        border-radius: 5px;
        font-size: 16px;
        z-index: 1000;
        display: none; /* Hidden by default */
        opacity: 0;
        transition: opacity 0.5s ease;
    }

/* button{
    align-items:center; 
    justify-content:center; 
    text-align:center;
    margin-bottom: 10px;
} */

svg{
  cursor:pointer; overflow:visible; width:55px; margin-bottom: -20px;
  #heart{transform-origin:center; animation:animateHeartOut .3s linear forwards;}
  #main-circ{transform-origin:29.5px 29.5px;}
}

#checkbox{display:none;}

#checkbox:checked + label svg{
    #heart{transform:scale(.2); fill:#E2264D; animation:animateHeart .3s linear forwards .25s;}
    #main-circ{transition:all 2s; animation:animateCircle .3s linear forwards; opacity:1;}
    #grp1{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(0, -30px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(10px, -50px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp2{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(30px, -15px); 
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(60px, -15px); 
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp3{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(30px, 0px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(60px, 10px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp4{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(30px, 15px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(40px, 50px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp5{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(-10px, 20px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(-60px, 30px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp6{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(-30px, 0px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(-60px, -5px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp7{
      opacity:1; transition:.1s all .3s;
      #oval1{
        transform:scale(0) translate(-30px, -15px);
        transform-origin:0 0 0;
        transition:.5s transform .3s;}
      #oval2{
        transform:scale(0) translate(-55px, -30px);
        transform-origin:0 0 0;
        transition:1.5s transform .3s;}
    }
    #grp2{opacity:1; transition:.1s opacity .3s;}
    #grp3{opacity:1; transition:.1s opacity .3s;}
    #grp4{opacity:1; transition:.1s opacity .3s;}
    #grp5{opacity:1; transition:.1s opacity .3s;}
    #grp6{opacity:1; transition:.1s opacity .3s;}
    #grp7{opacity:1; transition:.1s opacity .3s;}
}

@keyframes animateCircle{
  40%{transform:scale(10); opacity:1; fill:#DD4688;}
  55%{transform:scale(11); opacity:1; fill:#D46ABF;}
  65%{transform:scale(12); opacity:1; fill:#CC8EF5;}
  75%{transform:scale(13); opacity:1; fill:transparent; stroke:#CC8EF5; stroke-width:.5;}
  85%{transform:scale(17); opacity:1; fill:transparent; stroke:#CC8EF5; stroke-width:.2;}
  95%{transform:scale(18); opacity:1; fill:transparent; stroke:#CC8EF5; stroke-width:.1;}
  100%{transform:scale(19); opacity:1; fill:transparent; stroke:#CC8EF5; stroke-width:0;}
}

@keyframes animateHeart{
  0%{transform:scale(.2);}
  40%{transform:scale(1.2);}
  100%{transform:scale(1);}
}

@keyframes animateHeartOut{
  0%{transform:scale(1.4);}
  100%{transform:scale(1);}
}
</style>
