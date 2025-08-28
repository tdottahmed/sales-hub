<form action="{{ route('profile.destroy') }}" method="POST">
    @csrf
    @method('DELETE')
    <x-data-entry.input type="password" name="password" label="Password" required />
    <x-data-entry.input type="checkbox" name="confirm_delete" label="Delete Account" required />
    <x-action.button type="button" id="delete-account" variant="danger">Delete Account</x-action.button>
</form>
@push('scripts')
    <script>
        document.querySelectorAll('#delete-account').forEach(button => {
            button.addEventListener('click', function(e) {
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
@endpush
