<div class="px-5 pb-5 shrink-0">
    <template x-if="!uploading && !uploaded">
        <div>
            <input
                type="file"
                x-ref="fileInput"
                class="hidden"
                accept=".pdf,.doc,.docx"
                @change="handleFile($event)">

            <p class="text-[11px] text-[#1a3a52]/50 text-center">
                PDF, DOC, dan DOCX didukung · Maks. 100 MB
            </p>
        </div>
    </template>
</div>