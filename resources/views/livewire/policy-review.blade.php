<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
        <h2 class="mt-6 text-center text-2xl font-extrabold text-slate-900">
            Policy Update Required
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            The institution's policies have been updated. Please review and acknowledge the changes before continuing.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-4xl">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            
            @foreach($policiesToReview as $key => $policy)
            <div class="mb-10 last:mb-0 border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $policy['title'] }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Version {{ $policy['version'] }} &bull; Updated {{ \Carbon\Carbon::parse($policy['updated_at'])->format('M d, Y') }}</p>
                    </div>
                </div>
                <div class="p-6 text-sm text-slate-700 leading-relaxed max-h-96 overflow-y-auto prose prose-slate">
                    {!! $policy['content'] !!}
                </div>
                
                <div class="bg-amber-50 px-6 py-4 border-t border-amber-100 flex items-start space-x-3">
                    <div class="flex items-center h-5">
                        <input id="{{ $key }}Acknowledged" wire:model="{{ $key }}Acknowledged" type="checkbox" class="focus:ring-[#17357A] h-4 w-4 text-[#17357A] border-slate-300 rounded cursor-pointer">
                    </div>
                    <div class="text-sm">
                        <label for="{{ $key }}Acknowledged" class="font-medium text-amber-900 cursor-pointer">I have read and acknowledge the updated {{ $policy['title'] }}.</label>
                        @error($key.'Acknowledged') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @endforeach

            <div class="mt-6 flex justify-end">
                <button wire:click="acknowledge" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-[#17357A] hover:bg-[#0f2459] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] transition-colors">
                    Acknowledge & Continue
                </button>
            </div>
            
        </div>
    </div>
</div>
