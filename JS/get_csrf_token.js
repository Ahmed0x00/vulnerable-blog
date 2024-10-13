async function fetchCsrfToken() {
    try {
        const response = await fetch('http://127.0.0.1/vulnerable-blog/public/functions/get_csrf_token.php')
        const data = await response.json();
        if (data.csrf_token) {
            document.getElementById('csrfToken').value = data.csrf_token;
        } else {
            throw new Error('Failed to fetch CSRF token');
        }
    } catch (error) {
        console.error('Error fetching CSRF token:', error);
    }
}

document.addEventListener('DOMContentLoaded', fetchCsrfToken);
