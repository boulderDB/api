# BlocBeta API Documentation

[Authorization](#Authorization)
[Resources](#Resources)
  - [Statistics](#Statistics)
  - [location](#location)
  - [boulder](#boulder)
  - [grade](#grade)
  - [holdstyle](#holdstyle)
  - [wall](#wall)
  - [tag](#tag)
  - [ascent](#ascent)
  - [setter](#setter)
  - [error](#error)
  - [doubt](#doubt)
  - [ranking](#ranking)
  - [compare](#compare)
  - [me](#me)
  - [user](#user)

# Authorization

Obtain a [JWT](https://jwt.io/) token:
```
POST /api/login

{
    username: "foo",
    password: "bar"
}
```

Decode the token response with any client listed on [https://jwt.io](https://jwt.io/)

Example JWT Token Payload:
```json
{
  "iat": 1589234212,
  "exp": 1589237812,
  "roles": [
    "ROLE_USER",
    "ROLE_SETTER"
  ],
  "username": "schaschjan",
  "id": 42,
  "location": {
    "id": 1,
    "name": "Salon du Bloc",
    "url": "salon"
  }
}
```

On all subsequent API requests, send the Token in the Authorization header:
```
Authorization: Bearer <jwt-token>
```

# Resources
The {location} parameter takes the locations slug. To obtain a location see the the [location](#location) endpoint.

## Statistics

Statistics on active boulders
```
GET /api/{location}/statistics/boulder
```

Statistics on walls
```
GET /api/{location}/statistics/wall
```

Statistics on wall reset rotation
```
GET /api/{location}/statistics/wall-reset-rotation
```


### Location

List active locations
```
GET /api/location
```

### Boulder

Active boulders
```
GET /api/{location}/boulder/filter/active
```

Get boulder details
```
GET /api/{location}/boulder/{id}
```

Create boulder (admin)
```
POST /api/{location}/boulder
``` 

Update boulder (admin)
```
PUT /api/{location}/boulder/{id}
```

Report an error
```
POST /api/{location}/boulder/error
```

Mass update boulders (admin)
```
PUT /api/{location}/boulder/mass
```

### Grade

All grades
```
GET /api/{location}/grade
```

### Hold style

All hold styles
```
GET /api/{location}/holdstyle
```

### Wall

All walls
```
GET /api/{location}/wall
```

### Tag

All tags
```
GET /api/{location}/tag
```

### Ascent

Ascents on active boulders
```
GET /api/{location}/ascent/filter/active
```

Create ascent
```
POST /api/{location}/ascent
```

Remove ascent
```
DELETE /api/{location}/ascent/{id}
```

Doubt an ascent
```
POST /api/{location}/ascent/{id}/doubt
```

### Setter

Get all setters
```
GET /api/{location}/setter
```

Invite a user to join as a route setter (admin)
```
POST /api/{location}/setter/{userId}/invite
```

Revoke a users role as route setter (admin)
```
PUT /api/{location}/setter/{userId}/revoke
```

### Error

Get unresolved reported errors (admin)
```
GET /api/{location}/error
```

Count unresolved reported errors (admin)
```
GET /api/{location}/error/count
```

Resolve an error
```
PUT /api/{location}/error/{id}/resolve
```

### Doubt
Resolve a doubt
```
PUT /api/{location}/doubt/{id}/resolve
```

### Ranking

Returns a user ranking considering ascents on active boulders
```
GET /api/{location}/ranking/current
```

Returns a user ranking considering all time ascents 
```
GET /api/{location}/ranking/alltime
```

### Compare

Compare ascents on active boulders to another user

```
GET /api/{location}/compare/{userA}/to/{userB}/at/current
```

### Me

Get account details
```
GET /api/me
```

Update account details
```
PUT /api/me
```

Queue account for deletion
```
DELETE /api/me
```

### User

Search user by username
```
GET /api/{location}/user/search?username={foo}
```
