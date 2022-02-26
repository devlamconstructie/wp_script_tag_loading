<?php 
/** 
 * the objective: allow developers to enqueue scripts with a single line anywhere in their files.
 * 
 * 
 */

//the function you will actually use:
function dvc_add_script_to_header($id, $src, ...$options){
    $script = new Dvc_Script_Tag($id, $src, $options);
    dvc_add_script_to_collection($script->export_script_attributes());
}

/* the rest happens automagically */

add_action( 'wp_head', 'dvc_load_scripts' );
function dvc_load_scripts() {
    $script_collection = dvc_get_script_collection();
    foreach ($script_collection as $script) {
        dvc_dequeue_if_enqueued($script);
        wp_print_script_tag($script);
    }
}

function dvc_add_script_to_collection($script){
    dvc_script_collection($script);
}

function dvc_get_script_collection(){
    return dvc_script_collection();
}

/**
 *  returns script collection, adds script if provided.  
 * @param array associative script array;
 * 
*/
function dvc_script_collection($script){
    static $script_collection = array();

	if (!$script) 
        return $script_collection;

    $existing_key = array_search($script['id'], array_column($script_collection, 'id'));        
	
    if($existing_key){
        $script_collection[$existing_key] = $script;     
    } else {
        $script_collection[] = $script;
    } 

    return $script_collection;
}

function dvc_is_script_handle_enqueued_or_registered($script){
   $enqueued = wp_script_is( $script['id'], 'enqueued' );
   $registered = wp_script_is( $script['id'], 'registered' );
   return ($enqueued || $registered ) ? true : false ;
}

function dvc_dequeue_if_enqueued($script){
    $enqueued = wp_script_is( $script['id'], 'enqueued' );
    wp_dequeue_script( $script['id'] );
    return $enqueued;
}

/*
 * investigate whether we can / need to implement this logic:
 * Typically you want to use async where possible, 
 * then defer then no attribute. Here are some general rules to follow:
    
    * async downloads the file during HTML parsing and will pause the HTML parser to execute it when it has finished downloading.
    * defer downloads the file during HTML parsing and will only execute it after the parser has completed. defer scripts are also guaranteed to execute in the order that they appear in the document.

    If the script is modular and does not rely on any scripts then use async.
    If the script relies upon or is relied upon by another script then use defer.
    If the script is small and is relied upon by an async script then use an inline script with no attributes placed above the async scripts.
    from: https://www.growingwiththeweb.com/2014/02/async-vs-defer-attributes.html
 * 
 */

class Dvc_Script_Tag{
    public $id;
    public $src;
    public $attributes;
    public $tag;

    function __construct($id, $src, ...$options){
        $this->id = $id;
        $this->src = $src;
        $this->$other_attributes = pre_sanitize_script_attributes($options);
    }

    /* unpack attributes in a user friendly way.*/ 
    /* input can be single string or a series of strings or an array.*/
    function pre_sanitize_script_attributes($options){
        if (!$options || empty($options) || count($options) === 0)
            return [];

        $attributes_array = [];
        
        foreach($options as $opt){
            switch($opt){
                case('async'):
                    $attributes_array['async'] = true;
                    break;	
                case('defer'):
                    $attributes_array['defer'] = true;
                    break;
                case('module'):
                    $attributes_array['type'] = 'module';
                    break;
                case('autoversion'):
                    /* setup autoversion code */
                    break;	
                    /* consider other script loading options? */
                default:
                    break;
            }		
            
        }	

        return $attributes_array;
    }


    public function export_script_attributes(){
        $export =  array(
                'id' => $this->id,
                'src' => $this->src 
            );
        $export = array_merge($export, $this->other_attributes );
        return $export;
    }

}




?>