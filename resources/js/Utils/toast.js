import Swal from 'sweetalert2';

export const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

export const showSuccessToast = (message = 'Berhasil memperbarui profil!') => {
    Toast.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message
    });
};

export const showErrorToast = (message = 'Gagal memperbarui profil.') => {
    Toast.fire({
        icon: 'error',
        title: 'Gagal!',
        text: message
    });
};
