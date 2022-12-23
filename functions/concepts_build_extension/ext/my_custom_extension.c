// include the PHP API itself
#include <php.h>
// include the extension header
#include "my_custom_extension.h"

// register the "helloworld_from_extension" function to the PHP API
zend_function_entry my_custom_extension_functions[] = {
    PHP_FE(helloworld_from_extension, NULL)
    {NULL, NULL, NULL}
};

// some information about our module
zend_module_entry my_custom_extension_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_MY_CUSTOM_EXTENSION_EXTNAME,
    my_custom_extension_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    PHP_MY_CUSTOM_EXTENSION_VERSION,
    STANDARD_MODULE_PROPERTIES
};

// use a macro to output additional C code, to make ext dynamically loadable
ZEND_GET_MODULE(my_custom_extension)

// Implement our "Hello World" function, which returns a string
PHP_FUNCTION(helloworld_from_extension) {
    zval val;
    ZVAL_STRING(&val, "Hello World! (from my_custom_extension.so)\n");
    RETURN_STR(Z_STR(val));
}
