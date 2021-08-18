PHP_ARG_ENABLE(my_custom_extension, Whether to enable the MyCustomExtension extension, [ --enable-my-custom-extension Enable MyCustomExtension])

if test "$MY_CUSTOM_EXTENSION" != "no"; then
    PHP_NEW_EXTENSION(my_custom_extension, my_custom_extension.c, $ext_shared)
fi
