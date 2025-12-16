<?php

/**
 * CartService
 *
 * Service layer for cart operations (Cart Management module).
 * - Encapsulates cart‑related SQL
 * - Keeps controllers small and focused on HTTP concerns
 * - Makes behaviour easier to test and evolve (SRP / DIP / OCP)
 */

declare(strict_types=1);

/**
 * Result enum‑like values for adding a book to cart.
 */
final class CartAddResult
{
    public const ADDED_TO_CART   = 'added_to_cart';
    public const ALREADY_OWNED   = 'already_owned';
    public const ALREADY_IN_CART = 'already_in_cart';
}

/**
 * Ensure the user has an active (unpaid) cart and return its ID.
 */
function cart_get_or_create_active_cart(PDO $db, int $userId): int
{
    $stmt = $db->prepare('SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0');
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cartId = $stmt->fetchColumn();

    if ($cartId) {
        return (int) $cartId;
    }

    // Create a new active cart
    $insert = $db->prepare('INSERT INTO cart (user_id, paid) VALUES (:user_id, 0)');
    $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $insert->execute();

    return (int) $db->lastInsertId();
}

/**
 * Core business rules for adding a book to the cart.
 *
 * Rules:
 * - If user already owns the book → do not add, return ALREADY_OWNED
 * - If book already exists in active cart → do not add, return ALREADY_IN_CART
 * - Else ensure an active cart exists and insert new cart_item → ADDED_TO_CART
 */
function cart_add_book(PDO $db, int $userId, int $bookId): string
{
    // User already owns the book?
    $ownershipSql = 'SELECT 1 FROM book_ownership WHERE user_id = :user_id AND book_id = :book_id';
    $stmt = $db->prepare($ownershipSql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return CartAddResult::ALREADY_OWNED;
    }

    // Already in active cart?
    $existingSql = '
        SELECT 1
        FROM cart_item
        WHERE cart_id IN (
            SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0
        )
        AND book_id = :book_id
    ';
    $stmt = $db->prepare($existingSql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return CartAddResult::ALREADY_IN_CART;
    }

    // Get or create active cart
    $cartId = cart_get_or_create_active_cart($db, $userId);

    // Insert new cart item
    $insertItem = $db->prepare('INSERT INTO cart_item (cart_id, book_id) VALUES (:cart_id, :book_id)');
    $insertItem->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
    $insertItem->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $insertItem->execute();

    return CartAddResult::ADDED_TO_CART;
}

/**
 * Remove a book from the user's active cart (if present).
 */
function cart_remove_book(PDO $db, int $userId, int $bookId): void
{
    $sql = '
        DELETE FROM cart_item
        WHERE cart_id = (
            SELECT cart_id
            FROM cart
            WHERE user_id = :user_id AND paid = 0
        )
        AND book_id = :book_id
    ';

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();
}


