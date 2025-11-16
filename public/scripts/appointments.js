document.addEventListener('DOMContentLoaded', function() {
    
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    
    if (doctorSelect && dateInput && timeSelect) {
        const loadSlots = async () => {
            const doctorId = doctorSelect.value;
            const date = dateInput.value;
            
            if (!doctorId || !date) {
                timeSelect.innerHTML = '<option value="">Wybierz lekarza i datę</option>';
                return;
            }
            
            try {
                UI.showLoader(timeSelect);
                
                const response = await API.get('/get-available-slots', {
                    doctor_id: doctorId,
                    date: date
                });
                
                timeSelect.innerHTML = '<option value="">Wybierz godzinę</option>';
                
                if (response.slots && response.slots.length > 0) {
                    response.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.time;
                        option.textContent = slot.display;
                        timeSelect.appendChild(option);
                    });
                } else {
                    timeSelect.innerHTML = '<option value="">Brak dostępnych terminów</option>';
                }
                
            } catch (error) {
                UI.showError('Nie udało się pobrać dostępnych terminów');
                console.error(error);
            } finally {
                UI.hideLoader(timeSelect);
            }
        };
        
        doctorSelect.addEventListener('change', loadSlots);
        dateInput.addEventListener('change', loadSlots);
        
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
    
    const cancelButtons = document.querySelectorAll('.btn-cancel-appointment');
    
    cancelButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!UI.confirmDialog('Czy na pewno chcesz anulować tę wizytę?')) {
                return;
            }
            
            const appointmentId = this.dataset.appointmentId;
            
            try {
                UI.showLoader(this);
                
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                
                const response = await API.postForm('/cancel-appointment', formData);
                
                if (response.success) {
                    UI.showSuccess(response.message || 'Wizyta została anulowana');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    UI.showError(response.message || 'Nie udało się anulować wizyty');
                }
                
            } catch (error) {
                UI.showError('Wystąpił błąd podczas anulowania wizyty');
                console.error(error);
            } finally {
                UI.hideLoader(this);
            }
        });
    });
    
    const confirmButtons = document.querySelectorAll('.btn-confirm-appointment');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const appointmentId = this.dataset.appointmentId;
            
            try {
                UI.showLoader(this);
                
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                
                const response = await API.postForm('/confirm-appointment', formData);
                
                if (response.success) {
                    UI.showSuccess(response.message || 'Wizyta została potwierdzona');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    UI.showError(response.message || 'Nie udało się potwierdzić wizyty');
                }
                
            } catch (error) {
                UI.showError('Wystąpił błąd podczas potwierdzania wizyty');
                console.error(error);
            } finally {
                UI.hideLoader(this);
            }
        });
    });
    
    const completeButtons = document.querySelectorAll('.btn-complete-appointment');
    
    completeButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!UI.confirmDialog('Czy na pewno chcesz oznaczyć tę wizytę jako zakończoną?')) {
                return;
            }
            
            const appointmentId = this.dataset.appointmentId;
            
            try {
                UI.showLoader(this);
                
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                
                const response = await API.postForm('/complete-appointment', formData);
                
                if (response.success) {
                    UI.showSuccess(response.message || 'Wizyta została zakończona');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    UI.showError(response.message || 'Nie udało się zakończyć wizyty');
                }
                
            } catch (error) {
                UI.showError('Wystąpił błąd podczas kończenia wizyty');
                console.error(error);
            } finally {
                UI.hideLoader(this);
            }
        });
    });
    
    const filterSelect = document.getElementById('appointment-filter');
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filter = this.value;
            window.location.href = `/my-appointments?filter=${filter}`;
        });
    }
});