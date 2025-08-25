// Test script to verify CORS error handling improvements
console.log('Testing CORS error handling improvements...\n');

// Test 1: GET request to login endpoint (should return 405 Method Not Allowed)
console.log('🧪 Test 1: GET request to login endpoint (expecting 405 Method Not Allowed)');
fetch('https://calcfolio-api-dev.up.railway.app/admin/login', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
  }
})
.then(async response => {
  console.log('Status:', response.status);
  console.log('Status Text:', response.statusText);
  console.log('Headers:', Object.fromEntries(response.headers.entries()));
  
  try {
    const data = await response.json();
    console.log('Response Body:', JSON.stringify(data, null, 2));
  } catch (e) {
    console.log('Failed to parse JSON response:', e.message);
  }
})
.catch(error => {
  console.error('Request failed:', error.message);
  console.error('Error type:', error.constructor.name);
})
.finally(() => {
  console.log('\n' + '='.repeat(50) + '\n');
  
  // Test 2: POST to non-existent endpoint (should return 404 Not Found)
  console.log('🧪 Test 2: POST to non-existent endpoint (expecting 404 Not Found)');
  return fetch('https://calcfolio-api-dev.up.railway.app/nonexistent', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ test: 'data' })
  });
})
.then(async response => {
  console.log('Status:', response.status);
  console.log('Status Text:', response.statusText);
  console.log('Headers:', Object.fromEntries(response.headers.entries()));
  
  try {
    const data = await response.json();
    console.log('Response Body:', JSON.stringify(data, null, 2));
  } catch (e) {
    console.log('Failed to parse JSON response:', e.message);
  }
})
.catch(error => {
  console.error('Request failed:', error.message);
  console.error('Error type:', error.constructor.name);
})
.finally(() => {
  console.log('\n' + '='.repeat(50) + '\n');
  
  // Test 3: POST with invalid JSON to login endpoint (should handle gracefully)
  console.log('🧪 Test 3: POST with invalid credentials to login endpoint');
  return fetch('https://calcfolio-api-dev.up.railway.app/admin/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ username: 'invalid', password: 'invalid' })
  });
})
.then(async response => {
  console.log('Status:', response.status);
  console.log('Status Text:', response.statusText);
  console.log('Headers:', Object.fromEntries(response.headers.entries()));
  
  try {
    const data = await response.json();
    console.log('Response Body:', JSON.stringify(data, null, 2));
  } catch (e) {
    console.log('Failed to parse JSON response:', e.message);
  }
})
.catch(error => {
  console.error('Request failed:', error.message);
  console.error('Error type:', error.constructor.name);
})
.finally(() => {
  console.log('\n🎯 Test Summary:');
  console.log('- Test 1 should show 405 error with proper CORS headers, NOT a CORS blocking message');
  console.log('- Test 2 should show 404 error with proper CORS headers');  
  console.log('- Test 3 should show authentication error with proper CORS headers');
  console.log('- All responses should have Access-Control-Allow-Origin headers');
  console.log('- No "blocked by CORS policy" messages should appear in browser console');
});