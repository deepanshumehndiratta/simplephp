<?php
    
    header('HTTP/1.1 404 Not Found');

    $this->session->begin();

?>
<h2>Welcome to - <?= APP_NAME ?> </h2>
<pre style='background:red;color:white;font-weight:bold;'>404 Error. The requested URL was not found on this server.</pre>
<pre style='background:<?= ($db) ? 'green' : 'red' ?>;color:white;'>Database connection <?= ($db) ? 'Successful.' : 'Failed!' ?></pre>
<pre><p>Request Parameters:</p><hr><?php print_r ($this->args); ?></pre>
<pre><p>Visit ID:</p><hr><?= alphaId (time() . alphaId (session_id(), true)) ?></pre>