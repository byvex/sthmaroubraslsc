<x-admin-layout>
    <div class="max-w-screen-xl mx-auto px-4 mb-8">
        <div class="my-3">
            @if(!$current_user->isUser())
                <a href="{{ route('sender-numbers.create') }}" class="no-underline">Create Sender Number</a>
            @endif
        </div>
        <div class="overflow-auto mb-3">
            <table class="w-full bg-white shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2 font-semibold text-white bg-black">ID</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Number</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black"></th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Action</th>
                    </tr>
                </thead>
                <tbody class="border border-t-0 border-solid border-gray-200">
                    @if(!empty($items) && count($items))
                        @foreach($items as $item)
                            <tr>
                                <td class="px-4 py-2 border-0 border-b border-solid border-gray-100 text-sm">
                                    {{ $item->id }}
                                </td>
                                <td class="px-4 py-2 border-0 border-b border-solid border-gray-100 text-sm leading-tight">
                                    {{ $item->phone }}
                                </td>
                                <td class="px-4 py-2 border-0 border-b border-solid border-gray-100 text-sm">
                                </td>
                                <td class="px-4 py-2 border-0 border-b border-solid border-gray-100 text-sm">
                                    @if($current_user->isSuperAdmin() || $current_user->isAdmin())
                                        <a href="{{ route('sender-numbers.edit', $item->id) }}">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    @if(empty($items) || !count($items))
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-sm text-center">Nothing found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{ !empty($items) ? $items->onEachSide(2)->links() : null }}
    </div>
</x-admin-layout>