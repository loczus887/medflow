document.addEventListener('DOMContentLoaded', function() {
    
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    function validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, 'To pole jest wymagane');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !isValidEmail(field.value)) {
                showFieldError(field, 'Nieprawidłowy adres email');
                isValid = false;
            }
        });
        
        const password1 = form.querySelector('input[name="password1"]');
        const password2 = form.querySelector('input[name="password2"]');
        
        if (password1 && password2) {
            if (password1.value !== password2.value) {
                showFieldError(password2, 'Hasła nie są identyczne');
                isValid = false;
            }
        }
      
        const peselField = form.querySelector('input[name="pesel"]');
        if (peselField && peselField.value) {
            if (!isValidPesel(peselField.value)) {
                showFieldError(peselField, 'Nieprawidłowy numer PESEL');
                isValid = false;
            }
        }
        
        const phoneField = form.querySelector('input[name="phone"]');
        if (phoneField && phoneField.value) {
            if (!isValidPhone(phoneField.value)) {
                showFieldError(phoneField, 'Nieprawidłowy numer telefonu');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#c62828';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '4px';
        
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isValidPesel(pesel) {
        if (pesel.length !== 11 || !/^\d{11}$/.test(pesel)) {
            return false;
        }
        
        const weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        let sum = 0;
        
        for (let i = 0; i < 10; i++) {
            sum += parseInt(pesel[i]) * weights[i];
        }
        
        const checksum = (10 - (sum % 10)) % 10;
        return checksum === parseInt(pesel[10]);
    }
    
    function isValidPhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 9 && cleaned.length <= 15;
    }
    
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                showFieldError(this, 'Nieprawidłowy adres email');
            } else {
                clearFieldError(this);
            }
        });
    });
    
    const passwordInputs = document.querySelectorAll('input[type="password"][name="password1"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            showPasswordStrength(this);
        });
    });
    
    function showPasswordStrength(input) {
        const password = input.value;
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        
        let strengthText = '';
        let strengthColor = '';
        
        if (strength <= 1) {
            strengthText = 'Słabe';
            strengthColor = '#f44336';
        } else if (strength <= 3) {
            strengthText = 'Średnie';
            strengthColor = '#ff9800';
        } else {
            strengthText = 'Silne';
            strengthColor = '#4caf50';
        }
        
        let strengthDiv = input.parentNode.querySelector('.password-strength');
        if (!strengthDiv) {
            strengthDiv = document.createElement('div');
            strengthDiv.className = 'password-strength';
            strengthDiv.style.fontSize = '12px';
            strengthDiv.style.marginTop = '4px';
            input.parentNode.appendChild(strengthDiv);
        }
        
        strengthDiv.textContent = `Siła hasła: ${strengthText}`;
        strengthDiv.style.color = strengthColor;
    }
    
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    
    autoSaveForms.forEach(form => {
        const formId = form.dataset.autosave;
  
        const savedData = localStorage.getItem(`draft_${formId}`);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = data[key];
                    }
                });
            } catch (e) {
                console.error('Failed to load draft:', e);
            }
        }
        
        form.addEventListener('input', debounce(function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            localStorage.setItem(`draft_${formId}`, JSON.stringify(data));
        }, 1000));
        
        form.addEventListener('submit', function() {
            localStorage.removeItem(`draft_${formId}`);
        });
    });
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});