<?php

/**
 * The placeholder of the user table island ({@see \humhub\modules\admin\widgets\UserTable}): the
 * card with skeleton rows, in the markup of `DataTable.vue`'s own skeleton, so the page does not
 * jump when the island mounts.
 */
?>
<section class="card content-card data-table" aria-busy="true">
    <div class="card-body">
        <div class="data-table-wrap">
            <table class="table" aria-hidden="true">
                <tbody>
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr class="data-table-skeleton placeholder-glow">
                        <td class="data-table-col-main">
                            <div class="data-table-main">
                                <span class="placeholder data-table-main-image"></span>
                                <span class="data-table-main-text">
                                    <span class="placeholder col-8"></span>
                                    <span class="placeholder placeholder-sm col-6"></span>
                                </span>
                            </div>
                        </td>
                        <td><span class="placeholder col-6"></span></td>
                        <td><span class="placeholder col-6"></span></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
