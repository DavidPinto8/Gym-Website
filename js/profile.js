document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatar-upload');
    const avatarPreview = document.getElementById('profile-picture-preview');
    const uploadStatus = document.getElementById('avatar-upload-status');

    if (!avatarInput || !avatarPreview) {
        return;
    }

    function setUploadStatus(message, type = '') {
        if (!uploadStatus) {
            return;
        }

        uploadStatus.textContent = message;
        uploadStatus.className = `avatar-upload-status ${type}`.trim();
    }

    avatarInput.addEventListener('change', async function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            setUploadStatus('Please choose a PNG or JPEG image.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        const csrfToken = document.getElementById('avatar-csrf-token')?.value;

        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        setUploadStatus('Uploading photo...', 'loading');

        try {
            const response = await fetch('api/upload_avatar.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                avatarPreview.src = data.new_avatar_url + '?t=' + new Date().getTime();

                const headerAvatar = document.querySelector('.nav-avatar');

                if (headerAvatar) {
                    headerAvatar.src = avatarPreview.src;
                }

                setUploadStatus('Photo updated successfully.', 'success');
            } else {
                setUploadStatus('Upload failed: ' + data.message, 'error');
            }
        } catch (error) {
            setUploadStatus('An error occurred while uploading the image.', 'error');
        }
    });
});