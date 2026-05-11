const formData = new FormData();
formData.append('action', 'create_user');
formData.append('name', 'fetch_test_user');
formData.append('email', 'fetch@test.com');
formData.append('role', 'citizen');
formData.append('password', 'password123');
formData.append('confirm_password', 'password123');

fetch('http://localhost/integ/Verification.php', {
    method: 'POST',
    body: formData
}).then(res => res.text()).then(text => console.log('Response:', text));
