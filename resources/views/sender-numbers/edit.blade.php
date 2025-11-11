<x-admin-layout toastify="1">
    <div class="max-w-screen-xl mx-auto px-4 mb-8">
        <div class="my-3">
            @if(!$current_user->isUser())
                <a href="{{ route('sender-numbers.index') }}" class="no-underline">All Sender Numbers</a>
            @endif
        </div>
        <form action="{{ route('sender-numbers.update', $item) }}" method="post" data-js="app-form">
            @method('PUT')
            <div class="flex flex-wrap -mx-1">
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Phone</span>
                        <input type="number" name="phone" value="{{ $item->phone }}" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                    <small>Make sure you have this number actively alloted for sending sms.</small>
                </div>
                <div class="w-full px-1">
                    <div data-js="app-form-status" class="hidden font-semibold hidden w-full mb-2"></div>
                    <x-button type="submit" data-js="app-form-btn">Update Sender Number</x-button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>