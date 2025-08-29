@if (session('success'))
    <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    timer: 3000,
                    customClass: {
                        confirmButton: "btn btn-primary w-xs mt-2",
                        cancelButton: "btn btn-danger w-xs mt-2",
                    },
                    buttonsStyling: !1,
                    showCloseButton: !0,
                });
            });
    </script>
@endif

    @if (session('error'))
    <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ session('error') }}",
                    icon: "error",
                    timer: 3000,
                    customClass: {
                        confirmButton: "btn btn-primary w-xs mt-2",
                        cancelButton: "btn btn-danger w-xs mt-2",
                    },
                    buttonsStyling: !1,
                    showCloseButton: !0,
                });
            });
    </script>
    @endif

    <script>
        document.querySelectorAll('.remove-item-btn, .remove-btn, .delete-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                        cancelButton: 'btn btn-danger w-xs mt-2',
                    },
                    confirmButtonText: 'Yes, delete it!',
                    buttonsStyling: false,
                    showCloseButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
