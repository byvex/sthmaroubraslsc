@auth
    @props([
        'profiles'=> auth()->user()->getProfiles(),
        'activeProfile'=> auth()->user()->getActiveProfile(),
    ])
    @if(!empty($profiles) && !auth()->user()->isUser())
        <select id="profile-switcher" title="Company Profile" class="font-title py-2 leading-tight rounded border-gray-400 focus:border-primary-500 focus:ring-primary-400">
            @foreach($profiles as $profile_id => $profile)
                <option value="{{ $profile_id }}" {{ $profile_id == $activeProfile ? 'selected' : '' }}>{{ $profile['name'] }}</option>
            @endforeach
        </select>
    @endif
@endauth