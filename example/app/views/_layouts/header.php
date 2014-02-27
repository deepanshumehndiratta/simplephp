<!DOCTYPE html>
<html>

    <head>
    
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        
        <base href='<?= $config ["dir"]; ?>'/>
        
        <!-- Le title -->
        
        <title><?= isset ($title_for_layout) ? (!empty ($title_for_layout) ? ($title_for_layout . ' | ') : null ) : null ?><?= APP_NAME ?> &mdash; Simple</title>

        <!-- Le styles -->
        <link href="<?= load ('css/bootstrap', 'bootstrap.min.css'); ?>" rel="stylesheet">        
        <link href="<?= load ('css/bootstrap', 'bootstrap-responsive.min.css'); ?>" rel="stylesheet">
        
        <!-- Le scripts -->
        
        <?php if ($config['mode'] == 2): ?>
        <script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' type='text/javascript'></script>
        <?php else: ?>
        <script type="text/javascript" src="<?= load ('js', 'jquery.min.js'); ?>"></script>
        <?php endif; ?>
        
        <script src="<?= load ('js/bootstrap', 'bootstrap.min.js'); ?>"></script>
        
    </head>
    
    <body>