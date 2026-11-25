const http = require('http');

const data = JSON.stringify({ 
    serial_number: 'E JE02 31 A 6 44397',
    complaint_number: '12345'
});

const req = http.request({
    hostname: '127.0.0.1',
    port: 3000,
    path: '/battery-connect/check-status',
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Automation-Secret': 'amaron_local_secret_key_2026',
        'Content-Length': Buffer.byteLength(data)
    }
}, (res) => {
    let body = '';
    res.on('data', chunk => body += chunk);
    res.on('end', () => {
        console.log('HTTP Status:', res.statusCode);
        try {
            console.log('Response Body:', JSON.stringify(JSON.parse(body), null, 2));
        } catch(e) {
            console.log('Raw Response:', body);
        }
    });
});

req.on('error', console.error);
req.write(data);
req.end();
