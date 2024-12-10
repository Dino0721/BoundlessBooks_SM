<?php
$_title = 'Book Cart';
include '../pageFormat/base.php';

if (is_post()) {
    $btn = req('btn');
    if ($btn =='clear') {
        set_cart();
        redirect ('?');
    }
    $id = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    redirect();
}
?>

<table class="table">
    <tr>
        <th>Book ID</th>
        <th>Book Name</th>
        <th>Book Price (RM)</th>
        <th>Book Description</th>
        <th>Book Status</th>
        <th>Subtotal (RM)</th>
    </tr>

    <?php
        $count = 0;
        $total = 0;

        $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
        $cart = get_cart();

        foreach ($cart as $id => $unit):
            $stm->execute([$id]);
            $p = $stm->fetch();

            $subtotal = $p-> price * $unit;
            $count += $unit;
            $total += $subtotal;
            // TODO
    ?>
        <tr>
            <td><?= $p->id ?></td>
            <td><?= $p->name ?></td>
            <td class="right"><?= $p->price ?></td>
            <td>
                <form method="post">
                    <?= html_hidden('id') ?>
                    <?= html_select('unit', $_units,  '') ?>
                </form>            
            </td>
            <td class="right">
                <?= sprintf('%.2f', $subtotal) ?>
                <img src="/products/<?= $p->photo ?>" class="popup">
            </td>
        </tr>
    <?php endforeach ?>

    <tr>
        <th colspan="3"></th>
        <th class="right"><?= $count ?></th>
        <th class="right"><?= sprintf('%.2f', $total) ?></th>
    </tr>
</table>
