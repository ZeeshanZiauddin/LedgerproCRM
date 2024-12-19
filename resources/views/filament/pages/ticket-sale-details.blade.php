<x-filament-panels::page>
    <div class="space-y-4">
        <div>

            @if ($this->date_range)
                <div class="mt-4">
                    <h2 class="text-lg font-semibold">PDF Preview of all the records</h2>
                    <iframe
                        src="{{ route('ticketdetails.preview', ['customer_id' => $this->customer_id, 'date_range' => $this->date_range]) }}"
                        width="100%" height="600px"></iframe>
                </div>
            @else
                <div class="flex justify-center items-center">
                    <div class="border-dotted p-6 rounded">
                        <p>No records found for the selected filters.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
