# Error Handling Test Scenarios

This document provides comprehensive test scenarios to verify that exact server errors are properly passed down to the frontend instead of being categorized as CORS errors.

## Pre-Test Setup

### 1. Backend Implementation Checklist

Before running tests, ensure the following files are implemented:

- ✅ `backend/src/Middleware/ErrorHandlingMiddleware.php`
- ✅ `backend/src/Handlers/CustomErrorHandler.php` 
- ✅ Updated `backend/src/index.php` with new error handling
- ✅ Updated `frontend/composables/useApi.ts` with enhanced error handling
- ✅ Added `frontend/utils/debug.ts` for development debugging

### 2. Environment Configuration

Ensure your development environment has:

```bash
# backend/.env
APP_ENV=development  # Enable debug information
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://calcfolio.vercel.app
```

```bash
# frontend/.env
NUXT_PUBLIC_BACKEND_URL=https://calcfolio-api-dev.up.railway.app
```

## Test Scenarios

### Test 1: 405 Method Not Allowed (Primary Issue)

**Scenario**: Send GET request to POST-only `/admin/login` endpoint

**Steps**:
1. Open browser developer console
2. Execute this JavaScript in console:
```javascript
fetch('https://calcfolio-api-dev.up.railway.app/admin/login', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000'
  }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **CORS headers present**: Response should include `Access-Control-Allow-Origin`
- ✅ **Status code**: 405 Method Not Allowed
- ✅ **Structured error response**:
```json
{
  "success": false,
  "error": {
    "type": "method_not_allowed",
    "code": "HTTPMETHODNOTALLOWEDEXCEPTION_405",
    "message": "Method not allowed. Please check your request method.",
    "timestamp": "2025-01-01T12:00:00+00:00"
  }
}
```
- ✅ **No CORS error**: Should NOT see "blocked by CORS policy" in browser console

### Test 2: 404 Not Found

**Scenario**: Request non-existent endpoint

**Steps**:
```javascript
fetch('https://calcfolio-api-dev.up.railway.app/nonexistent-endpoint', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000'
  }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **Status code**: 404 Not Found
- ✅ **Error type**: `not_found`
- ✅ **User-friendly message**: "The requested resource was not found."

### Test 3: 401 Unauthorized

**Scenario**: Access protected endpoint without authentication

**Steps**:
```javascript
fetch('https://calcfolio-api-dev.up.railway.app/admin/messages', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000'
  }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **Status code**: 401 Unauthorized
- ✅ **Error type**: `unauthorized`
- ✅ **User-friendly message**: "Authentication required. Please log in."

### Test 4: 422 Validation Error

**Scenario**: Submit invalid data to contact form

**Steps**:
```javascript
fetch('https://calcfolio-api-dev.up.railway.app/contact', {
  method: 'POST',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000'
  },
  body: JSON.stringify({
    name: '',  // Invalid: empty name
    email: 'invalid-email',  // Invalid: bad email format
    subject: '',
    message: ''
  })
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **Status code**: 422 Validation Error (or 400 Bad Request)
- ✅ **Error type**: `validation_error` or `bad_request`
- ✅ **Specific error message** about validation failures

### Test 5: 500 Server Error

**Scenario**: Trigger internal server error

**Steps**:
1. Temporarily modify backend to throw an exception:
```php
// Add this to any route handler in index.php
throw new Exception('Test server error');
```

2. Make request to that endpoint:
```javascript
fetch('https://calcfolio-api-dev.up.railway.app/test-error-endpoint', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000'
  }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **Status code**: 500 Internal Server Error
- ✅ **Error type**: `server_error`
- ✅ **Debug info in development**: Exception details in response
- ✅ **Error logged**: Check server logs for error context

### Test 6: Frontend Login Integration Test

**Scenario**: Test the actual login page error handling

**Steps**:
1. Navigate to `http://localhost:3000/admin/login`
2. Open browser developer console
3. Try to login with:
   - Invalid credentials (should get 401)
   - Empty fields (should get 400)
   - Send GET request instead of POST (should get 405)

**Expected Results**:
- ✅ **Specific error messages** displayed in UI
- ✅ **No CORS errors** in console
- ✅ **Proper error types** logged in console
- ✅ **Toast notifications** with specific messages

### Test 7: Network Error Simulation

**Scenario**: Test when server is completely down

**Steps**:
1. Stop the backend server
2. Try to make any API request from frontend
3. Check error handling

**Expected Results**:
- ✅ **Network error message**: "Unable to connect to server"
- ✅ **No false CORS attribution**
- ✅ **Graceful error handling** in UI

### Test 8: CORS Preflight Test

**Scenario**: Verify CORS preflight requests work correctly

**Steps**:
```javascript
// This should trigger a preflight request
fetch('https://calcfolio-api-dev.up.railway.app/admin/login', {
  method: 'POST',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Origin': 'http://localhost:3000',
    'X-Custom-Header': 'test'  // This will trigger preflight
  },
  body: JSON.stringify({
    username: 'test',
    password: 'test'
  })
})
.then(response => response.json())
.then(data => console.log('Response:', data))
.catch(error => console.error('Error:', error));
```

**Expected Results**:
- ✅ **OPTIONS request succeeds** with 204 status
- ✅ **POST request proceeds** with structured error response
- ✅ **No CORS blocking** for the actual request

## Testing Tools

### 1. Browser Developer Console

For manual testing, use the browser console to execute fetch requests and inspect responses.

### 2. Postman/Thunder Client

Create a collection with all test scenarios:

```json
{
  "name": "Error Handling Tests",
  "requests": [
    {
      "name": "405 Method Not Allowed",
      "method": "GET",
      "url": "{{baseUrl}}/admin/login",
      "headers": {
        "Origin": "http://localhost:3000",
        "Content-Type": "application/json"
      }
    },
    {
      "name": "404 Not Found", 
      "method": "GET",
      "url": "{{baseUrl}}/nonexistent",
      "headers": {
        "Origin": "http://localhost:3000"
      }
    }
  ]
}
```

### 3. curl Commands

```bash
# Test 405 Method Not Allowed
curl -X GET \
  -H "Origin: http://localhost:3000" \
  -H "Content-Type: application/json" \
  -i \
  https://calcfolio-api-dev.up.railway.app/admin/login

# Test 404 Not Found
curl -X GET \
  -H "Origin: http://localhost:3000" \
  -i \
  https://calcfolio-api-dev.up.railway.app/nonexistent

# Test CORS preflight
curl -X OPTIONS \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -i \
  https://calcfolio-api-dev.up.railway.app/admin/login
```

## Verification Checklist

For each test scenario, verify:

### ✅ CORS Headers Present
- `Access-Control-Allow-Origin` header is present
- `Vary: Origin` header is present
- `Access-Control-Allow-Credentials: true` header is present

### ✅ Structured Error Response
```json
{
  "success": false,
  "error": {
    "type": "error_type",
    "code": "ERROR_CODE_XXX", 
    "message": "User friendly message",
    "timestamp": "ISO 8601 timestamp"
  }
}
```

### ✅ Development Debug Info (when APP_ENV=development)
```json
{
  "error": {
    // ... other fields
    "debug": {
      "exception": "ExceptionClassName",
      "file": "/path/to/file.php",
      "line": 123,
      "trace": "Stack trace..."
    }
  }
}
```

### ✅ Frontend Error Handling
- Specific error messages displayed to users
- No generic "CORS error" messages
- Toast notifications work correctly
- Console shows structured error details in development

### ✅ Server Logging
Check server logs for:
- Error context (method, URI, IP address)
- Exception details
- Timestamp and correlation information

## Common Issues and Troubleshooting

### Issue 1: Still Getting CORS Errors

**Symptoms**: Browser still shows "blocked by CORS policy"
**Causes**:
- Error handling middleware not properly registered
- CORS headers not being added to error responses
- Middleware order incorrect

**Solution**: Verify middleware registration order in `index.php`

### Issue 2: Generic Error Messages

**Symptoms**: Frontend shows generic error messages instead of specific server errors
**Causes**:
- Frontend error handling not updated
- Server not returning structured error format
- Error parsing failing in frontend

**Solution**: Check frontend `handleApiError` function implementation

### Issue 3: Debug Info Not Showing

**Symptoms**: No debug information in development mode
**Causes**:
- `APP_ENV` not set to `development`
- Debug flag not passed to error handlers

**Solution**: Verify environment configuration

## Success Criteria

The error handling improvement is successful when:

1. ✅ **All HTTP error codes** (400, 401, 403, 404, 405, 500, etc.) reach the frontend with specific error messages
2. ✅ **No CORS masking** occurs - browser console shows actual HTTP errors instead of CORS errors
3. ✅ **Structured error format** is consistent across all endpoints
4. ✅ **Frontend displays specific error messages** based on server error responses
5. ✅ **Development debugging** provides detailed error information
6. ✅ **Production security** hides sensitive error details while maintaining user-friendly messages
7. ✅ **Server logging** captures comprehensive error context for troubleshooting

## Rollback Plan

If issues occur during implementation:

1. **Revert middleware changes** in `index.php`
2. **Restore original CORS middleware** 
3. **Remove custom error handlers**
4. **Revert frontend error handling changes**
5. **Test that basic functionality still works**

## Next Steps After Testing

Once all tests pass:

1. **Deploy to staging environment** for integration testing
2. **Monitor error logs** for any unexpected issues
3. **Update frontend error messages** for better user experience
4. **Add automated tests** to prevent regression
5. **Document the new error handling patterns** for team reference

This comprehensive testing strategy ensures that the error handling improvements work correctly and that exact server errors properly reach the frontend without CORS masking.