<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; font-size: 14px; color: #222; max-width: 640px;">
    <h2 style="margin-bottom: 4px;">Enamad verification failures digest</h2>
    <p style="margin-top: 0; color: #666;">
        Window: last {{ $digest['window_hours'] }}h &middot; Generated: {{ $digest['generated_at'] }}
    </p>

    <p><strong>Total failures: {{ $digest['total'] }}</strong></p>

    @if (!empty($digest['counts']))
        <ul style="padding-left: 18px;">
            @foreach ($digest['counts'] as $event => $count)
                <li>{{ $event }}: {{ $count }}</li>
            @endforeach
        </ul>
    @endif

    @if (!empty($digest['entries']))
        <h3>Latest entries (up to 20)</h3>
        <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-size: 12px;">
            <tr style="background: #f3f3f3;">
                <th align="left">Time</th>
                <th align="left">Event</th>
                <th align="left">Detail</th>
            </tr>
            @foreach ($digest['entries'] as $entry)
                <tr>
                    <td>{{ $entry['time'] }}</td>
                    <td>{{ $entry['event'] }}</td>
                    <td>{{ $entry['error'] !== '' ? $entry['error'] : $entry['body'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="color: #666;">
        Verify manually:
        <a href="{{ url('/admin/login') }}">{{ url('/admin/login') }}</a>
    </p>
</body>
</html>
