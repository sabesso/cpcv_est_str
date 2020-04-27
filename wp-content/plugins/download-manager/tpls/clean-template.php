<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php the_title(); ?></title>
    <?php wp_head(); ?>
    <style>
        body{
            font-family: var(--wpdm-font);
            background: #ffffff;
        }
        .outer {
            display: table;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
        }

        .middle {
            display: table-cell;
            vertical-align: middle;
        }



        .w3eden .panel .panel-heading{
            font-size: 10px;
        }
        .w3eden p{
            margin: 15px 0 !important;
        }

    </style>
</head>
<body>
<div class="outer">
    <div class="middle">
        <div class="inner">
            <?php
            the_post();
            the_content();
            ?>
        </div>
    </div>
</div>

<?php wp_footer(); ?>

</body>


</html>