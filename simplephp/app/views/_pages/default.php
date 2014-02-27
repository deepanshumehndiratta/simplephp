<!DOCTYPE html>
<html>

    <head>
    
        <base href='<?php print $config ["dir"]; ?>'/>
        
        <!-- Le title -->
        
        <title><?= APP_NAME ?> &mdash; Simple</title>

        <!-- Le styles -->
        <link href="<?php print load ('css/bootstrap', 'bootstrap.min.css'); ?>" rel="stylesheet">        
        <link href="<?php print load ('css/bootstrap', 'bootstrap-responsive.min.css'); ?>" rel="stylesheet">
        
        <!-- Le scripts -->
        
        <?php if ($config ['mode'] == 2): ?>
        <script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' type='text/javascript'></script>
        <?php else: ?>
        <script type="text/javascript" src="<?php print load ('js', 'jquery.min.js'); ?>"></script>
        <?php endif; ?>
        
        <script src="<?php print load ('js/bootstrap', 'bootstrap.min.js'); ?>"></script>
        
    </head>
    
    <body>
    
        <div class="container" style="max-width:65%;">
            <div class="hero-unit">
                <h2>Welcome to - <?= APP_NAME ?> </h2>
                <?php if (isset ($error)): ?>
                <pre style='background:red;color:white;font-weight:bold;'><?= $error ?></pre>
                <?php endif; ?>
                <pre style='background:<?= ($db) ? 'green' : 'red' ?>;color:white;'>Database connection <?= ($db) ? 'Successful.' : 'Failed!' ?></pre>
                <pre><p>Request Parameters:</p><hr><?php print_r ($this->args); ?></pre>
                <pre><p>Visit ID:</p><hr><?= alphaId (time() . alphaId (session_id(), true)) ?></pre>
            </div>
        </div>
        &copy; <a href='http://simplephp.org' target='_blank'>Simple &mdash; The PHP Framework</a>
        
    </body>
    
</html>