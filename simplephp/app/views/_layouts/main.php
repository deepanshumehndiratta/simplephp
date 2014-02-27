<?
    $this->renderPartial
    (
        'header',
        array(
            'db' => $db,
            'config' => $config,
            'title_for_layout' => isset ($title_for_layout) ? $title_for_layout : null
        )
    );
?>
<div class="container" style="max-width:65%;">
    <div class="hero-unit">
    
    <? echo $content; ?>

    </div>
</div>
<? $this->renderPartial ('footer'); ?>