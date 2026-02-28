// Reviews and Ratings JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Rating input stars
    const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
    const starLabels = document.querySelectorAll('.star-label');
    
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            updateStarDisplay(index + 1);
        });
        
        input.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
    });
    
    const ratingContainer = document.querySelector('.rating-input');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', function() {
            const checked = document.querySelector('.rating-input input[type="radio"]:checked');
            if (checked) {
                const index = Array.from(ratingInputs).indexOf(checked);
                highlightStars(index + 1);
            } else {
                clearStars();
            }
        });
    }
    
    // Review form submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReview(this);
        });
    }
});

function updateStarDisplay(rating) {
    const starLabels = document.querySelectorAll('.star-label');
    starLabels.forEach((label, index) => {
        const icon = label.querySelector('i');
        if (index < rating) {
            icon.classList.remove('far');
            icon.classList.add('fas');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
        }
    });
}

function highlightStars(rating) {
    const starLabels = document.querySelectorAll('.star-label');
    starLabels.forEach((label, index) => {
        const icon = label.querySelector('i');
        if (index < rating) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#ffc107';
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '#ccc';
        }
    });
}

function clearStars() {
    const starLabels = document.querySelectorAll('.star-label');
    starLabels.forEach((label) => {
        const icon = label.querySelector('i');
        icon.classList.remove('fas');
        icon.classList.add('far');
        icon.style.color = '#ccc';
    });
}

function submitReview(form) {
    const formData = new FormData(form);
    
    fetch('api/reviews.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error submitting review', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error submitting review. Please try again.', 'error');
    });
}

function showNotification(message, type) {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}






