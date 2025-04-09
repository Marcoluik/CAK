document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Basic validation
            if (!data.name || !data.email || !data.phone || !data.zipcode || !data.address || !data.house_type) {
                alert('Venligst udfyld alle påkrævede felter');
                return;
            }
            
            if (!data.consent) {
                alert('Du skal acceptere, at vi må kontakte dig');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="inline-block animate-spin mr-2">⌛</span>Sender...';
            
            try {
                const response = await fetch('/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.error || 'Der opstod en fejl ved afsendelse af din forespørgsel');
                }
                
                // Show success message
                alert('Tak for din forespørgsel! Vi vender tilbage hurtigst muligt.');
                this.reset();
                
            } catch (error) {
                alert(error.message);
            } finally {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    }
}); 