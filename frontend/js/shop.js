// Search functionality
const searchToggle = document.querySelector('.search-toggle');
const searchBox = document.querySelector('.search-box');
const searchInput = document.getElementById('searchInput');

if (searchToggle && searchBox) {
    searchToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        searchBox.classList.toggle('show');
        if (searchBox.classList.contains('show')) {
            searchInput.focus();
        }
    });
    
    // Close search when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchToggle.contains(e.target) && !searchBox.contains(e.target)) {
            searchBox.classList.remove('show');
        }
    });
    
    // Prevent search box from closing when clicking inside
    searchBox.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Real-time search suggestions
if (searchInput) {
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        if (this.value.length >= 2) {
            searchTimeout = setTimeout(() => {
                fetchSearchSuggestions(this.value);
            }, 300);
        } else {
            hideSearchSuggestions();
        }
    });
    
    // Handle Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('searchForm').submit();
        }
    });
}

function fetchSearchSuggestions(query) {
    fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                showSearchSuggestions(data.suggestions, query);
            } else {
                hideSearchSuggestions();
            }
        })
        .catch(() => hideSearchSuggestions());
}

function showSearchSuggestions(suggestions, query) {
    let suggestionsHTML = '';
    
    suggestions.forEach(suggestion => {
        const highlightedName = highlightText(suggestion.name, query);
        suggestionsHTML += `
            <div class="suggestion-item" data-product-id="${suggestion.id}">
                <img src="${suggestion.image}" alt="${suggestion.name}">
                <div class="suggestion-info">
                    <div class="suggestion-name">${highlightedName}</div>
                    <div class="suggestion-category">${suggestion.category}</div>
                </div>
            </div>
        `;
    });
    
    // Create or update suggestions container
    let suggestionsContainer = document.querySelector('.search-suggestions');
    if (!suggestionsContainer) {
        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        searchBox.appendChild(suggestionsContainer);
    }
    
    suggestionsContainer.innerHTML = suggestionsHTML;
    
    // Add click event to suggestions
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            const productId = this.dataset.productId;
            window.location.href = `product.php?id=${productId}`;
        });
    });
}

function hideSearchSuggestions() {
    const suggestionsContainer = document.querySelector('.search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.remove();
    }
}

function highlightText(text, query) {
    if (!query) return text;
    
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<span class="suggestion-highlight">$1</span>');
}