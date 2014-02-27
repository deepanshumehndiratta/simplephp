<?php

    if (!defined ('DS'))
    {
        print 'Direct Script Access Denied!'.
        exit;
    }

    global $config;
    
?>

<style type="text/css">
        
   table#profile tr:first-child td {
                
       border: 1px solid #fff;
                
    }
        
   table#profile tr td {
                
       border: 1px solid #000;
       line-height: 2em;
                
   }
        
</style>
<h2>Database Profiler:</h2>
<table id='profile' style='width:100%;'>
    <tr style='background:#000;color:#fff;border:1px solid #fff;'>
        <td>S No.</td>
        <td>Query</td>
        <td>Status</td>
        <td>Affected Rows</td>
        <td>Cached</td>
    </tr>
    <?php
    
    global $profile;
            
    $i = 0;
            
    foreach ($profile as $t):
                
    ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= $t['query'] ?></td>
        <td style='background:<?= $t['status'] ? 'green' : 'red' ?>'><?= $t['status'] ? 'Successful' : 'Failed' ?></td>
        <td><?= $t['rows'] ?></td>
        <td><?= $t['cached'] ? 'True' : 'False' ?></td>
    </tr>
    <?php
                    
        $i++;
                    
    endforeach;
            
    ?>
</table>
<?php if (isset ($config['cache']['enabled']) && $config['cache']['enabled']): ?>
<h2>Mem-Cached Profiler:</h2>
<table id='profile' style='width:100%;'>
    <tr style='background:#000;color:#fff;border:1px solid #fff;'>
        <td>S No.</td>
        <td>Query</td>
        <td>Action</td>
        <td>Result Size (KBytes)</td>
    </tr>
    <?php
    
    global $mem_profile;
            
    $i = 0;
            
    foreach ($mem_profile as $t):
                
    ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= $t['query'] ?></td>
        <td><?= $t['action'] ?></td>
        <td><?= $t['size'] / 1024 ?></td>
    </tr>
    <?php
                    
        $i++;
                    
    endforeach;
            
    ?>
</table>
<?php endif; ?>