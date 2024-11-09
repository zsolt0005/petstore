# PetStore





### Poznámky k Swagger
- POST /pet vracia 405 MethodNotAllowed pre nevalidných datach?
- POST /pet nevracia invalid ID ale PUT /pet ano ?
- PUT /pet validation exception je 405 status code ?





# TODO
- GET /pet/findByStatus
- GET /pet/findByTags
- POST /pet/{petId}
- POST /pet/{petId}/uploadImage


- Ak do URL dam pre ID parameter < 0 tak to hodí 404 route not found
- Ak je prázdný array pre photoUrls, pri naćítaní tam dá 1 default prázdný string
- Ak je prázdný array pre tags, pri načítaní tam dá 1 default empty object