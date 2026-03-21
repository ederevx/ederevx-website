<?php
switch ($container) {
case "main":
    ?>
<br id="<?= $navId ?>">
<<?= $element ?> id="<?= $id ?>" class="<?= $class ?>">
    <?= $htmlContent ?>
</<?= $element ?>>
    <?php
    break;

case "double":
    ?>
<<?= $element ?> id="<?= $id ?>" class="<?= $class ?>">
    <<?= $element2 ?> id="<?= $childId ?>" class="<?= $class2 ?>">
        <?= $htmlContent ?>
    </<?= $element2 ?>>
</<?= $element ?>>
    <?php
    break;

case "button":
    ?>
<button id="<?= $id ?>" class="<?= $class ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $target ?>" 
        aria-expanded="false" aria-controls="<?= $target ?>">
    <?= $htmlContent ?>
</button>
    <?php
    break;

case "href":
    ?>
<a id="<?= $id ?>" class="<?= $class ?>" href="<?= $href ?>" target="<?= $target ?>">
    <?= $htmlContent ?>
</a>
    <?php
    break;

case "card":
    ?>
<div id="<?= $id ?>" class="card <?= $class ?>">
    <div id="<?= $bodyId ?>" class="card-body <?= $bodyClass ?>">
        <h5 id="<?= $titleId ?>" class="card-title <?= $titleClass ?>"><?= $titleContent ?></h5>
        <h6 id="<?= $subtitleId ?>" class="card-subtitle <?= $subtitleClass ?>"><?= $subtitleContent ?></h6>
        <p id="<?= $textId ?>" class="card-text <?= $textClass ?>"><?= $textContent ?></p>
    </div>
    <?= $htmlContent ?>
</div>
    <?php
    break;

default:
    ?>
<<?= $element ?> id="<?= $id ?>" class="<?= $class ?>">
    <?= $htmlContent ?>
</<?= $element ?>>
    <?php
    break;
}
?>
