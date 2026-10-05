<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; font-size: 14px; color: #222; max-width: 720px;">
    <h2 style="margin-bottom: 4px;">Payment gateway failures digest</h2>
    <p style="margin-top: 0; color: #666;">
        Window: last {{ $digest['window_hours'] }}h &middot; Generated: {{ $digest['generated_at'] }}
    </p>

    <p><strong>Total failures: {{ $digest['total'] }}</strong></p>

    @if (!empty($digest['by_gateway']))
        <h3 style="margin-bottom: 4px;">By gateway</h3>
        <ul style="padding-left: 18px; margin-top: 0;">
            @foreach ($digest['by_gateway'] as $gateway => $count)
                <li>{{ $gateway }}: <strong>{{ $count }}</strong></li>
            @endforeach
        </ul>
    @endif

    @if (!empty($digest['by_kind']))
        <h3 style="margin-bottom: 4px;">By failure kind</h3>
        <ul style="padding-left: 18px; margin-top: 0;">
            @foreach ($digest['by_kind'] as $kind => $count)
                <li>{{ $kind }}: <strong>{{ $count }}</strong></li>
            @endforeach
        </ul>
    @endif

    @if (!empty($digest['entries']))
        <h3>Latest entries (up to 25, most severe first)</h3>
        <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-size: 12px;">
            <tr style="background: #f3f3f3;">
                <th align="left">Time</th>
                <th align="left">Level</th>
                <th align="left">Gateway</th>
                <th align="left">Kind</th>
                <th align="left">Message</th>
                <th align="left">Detail</th>
            </tr>
            @foreach ($digest['entries'] as $entry)
                <tr>
                    <td>{{ $entry['time'] }}</td>
                    <td>{{ $entry['level'] }}</td>
                    <td>{{ $entry['gateway'] }}</td>
                    <td>{{ $entry['kind'] }}</td>
                    <td>{{ $entry['message'] }}</td>
                    <td>{{ $entry['detail'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="color: #666;">
        These failures were recovered from in the request that produced them, so
        affected customers were shown an error page. Unknown transactions and
        grant failures usually mean money arrived without the membership being
        issued &mdash; check the transaction list.
    </p>
</body>
</html>