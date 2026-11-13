/**
 * TaskFlow – Smart Task Management System
 * Client-Side Interactivity, Validation & Modal Management
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Delete Confirmation Modal Handling
    const deleteModal = document.getElementById('deleteTaskModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const taskId = button.getAttribute('data-task-id');
            const taskTitle = button.getAttribute('data-task-title');

            const modalTaskIdInput = deleteModal.querySelector('#delete_task_id');
            const modalTaskTitleSpan = deleteModal.querySelector('#delete_task_title');

            if (modalTaskIdInput) {
                modalTaskIdInput.value = taskId;
            }
            if (modalTaskTitleSpan) {
                modalTaskTitleSpan.textContent = taskTitle || 'this task';
            }
        });
    }

    // 3. Quick Status Change Modal Handling
    const statusModal = document.getElementById('statusModal');
    if (statusModal) {
        statusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const taskId = button.getAttribute('data-task-id');
            const currentStatus = button.getAttribute('data-current-status');
            const taskTitle = button.getAttribute('data-task-title');

            const modalTaskIdInput = statusModal.querySelector('#status_task_id');
            const modalTaskTitleSpan = statusModal.querySelector('#status_task_title');
            const statusSelect = statusModal.querySelector('#new_status_select');

            if (modalTaskIdInput) {
                modalTaskIdInput.value = taskId;
            }
            if (modalTaskTitleSpan) {
                modalTaskTitleSpan.textContent = taskTitle || '';
            }
            if (statusSelect && currentStatus) {
                statusSelect.value = currentStatus;
            }
        });
    }

    // 4. Client-side Form Validation (Bootstrap 5)
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 5. Password Confirmation Check on Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (passwordInput && confirmPasswordInput) {
            function validatePasswordMatch() {
                if (confirmPasswordInput.value !== '' && passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            }

            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }
    }

    // 6. Password Visibility Toggle
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            }
        });
    });

    // 7. Auto-dismiss Flash Alerts after 5 seconds
    const autoAlerts = document.querySelectorAll('.alert-auto-dismiss');
    autoAlerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // 8. Textarea Character Counter for Task Description
    const descTextarea = document.getElementById('description');
    const charCounter = document.getElementById('descriptionCharCounter');
    if (descTextarea && charCounter) {
        function updateCharCount() {
            const remaining = 5000 - descTextarea.value.length;
            charCounter.textContent = `${descTextarea.value.length} / 5000 characters`;
            if (remaining < 0) {
                charCounter.classList.add('text-danger');
            } else {
                charCounter.classList.remove('text-danger');
            }
        }
        descTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    }
});

