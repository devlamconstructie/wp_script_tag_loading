# wp_script_tag_loading
This snippet aims to facilitate script loading features introduced in Wordpress version 5.7.

The snippet aims to make it easier (if necessary) to: 
1. enqueue asynchronously 
2. enqueue deferred scripts
3. enqueue autoversioned scripts based on file changes.
4. enqueue scripts as module


##todo
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
 
