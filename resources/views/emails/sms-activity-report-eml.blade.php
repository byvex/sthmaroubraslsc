<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>folder</th>
                <th>delivered_at</th>
                <th>to</th>
                <th>group</th>
                <th>name</th>
                <th>country</th>
                <th>from</th>
                <th>status</th>
                <th>message</th>
                <th>part</th>
                <th>user_id</th>
                <th>user_email</th>
                <th>user_name</th>
                <th>send_at</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($items) && count($items))
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->folder }}</td>
                        <td>{{ $item->delivered_at }}</td>
                        <td>{{ $item->to }}</td>
                        <td>{{ $item->recipient }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->countrycode }}</td>
                        <td>{{ $item->from }}</td>
                        <td>{{ strtolower($item->status) }}</td>
                        <td>{{ $item->message }}</td>
                        <td>{{ $item->part }}</td>
                        <td>{{ $item->user_id }}</td>
                        <td>{{ $item->sender?->email }}</td>
                        <td>{{ $item->sender?->name }}</td>
                        <td>{{ $item->send_at }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>