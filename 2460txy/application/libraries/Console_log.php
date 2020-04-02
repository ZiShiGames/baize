<?php
defined('BASEPATH') OR exit('No direct script access allowed');

register_shutdown_function('my_shutdown');

function my_shutdown()
{
	echo Console_log::fetch_output();
}

class Console_log {
    private static $output = '';
    static function log($data)
    {
        if (is_array($data) || is_object($data))
        {
            $data = json_encode($data);
        }
        ob_start();
        ?>
		<?php if (self::$output === ''):?>
		<script>
		<?php endif;?>
		console.log('<?php echo $data;?>');
        <?php
        self::$output .= ob_get_contents();
        ob_end_clean();
    }

    static function fetch_output()
    {
    	if (self::$output !== '')
    	{
    		self::$output .= '</script>';
    	}
        return self::$output;
    }
	static function index(){
		var_dump("leading success");
    }
    static function alert($data)
    {
        if (is_array($data) || is_object($data))
        {
            $data = json_encode($data);
        }
        ob_start();
        ?>
		<?php if (self::$output === ''):?>
		<script>
		<?php endif;?>
		alert('<?php echo $data;?>');
        <?php
        self::$output .= ob_get_contents();
        ob_end_clean();
    }
}
