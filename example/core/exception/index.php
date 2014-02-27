<?php

    if (!defined ('DS'))
    {
        print 'Direct Script Access Denied!'.
        exit;
    }

    if (ob_get_length())
        ob_clean();

    global $config, $exception, $message, $errfile, $errline;;
    
    $trace = $exception->getTrace();
    
    if ($trace[0]['function'] == 'handleError')
    {
        
        unset ($trace[0]);
        
        $trace = array_values ($trace);
        
    }
    
    if ($trace[0]['function'] == 'trigger_error')
    {
        
        unset ($trace[0]);
        
    }
    
    for ($i = 0; $i < count ($trace); $i++)
    {
        
        if (isset ($trace[$i]['function']) && $trace[$i]['function'] == 'call_user_func_array')
        {
            
            $trace[$i - 1]['file'] = $trace[$i]['file'];
            $trace[$i - 1]['line'] = $trace[$i]['line'];
            
            unset ($trace[$i]);
            
            $trace = array_values ($trace);
            
            break;
            
        }
        
    }
    
    for ($i = 0; $i < count ($trace); $i++)
    {
        
        if (isset ($trace[$i]['function']) && in_array ($trace[$i]['function'], array ('requires', 'require_once')))
        {
            
            unset ($trace[$i]);
            
            $trace = array_values ($trace);
            
            $i--;
                        
        }
        
    }

?>
<!DOCTYPE html>
<html>

    <head>
    
        <title> Error - <?= $config['name'] ?> </title>

       <!-- Le styles -->
        <link href="<?php print load ('css/bootstrap', 'bootstrap.min.css'); ?>" rel="stylesheet">        
        <link href="<?php print load ('css/bootstrap', 'bootstrap-responsive.min.css'); ?>" rel="stylesheet">
        
        <style type="text/css">
        
            table tr:first-child td {
                
                border: 1px solid #fff;
                
            }
        
            table tr td {
                
                border: 1px solid #000;
                line-height: 2em;
                
            }
        
        </style>
    
    </head>
    
    <body>

        <div class="container" style="max-width:85%;">
          
          <div class="hero-unit">
          
              <h2><?= $config['name']; ?></h2>
        
                <p>The following errors were encountered during runtime. Try reloading the page, if the errors persist please contact the webmaster of the site.</p>

                <p style='color:red;font-weight:bold;font-size:1.2em;'><?= $message ?></p>
        
                <h2>Stack Trace</h2>
        
                <table style='width:100%;'>
                    <tr style='background:#000;color:#fff;border:1px solid #fff;'>
                        <td>S No.</td>
                        <td>Filename</td>
                        <td>Function</td>
                    </tr>
                <?php
            
                    $i = 0;
            
                    foreach ($trace as $t):
                        
                        $args = array();
                    
                        if (isset ($t['class']))
                        {
                    
                            $classMethod = new ReflectionMethod ($t['class'], $t['function']);
                            
                            foreach ($classMethod->getParameters() as $parameter)
                            {
                            
                                array_push ($args, $parameter->name);
                                
                            }
                            
                        }
                        
                        
                
                ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= $t['file'] . ' on line ' . $t['line'] ?></td>
                        <td><?= isset ($t['class']) ? ($t['class'] . '->' . $t['function']) : ($t['function']) ?>(<?= (!empty ($args) ? '$' : null) . implode (', $', $args) ?>)</td>
                    </tr>
                <?php
                    
                        $i++;
                    
                    endforeach;
            
                ?>
                </table>
            
            </div>
        
            &copy; li8PHP - 2012
        
        </div>

    </body>

</html>