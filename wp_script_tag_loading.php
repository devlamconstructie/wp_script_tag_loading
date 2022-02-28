<?php 
/** 
 * the objective: allow developers to enqueue scripts with a single line anywhere in their files.
 * 
 * developers can now add single use attributes (like hashes) by simply passing the att as a key/value array.
 * @example dvc_add_script_to_header('my-script', 'https://www.someurl.tld/some-script.js', 'async', ['integrity'=>'3216gobbledygook'], 'module');
 * @param string $id a.k.a. the handle. a string representing the script. 
 * @param string $src the url of the script. 
 * @param string|array ...$options strings representing argument settings or single item associative arrays with the literal attributes and their values in any order.
 * recognized strings by default are: async, defer, module, nomodule, autoversion, anonymous, use-credentials.
*/
function dvc_add_script_to_header($id, $src, ...$options){
    $script = new Dvc_Script_Tag($id, $src, $options);
    dvc_add_script_to_collection($script->export_script_attributes());
}

//if you need a custom option you can supply the attribute using this function,
//  and add either the value or a callback function that will supply the value.
function dvc_add_possible_attribute($attribute, $value_or_callback){
    Dvc_Script_Tag::$possible_attributes[$attribute] = $value; 
}

/* the rest happens automagically */
/* may want this to happen last, because we mey be dequeueing some stuff. */
add_action( 'wp_head', 'dvc_load_scripts' );
function dvc_load_scripts() {
    $script_collection = dvc_get_script_collection();
    foreach ($script_collection as $script) {
        dvc_dequeue_if_enqueued($script);
        wp_print_script_tag($script);
    }
}

function dvc_add_script_to_collection($script){
    dvc_manage_script_collection($script);
}

function dvc_get_script_collection(){
    return dvc_managae_script_collection();
}

/**
 *  returns script collection, adds script if provided.  
 * @param array associative script array;
 * 
*/
function dvc_manage_script_collection($script){
    static $script_collection = array();

	if (!$script) 
        return $script_collection;
    
    $existing_index = array_search($script['id'], array_column($script_collection, 'id'));        
	//if already exists overwrite. 
    if($existing_index){
        $script_collection[$existing_index] = $script;     
    } else {
        $script_collection[] = $script;
    } 

    return $script_collection;
}

function dvc_dequeue_if_enqueued($script){
    if( $enqueued = wp_script_is( $script['id'], 'enqueued' ) ){
        wp_dequeue_script( $script['id'] );
    }
    return $enqueued;
}

class Dvc_Script_Tag{
    public $id;
    public $src;
    public $attributes;
    public static $possible_attributes; 
    
    function __construct($id, $src, ...$options){
        $this->id = $id;
        $this->src = esc_url($src);
        $this->$other_attributes = pre_sanitize_script_attributes($options);
        self::$possible_attributes = dvc_prefill_possible_attributes();
    }

    /* unpack attributes in a user friendly way.*/ 
    /* input can be single string or a series of strings or an array.*/
    function pre_sanitize_script_attributes($options){
        if (!$options || empty($options) || count($options) === 0)
            return [];

        //well collect the key/value pairs of the attributes here.        
        $attributes_array = [];
        
        foreach($options as $option){
            
            //if the option was passed as an array key/value pair
            //just add it to the list and gtfo.
            if( is_array($option) && count($option) == 1 && $key = key($option)){
                $attributes_array[$key] = $option[$key];
                continue;
            }

            //if the option is a string compare it to the list of predefined possible attributes.
            $attribute = self::$possible_attributes[$option];

            //if the predefined possible attribute has a callback attached to it, exec the callback.
            if ($attribute && is_callable($attribute[$option])){
                 
                $value = call_user_func($attribute[$option], ['id' => $this->id, 'src' => $this->src, 'options'=>$options]);
                if($value){
                    $attribute[$option] = $value;     
                }    

            }
            $attributes_array[] = $attribute;          
        }	

        return $attributes_array;
    }

    //create the array in the format 
    public function export_script_attributes(){
        $export =  array(
                'id' => $this->id,
                'src' => $this->src, 
            );
        $export = array_merge($export, $this->other_attributes );
        return $export;
    }

}

function dvc_prefill_possible_attributes(){
    $possible_attributes = array(
        'async' => ['async' => true],
        'defer' => ['defer' => true],
        'nomodule' => ['nomodule' => true],
        'module' => ['type' => 'module'],
        'autoversion' => ['version' => 'dvc_get_file_modified_time_from_src'],
        'anonymous' => ['crossorigin' => 'anonymous'],
        'usecredentials' => ['crossorigin' => 'use-credentials'],
    );
    return $possible_attributes;
}



/**
 * returns date and time a file linked in src attribute was changed. 
 * @uses dvi_update_autoversion_script_handles() 
 * @param string $tag the tag as generated by wordpress
 * @param string $handle the handle as defined in wp_enqueue 
 * @param string $src the source value as compiled by WP_Scripts  
 */
function dvc_get_file_modified_time_from_url($argsarray){
 
    /* todo:
    check if domain is in the src, if not abort.
    figure out from the url what the file path would be.

    */

    ['src'=> $url ] = $argsarray;
	$path = dvc_get_server_path_from_url($url);

	return filemtime($path);
}

function dvc_get_server_path_from_url($url){
    //I'm pretty sure this currently only works if this snippet is in the root dir of the plugin. 
	$plugin_url = trailingslashit( trailingslashit( plugins_url() ) . plugin_basename( dirname( __FILE__ ) ) ) ;
	$plugin_path = trailingslashit( dirname(__FILE__, 1) );
		
	// create system path to the file 
	$file_url = explode('?', $url);
	$file_url = array_shift($file_url);
	$path = str_replace($plugin_url, $plugin_path, $file_url);
}

?>