// Language and Currency Switcher

// Toggle dropdown menu
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    
    // Close all other dropdowns
    allDropdowns.forEach(dd => {
        if (dd.id !== id) {
            dd.classList.remove('show');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.nav-dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(dd => {
            dd.classList.remove('show');
        });
    }
});

// Set language
function setLanguage(lang) {
    fetch('api/set-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'language=' + encodeURIComponent(lang)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to apply translations
            window.location.reload();
        } else {
            alert('Failed to change language: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change language. Please try again.');
    });
}

// Set currency
function setCurrency(currency) {
    fetch('api/set-currency.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'currency=' + encodeURIComponent(currency)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all prices on the page
            updatePrices();
            // Close dropdown
            document.getElementById('currency-dropdown').classList.remove('show');
        } else {
            alert('Failed to change currency: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change currency. Please try again.');
    });
}

// Update all prices on the page
function updatePrices() {
    // Find all price elements (they should have data-price attribute)
    const priceElements = document.querySelectorAll('[data-price]');
    
    priceElements.forEach(element => {
        const originalPrice = parseFloat(element.getAttribute('data-price'));
        const fromCurrency = element.getAttribute('data-currency') || 'USD';
        
        // Get current currency from session (we'll need to get it from a hidden input or make an API call)
        fetch('api/convert-currency.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'amount=' + originalPrice + '&from=' + fromCurrency
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.textContent = data.formatted;
            }
        })
        .catch(error => {
            console.error('Error converting price:', error);
        });
    });
}

// Auto-update prices on page load if currency changed
document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to update prices
    const priceElements = document.querySelectorAll('[data-price]');
    if (priceElements.length > 0) {
        // Prices will be updated via PHP on server-side, but we can also do client-side updates
        // This is mainly for dynamic content
    }
});





