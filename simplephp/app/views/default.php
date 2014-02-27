<?php $this->session->begin(); ?>
<h2>Welcome to - <?= APP_NAME ?> </h2>
<?php if (isset ($error)): ?>
    <pre style='background:red;color:white;font-weight:bold;'><?= $error ?></pre>
<?php endif; ?>
    <pre style='background:<?= ($db) ? 'green' : 'red' ?>;color:white;'>Database connection <?= ($db) ? 'Successful.' : 'Failed!' ?></pre>
<pre><!--<?php print_r(posix_uname()); ?><hr>--><p>Request Parameters:</p><hr><?php print_r ($this->args); ?></pre>
<pre><p>Visit ID:</p><hr><?= alphaId (time() . alphaId (session_id(), true)) ?></pre>