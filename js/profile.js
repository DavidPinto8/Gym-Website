document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatar-upload');
    const avatarPreview = document.getElementById('profile-picture-preview');

    if (!avatarInput || !avatarPreview) {
        return;
    }

    avatarInput.addEventListener('change', async function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

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
            } else {
                alert('Upload failed: ' + data.message);
            }
        } catch (error) {
            console.error('Server communication error:', error);
            alert('An error occurred while uploading the image.');
        }
    });
});