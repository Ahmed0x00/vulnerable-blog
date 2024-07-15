function fetchCredits() {
    fetch('http://127.0.0.1/vulnerable-blog/premium_membership/get_credits.php')
        .then(response => response.json())
        .then(data => {
            if (data.credits !== undefined) {
                document.getElementById('credits').innerHTML = `${data.credits}$`;
            } else {
                console.error(data.error);
            }
        })
        .catch(error => console.error('Error fetching credits:', error));
}
window.onload = fetchCredits;