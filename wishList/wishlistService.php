<?php

/**
 * WishlistService
 *
 * Lightweight service layer for wishlist operations.
 * - Encapsulates all SQL for the wishlist module in one place
 * - Keeps page/controller files focused on HTTP / rendering
 * - Makes the behaviour easier to test and reuse
 */

declare(strict_types=1);

/**
 * Get all book IDs in a user's wishlist.
 *
 * @param PDO   $db
 * @param int   $userId
 * @return int[]
 */
function wishlist_get_user_book_ids(PDO $db, int $userId): array
{
    $sql = 'SELECT book_id FROM wishlist WHERE user_id = :user_id';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fetch full book records for the given IDs.
 *
 * @param PDO   $db
 * @param int[] $bookIds
 * @return array<int,array<string,mixed>>
 */
function wishlist_get_items(PDO $db, array $bookIds): array
{
    if (empty($bookIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));

    $sql = "
        SELECT
            b.book_id,
            b.book_photo,
            b.book_name,
            b.book_desc,
            b.book_price,
            b.book_category,
            b.book_status
        FROM book_item b
        WHERE b.book_id IN ($placeholders)
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($bookIds);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add a book to the user's wishlist.
 */
function wishlist_add(PDO $db, int $userId, int $bookId): void
{
    $sql = 'INSERT INTO wishlist (user_id, book_id) VALUES (:user_id, :book_id)';
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':book_id' => $bookId,
    ]);
}

/**
 * Remove a book from the user's wishlist.
 */
function wishlist_remove(PDO $db, int $userId, int $bookId): void
{
    $sql = 'DELETE FROM wishlist WHERE user_id = :user_id AND book_id = :book_id';
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':book_id' => $bookId,
    ]);
}

/**
 * Simple enum-like result for "move wishlist item to cart" use‑case.
 */
final class WishlistAddToCartResult
{
    public const ADDED_TO_CART        = 'added_to_cart';
    public const ALREADY_OWNED        = 'already_owned';
    public const ALREADY_IN_CART      = 'already_in_cart';
}

/**
 * Move a wishlist item into the active cart (creating cart if necessary).
 *
 * Business rules:
 * - If the user already owns the book → do not add to cart, return ALREADY_OWNED
 * - If the book is already in the active cart → do not add, return ALREADY_IN_CART
 * - Otherwise, ensure an active cart exists and insert one cart_item row
 *
 * @return string One of WishlistAddToCartResult::* constants
 */
function wishlist_add_book_to_cart(PDO $db, int $userId, int $bookId): string
{
    // 1) User already owns the book?
    $ownershipSql = 'SELECT 1 FROM book_ownership WHERE user_id = :user_id AND book_id = :book_id';
    $stmt = $db->prepare($ownershipSql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return WishlistAddToCartResult::ALREADY_OWNED;
    }

    // 2) Already in an active cart?
    $existingCartItemSql = '
        SELECT 1
        FROM cart_item
        WHERE cart_id IN (
            SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0
        )
        AND book_id = :book_id
    ';
    $stmt = $db->prepare($existingCartItemSql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return WishlistAddToCartResult::ALREADY_IN_CART;
    }

    // 3) Ensure active cart exists
    $cartIdSql = 'SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0';
    $stmt = $db->prepare($cartIdSql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        $insertCartSql = 'INSERT INTO cart (user_id) VALUES (:user_id)';
        $stmt = $db->prepare($insertCartSql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $cartId = (int) $db->lastInsertId();
    }

    // 4) Insert new cart_item
    $insertItemSql = 'INSERT INTO cart_item (cart_id, book_id) VALUES (:cart_id, :book_id)';
    $stmt = $db->prepare($insertItemSql);
    $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
    $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $stmt->execute();

    return WishlistAddToCartResult::ADDED_TO_CART;
}


