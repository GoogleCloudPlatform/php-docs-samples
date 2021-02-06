export RUN_APPENGINE_FLEX_TESTS=false
export DIR=$(pwd)

if [ "$RUN_APPENGINE_FLEX_TESTS" != "true" ] &&
       [[ -n $(grep -ri 'env:.+flex' app.*ml $DIR ) ]]; then
        echo "Skipping tests in $DIR (no App Engine Flex tests)"
        continue
fi
