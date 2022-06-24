# test work for xSite offer

## todo
 - better test
 - more tests
 - validation errors with more information
 - not a dummy black list validator realization
 - separate validation and store (stupid repository)

## to test start do:

 ```
 docker-compose up -d
 docker ps
 ```

 - copy CONTAINER_ID of xtest_php
 - run:
```
 docker exec -ti CONTAINER_ID /www/vendor/bin/phpunit /www/tests/Service/UserServiceImplTest.php
```


