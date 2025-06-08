document.addEventListener('DOMContentLoaded', function() {
    const requestModal = document.getElementById('requestTutorModal');
    
    function displayModal() {
        if (requestModal) {
            requestModal.style.display = 'block'; 
            setTimeout(() => {
                requestModal.classList.add('show'); 
            }, 10); 
        }
    }

    function hideModal() {
        if (requestModal) {
            requestModal.classList.remove('show');
            setTimeout(() => {
                requestModal.style.display = 'none';
            }, 300);
        }
    }

    const openModalBtnOnRequestPage = document.getElementById('openRequestModalBtnOnPage'); 
    if (openModalBtnOnRequestPage) {
        openModalBtnOnRequestPage.onclick = displayModal;
    }

    const closeModalSpans = requestModal ? requestModal.querySelectorAll('.close-btn') : [];
    closeModalSpans.forEach(span => {
        span.onclick = hideModal;
    });

    window.onclick = function(event) {
        if (event.target == requestModal) {
            hideModal();
        }
    }

    const formsToValidate = document.querySelectorAll('form.validate-form');
    formsToValidate.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                input.style.borderColor = '#5f6368'; 
                const existingErrorMsg = input.nextElementSibling;
                if (existingErrorMsg && existingErrorMsg.classList.contains('error-message-inline')) {
                    existingErrorMsg.remove();
                }

                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#f28b82'; 
                    const errorMsg = document.createElement('small');
                    errorMsg.textContent = 'Field ini wajib diisi.';
                    errorMsg.style.color = '#f28b82';
                    errorMsg.style.display = 'block';
                    errorMsg.style.marginTop = '4px';
                    errorMsg.classList.add('error-message-inline');
                    input.parentNode.insertBefore(errorMsg, input.nextSibling);
                } else if (input.type === 'email' && !/^\S+@\S+\.\S+$/.test(input.value.trim())) {
                }
            });

            if (!isValid) {
                event.preventDefault(); 
            }
        });
    });
});
