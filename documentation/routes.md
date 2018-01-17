## /status

checks if your cookie token is still valid

there is **lifetime** you can configure in **config/twitter.ini**

### response types

* **200** authorized
* **401** not authorized
* **500** internal error

## /login

### required parametes

url parameter **redirect** with a valid url or the **HTTP_REFERER** Header

url parameter will overrule the header

### response types

* **500** internal error
* **TODO** **400** parameter missing
* redirect if successfull
