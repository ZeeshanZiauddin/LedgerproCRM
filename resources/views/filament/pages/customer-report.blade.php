<x-filament::page>
    <div class="space-y-4">
        <h1 class="text-xl font-semibold">Customer Report</h1>

        {{ $this->form }}

        @if ($receipts && $receipts->isNotEmpty())
            <div class="mt-4">
                <h2 class="text-lg font-semibold">PDF Preview</h2>
                <iframe
                    src="{{ route('generate.pdf.preview', ['customer_id' => $customer_id, 'date_range' => $date_range]) }}"
                    width="100%" height="600px"></iframe>
            </div>
        @else
            <p>No receipts found for the selected filters.</p>
        @endif
    </div>
</x-filament::page>
