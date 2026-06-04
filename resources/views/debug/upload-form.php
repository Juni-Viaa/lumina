{{-- Change Later --}}
<?php
            try {
                const res  = await fetch("{{ route('uploads.store') }}", {
                    method: 'POST',
                    body:   form,
                });
                const data = await res.json();

                if (res.ok) {
                    // Snap to last step, brief pause, then show success
                    this.currentStep = this.steps.length;
                    await new Promise(r => setTimeout(r, 500));
                    this.uploaded  = true;
                    this.uploading = false;
                    await this.fetchDocuments();
                } else {
                    this.uploading   = false;
                    this.uploadError = data.message ?? 'Upload gagal. Coba lagi.';
                }
            } catch {
                this.uploading   = false;
                this.uploadError = 'Koneksi bermasalah. Coba lagi.';
            }
?>