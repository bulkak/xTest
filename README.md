# test work for xSite offer

## todo
 - better test
 - ~~more tests~~
 - validation errors with more information
 - not a dummy black list validator realization
 - ~~separate validation and store (stupid repository)~~

## to test start do:

 ```
 docker-compose up -d
 docker ps
 ```

 - copy CONTAINER_ID of xtest_php
 - run for integration tests:
```
 docker exec -ti CONTAINER_ID /www/vendor/bin/phpunit --testsuite integration
```

- run for unit tests:
```
 docker exec -ti CONTAINER_ID /www/vendor/bin/phpunit --testsuite unit
```


