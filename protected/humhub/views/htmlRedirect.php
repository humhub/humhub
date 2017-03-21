<script>

    // If current URL == New Url (absolute || relative) then only Refresh
    if (window.location.pathname + window.location.search + window.location.hash == '<?= $url; ?>' || '<?= $url; ?>' == window.location.href) {

        <?php
        //echo "window.location.reload();\n"; // Drops warning on Posts
        // Remove test.php#xy  (#xy) part
        $temp = explode("#", $url);
        $url = $temp[0];
        ?>

        if (window.location.search == '') {
            window.location.href = '<?= $url; ?>';
        } else {
            window.location.href = '<?= $url; ?>';
        }

    } else {
        window.location.href = '<?= $url; ?>';
    }
</script>

