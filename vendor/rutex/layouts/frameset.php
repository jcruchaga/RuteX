<link rel="stylesheet" href="/static/css/<?= $style ?>.css">
<body>
<?php foreach($frames as $frame): ?>
    <iframe name ="<?=$frame["name"]?>"
            class="<?=$frame["class"]?>"
            src  ="/<?=$path?>?frame=<?=$frame["name"]?>"
            frameborder="<?= $frame["border"]?>"
            scrolling="<?=$frame["scrolling"]?>"></iframe>
<?php endforeach?>
</body>
