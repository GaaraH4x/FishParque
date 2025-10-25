document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    const productSelect = document.getElementById('product');
    const quantityInput = document.getElementById('quantity');
    const quantityHelp = document.getElementById('quantityHelp');
    const messageDiv = document.getElementById('message');

    // Minimum quantities for each product
    const minQuantities = {
        'fish_feed': 10,
        'catfish': 1,
        'materials': 50
    };

    // Update quantity helper text when product changes
    productSelect.addEventListener('change', function() {
        const selectedProduct = this.value;
        
        if (selectedProduct) {
            const minQty = minQuantities[selectedProduct];
            quantityInput.min = minQty;
            quantityInput.value = minQty;
            quantityHelp.textContent = `Minimum order: ${minQty}kg`;
        } else {
            quantityHelp.textContent = '';
            quantityInput.value = '';
            quantityInput.min = 1;
        }
    });

    // Form validation and submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous messages
        messageDiv.className = 'message';
        messageDiv.textContent = '';
        
        // Get form values
        const name = document.getElementById('name').value.trim();
        const address = document.getElementById('address').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const product = productSelect.value;
        const quantity = parseFloat(quantityInput.value);
        const notes = document.getElementById('notes').value.trim();
        
        // Validate product selection
        if (!product) {
            showMessage('Please select a product', 'error');
            return;
        }
        
        // Validate minimum quantity
        const minQty = minQuantities[product];
        if (quantity < minQty) {
            showMessage(`Minimum order for this product is ${minQty}kg`, 'error');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('name', name);
        formData.append('address', address);
        formData.append('phone', phone);
        formData.append('product', product);
        formData.append('quantity', quantity);
        formData.append('notes', notes);
        
        // Send data to PHP
        fetch('main.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                form.reset();
                quantityHelp.textContent = '';
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        });
    });
    
    // Helper function to display messages
    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.className = `message ${type}`;
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.className = 'message';
                messageDiv.textContent = '';
            }, 5000);
        }
    }
});