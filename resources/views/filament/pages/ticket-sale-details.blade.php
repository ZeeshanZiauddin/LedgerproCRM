<x-filament-panels::page>
    <div class="space-y-4">

        <div>
            {{-- {{ dd($receipts) }} --}}
            @if ($receipts && $receipts->isNotEmpty())
                <div class="mt-4">
                    <h2 class="text-lg font-semibold">PDF Preview</h2>
                    <iframe
                        src="{{ route('generate.ticketdetails.pdf.preview', ['customer_id' => $customer_id, 'date_range' => $date_range]) }}"
                        width="100%" height="600px"></iframe>
                </div>
            @else
                <p>No receipts found foddr the selected filters.</p>
            @endif

        </div>
    </div>
</x-filament-panels::page>
