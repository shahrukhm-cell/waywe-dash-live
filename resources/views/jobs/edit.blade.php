<div>

    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit service job</h2>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form class="space-y-6" wire:submit="save">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quote / estimate number</label>
                            <input type="text" name="quote_estimate_number" wire:model="form.quote_estimate_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PO #</label>
                            <input type="text" name="po_number" wire:model="form.po_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Job type</label>
                            <select name="job_type" wire:model="form.job_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="estimate">Estimate</option>
                                <option value="job">Job</option>
                                <option value="redo">Redo</option>
                                <option value="pending">Pending</option>
                                <option value="adjustment">Adjustment</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tax</label>
                            <input type="number" step="0.01" name="tax" wire:model="form.tax" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Other tax</label>
                            <input type="number" step="0.01" name="other_tax" wire:model="form.other_tax" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirmed date</label>
                            <input type="date" name="confirmed_date" wire:model="form.confirmed_date" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Booked date / work date</label>
                            <input type="date" name="booked_date" wire:model="form.booked_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">This will also be saved as the scheduled date.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Best time</label>
                            <input type="time" name="best_time" wire:model="form.best_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full name</label>
                            <input type="text" name="site_full_name" wire:model="form.site_full_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telephone</label>
                            <input type="text" name="site_telephone" wire:model="form.site_telephone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Business</label>
                            <input type="text" name="site_business" wire:model="form.site_business" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="site_title" wire:model="form.site_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="site_address" wire:model="form.site_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="site_city" wire:model="form.site_city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">State</label>
                            <input type="text" name="site_state" wire:model="form.site_state" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Postal code</label>
                            <input type="text" name="site_postal_code" wire:model="form.site_postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing name</label>
                            <input type="text" name="billing_full_name" wire:model="form.billing_full_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing phone</label>
                            <input type="text" name="billing_telephone" wire:model="form.billing_telephone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing address</label>
                            <input type="text" name="billing_address" wire:model="form.billing_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing city</label>
                            <input type="text" name="billing_city" wire:model="form.billing_city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing state</label>
                            <input type="text" name="billing_state" wire:model="form.billing_state" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing postal code</label>
                            <input type="text" name="billing_postal_code" wire:model="form.billing_postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Service line items</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-2 py-2 text-left">Area/Item</th>
                                    <th class="border px-2 py-2 text-left">Length</th>
                                    <th class="border px-2 py-2 text-left">Width</th>
                                    <th class="border px-2 py-2 text-left">Description</th>
                                    <th class="border px-2 py-2 text-left">Unit</th>
                                    <th class="border px-2 py-2 text-left">Qty</th>
                                    <th class="border px-2 py-2 text-left">Scheduled time</th>
                                    <th class="border px-2 py-2 text-left">Amount</th>
                                    <th class="border px-2 py-2 text-left"><button type="button" wire:click="addItem" class="text-indigo-700">Add row</button></th>
                                </tr>
                            </thead>
                            <tbody id="item-rows">
                                @foreach ($form['items'] as $index => $item)
                                    <tr class="item-row" wire:key="job-item-{{ $index }}">
                                        <td class="border px-2 py-2"><input type="text" wire:model="form.items.{{ $index }}.area_item" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="number" step="0.01" wire:model="form.items.{{ $index }}.length" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="number" step="0.01" wire:model="form.items.{{ $index }}.width" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="text" wire:model="form.items.{{ $index }}.description" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="text" wire:model="form.items.{{ $index }}.unit" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="number" step="0.01" wire:model="form.items.{{ $index }}.quantity" class="quantity-field w-full rounded-md border-gray-300 shadow-sm" {{ (float) $item['length'] > 0 && (float) $item['width'] > 0 ? 'readonly' : '' }}></td>
                                        <td class="border px-2 py-2"><input type="time" wire:model="form.items.{{ $index }}.time" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><input type="number" step="0.01" wire:model="form.items.{{ $index }}.amount" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                                        <td class="border px-2 py-2"><button type="button" wire:click="removeItem({{ $index }})" class="text-rose-600">Remove</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700">Internal notes</label>
                    <textarea name="notes" wire:model="form.notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('jobs.show', $job) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update job</button>
                </div>
            </form>
        </div>
    </div>
<script>
document.addEventListener('input', (event) => {
    const row = event.target.closest('.item-row');
    if (!row || (!event.target.matches('[wire\\:model$=".length"]') && !event.target.matches('[wire\\:model$=".width"]'))) return;
    const length = row.querySelector('[wire\\:model$=".length"]');
    const width = row.querySelector('[wire\\:model$=".width"]');
    const quantity = row.querySelector('.quantity-field');
    if (length.value !== '' && width.value !== '') {
        quantity.value = (Number(length.value) * Number(width.value)).toFixed(2);
        quantity.readOnly = true;
        quantity.dispatchEvent(new Event('input', { bubbles: true }));
    } else {
        quantity.readOnly = false;
    }
});
</script>


</div>
