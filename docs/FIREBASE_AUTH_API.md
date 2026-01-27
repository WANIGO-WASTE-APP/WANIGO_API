# Firebase Authentication API Documentation

## Overview
This document describes the Firebase Authentication integration for the WANIGO Bank Sampah API. The system allows users to authenticate using Google Sign-In via Firebase and receive a Laravel Sanctum token for API access.

## Authentication Endpoint

### POST /api/auth/firebase/google

Authenticate a user with Firebase Google Sign-In and receive a Sanctum API token.

**Request:**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE4MmU0M..."
}
```

**Parameters:**
- `id_token` (required, string): Firebase ID token obtained from Firebase Authentication

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Login success",
  "data": {
    "token": "1|abcdef123456...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "https://lh3.googleusercontent.com/..."
    }
  }
}
```

**Error Responses:**

**401 Unauthorized** - Invalid or expired token:
```json
{
  "success": false,
  "message": "Invalid or expired Firebase token"
}
```

**422 Unprocessable Entity** - Validation error:
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "id_token": ["Firebase ID token is required"]
  }
}
```

**500 Internal Server Error** - Security configuration error:
```json
{
  "success": false,
  "message": "Security configuration error"
}
```

## Using the Sanctum Token

After successful authentication, use the returned token in subsequent API requests:

```
Authorization: Bearer 1|abcdef123456...
```

Example:
```bash
curl -X GET https://api.example.com/api/nasabah/profile \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -H "Accept: application/json"
```

## Postman Collection

### Firebase Authentication Request

**Name:** Firebase Google Sign-In  
**Method:** POST  
**URL:** `{{base_url}}/api/auth/firebase/google`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
  "id_token": "{{firebase_id_token}}"
}
```

**Tests Script:**
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("sanctum_token", jsonData.data.token);
    pm.environment.set("user_id", jsonData.data.user.id);
}
```

## Environment Variables

Add these variables to your Postman environment:

- `base_url`: API base URL (e.g., `http://localhost:8000`)
- `firebase_id_token`: Firebase ID token from Google Sign-In
- `sanctum_token`: Sanctum token (auto-set after successful auth)
- `user_id`: User ID (auto-set after successful auth)

## Integration Flow

1. **Client authenticates with Firebase:**
   - User signs in with Google via Firebase SDK
   - Client receives Firebase ID token

2. **Client sends token to API:**
   - POST request to `/api/auth/firebase/google` with `id_token`

3. **API verifies and processes:**
   - Verifies token with Firebase Admin SDK
   - Creates or updates user in database
   - Generates Sanctum token

4. **Client receives Sanctum token:**
   - Stores token securely
   - Uses token for subsequent API requests

## Security Notes

- Firebase ID tokens expire after 1 hour
- Sanctum tokens do not expire by default
- Always use HTTPS in production
- Never expose Firebase credentials in client code
- Validate APP_DEBUG is false in production

## Error Handling

The API follows a consistent error response format:

```json
{
  "success": false,
  "message": "Error description"
}
```

Common error scenarios:
- **Invalid token**: Token is malformed or expired
- **Security error**: APP_DEBUG is true in production
- **Network error**: Cannot reach Firebase servers
- **Database error**: Cannot create/update user

## Testing

Use the provided Postman collection to test the authentication flow:

1. Obtain a Firebase ID token from Firebase Console or your app
2. Set the `firebase_id_token` environment variable
3. Send the Firebase Google Sign-In request
4. Verify the response contains a valid Sanctum token
5. Use the token to access protected endpoints
